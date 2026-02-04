# Watchtower Architecture

Technical overview of how Watchtower works under the hood.

---

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                      Laravel Application                     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────┐         ┌──────────────┐                   │
│  │   Queue     │────────▶│ Job Monitor  │                   │
│  │   Events    │         │  (Listeners) │                   │
│  └─────────────┘         └──────┬───────┘                   │
│                                  │                            │
│                                  ▼                            │
│                          ┌──────────────┐                    │
│                          │   Database   │                    │
│                          │ (Job Records)│                    │
│                          └──────────────┘                    │
│                                                               │
│  ┌──────────────┐       ┌──────────────┐                    │
│  │  Supervisor  │◀─────▶│    Redis     │◀────┐              │
│  │   Command    │       │ (Control Ch.)│     │              │
│  └──────┬───────┘       └──────────────┘     │              │
│         │                                     │              │
│         │ spawns/manages                      │ polls        │
│         ▼                                     │              │
│  ┌──────────────┐                     ┌──────┴───────┐      │
│  │   Worker     │────────────────────▶│    Worker    │      │
│  │  Processes   │   (Symfony Process) │   Processes  │      │
│  └──────────────┘                     └──────────────┘      │
│                                                               │
│  ┌──────────────┐                                            │
│  │  Dashboard   │◀───polls (3s)────┐                        │
│  │ (Inertia+Vue)│                   │                        │
│  └──────────────┘                   │                        │
│         │                     ┌──────┴───────┐               │
│         └────sends commands──▶│ API          │               │
│                               │ Controllers  │               │
│                               └──────────────┘               │
└─────────────────────────────────────────────────────────────┘
```

---

## Core Components

### 1. Job Monitor

**Location:** `src/Services/JobMonitor.php`

Listens to Laravel queue events and records job lifecycle:

| Event | Action |
|-------|--------|
| `JobQueued` | Create job record (status: pending) |
| `JobProcessing` | Update to processing, record worker ID |
| `JobProcessed` | Update to completed |
| `JobFailed` | Update to failed, store exception |
| `JobRetryRequested` | Increment attempts counter |

```php
// Registered in WatchtowerServiceProvider
Event::listen(JobQueued::class, fn($e) => $monitor->recordJobQueued($e));
Event::listen(JobProcessing::class, fn($e) => $monitor->recordJobStarted($e));
// ...
```

### 2. Worker Manager

**Location:** `src/Services/WorkerManager.php`

Manages worker processes using Symfony Process:

```php
// Start worker
$process = new Process(['php', 'artisan', 'watchtower:worker', $queue]);
$process->start();

// Send stop command via Redis
Redis::set("watchtower:worker:{$id}:command", "stop");
```

### 3. Worker Command

**Location:** `src/Commands/WorkerCommand.php`

The actual worker process that:

1. Registers itself in database
2. Processes jobs from queue
3. Polls Redis every 3s for commands
4. Sends heartbeat updates

```php
while (!$this->shouldStop) {
    $command = Redis::get("watchtower:worker:{$id}:command");
    
    if ($command === 'stop') break;
    if ($command === 'pause') $this->waitWhilePaused();
    
    $worker->runNextJob(...);
    $this->sendHeartbeat();
}
```

### 4. Supervisor Command

**Location:** `src/Commands/SupervisorCommand.php`

Orchestrates worker lifecycle:

- Maintains minimum worker count
- Restarts dead workers
- Monitors health via heartbeats
- Cleans up stale records

---

## Cross-Platform Control Protocol

### The Problem with PCNTL

Laravel Horizon uses PCNTL signals (`SIGTERM`, `SIGUSR2`) for worker control. PCNTL is **Unix-only** - it doesn't exist on Windows.

### Watchtower's Solution: Redis Polling

```
┌───────────────┐    SET command     ┌───────────────┐
│   Dashboard   │ ─────────────────▶ │     Redis     │
│   (Browser)   │                    │               │
└───────────────┘                    └───────┬───────┘
                                             │
                                     GET every 3s
                                             │
                                             ▼
                                     ┌───────────────┐
                                     │    Worker     │
                                     │   Process     │
                                     └───────────────┘
```

**Command Flow:**

1. User clicks "Stop Worker" in dashboard
2. Controller calls `WorkerManager::stopWorker($id)`
3. WorkerManager writes to Redis: `watchtower:worker:{id}:command = "stop"`
4. Worker polls Redis during job processing loop
5. Worker reads command, finishes current job, exits gracefully

**Trade-offs:**

| Aspect | PCNTL Signals | Redis Polling |
|--------|---------------|---------------|
| Response Time | Instant | 1-3 seconds |
| Platform Support | Unix only | Cross-platform |
| Complexity | Signal handlers | Simple loop |
| Reliability | Edge cases | Predictable |

---

## Database Schema

### `watchtower_jobs`

```sql
CREATE TABLE watchtower_jobs (
    id BIGINT PRIMARY KEY,
    job_id VARCHAR(255) UNIQUE,     -- Laravel job UUID
    queue VARCHAR(255),              -- Queue name
    connection VARCHAR(255),         -- Connection (redis, database)
    payload LONGTEXT,                -- Serialized job data
    status VARCHAR(255),             -- pending, processing, completed, failed
    worker_id BIGINT,                -- FK to watchtower_workers
    attempts INT DEFAULT 0,
    exception LONGTEXT,              -- Error trace for failed jobs
    queued_at TIMESTAMP,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (status, queued_at),
    INDEX (queue, status)
);
```

### `watchtower_workers`

```sql
CREATE TABLE watchtower_workers (
    id BIGINT PRIMARY KEY,
    worker_id VARCHAR(255) UNIQUE,  -- UUID
    supervisor VARCHAR(255),         -- Supervisor name
    queue VARCHAR(255),              -- Queue being processed
    pid INT,                         -- OS process ID
    status VARCHAR(255),             -- running, paused, stopped
    started_at TIMESTAMP,
    last_heartbeat TIMESTAMP,        -- Health check
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX (supervisor, status),
    INDEX (status, last_heartbeat)
);
```

---

## Dashboard Architecture

### Inertia + Vue 3

The dashboard is a single-page application using:

- **Inertia.js** - Server-side routing with client-side rendering
- **Vue 3** - Reactive UI components
- **Vite** - Asset bundling

### Polling Mechanism

```javascript
// Dashboard.vue
onMounted(() => {
    pollTimer = setInterval(async () => {
        const response = await fetch('/watchtower/api/poll');
        const data = await response.json();
        stats.value = data.stats;
        recentJobs.value = data.recentJobs;
        workers.value = data.workers;
    }, 3000);
});
```

### Route Structure

| Route | Controller | Purpose |
|-------|------------|---------|
| `GET /watchtower` | DashboardController | Main dashboard |
| `GET /watchtower/api/poll` | DashboardController | Polling data |
| `GET /watchtower/jobs` | JobsController | Job list |
| `GET /watchtower/jobs/{id}` | JobsController | Job detail |
| `GET /watchtower/failed` | FailedJobsController | Failed jobs |
| `POST /watchtower/failed/{id}/retry` | FailedJobsController | Retry job |
| `GET /watchtower/workers` | WorkersController | Worker list |
| `POST /watchtower/workers/start` | WorkersController | Start worker |
| `POST /watchtower/workers/{id}/stop` | WorkersController | Stop worker |
| `GET /watchtower/metrics` | MetricsController | Metrics view |

---

## Security Considerations

### Authorization

- Gate-based access control via `viewWatchtower`
- Middleware: `AuthorizeWatchtower`
- Default: Local environment only

### Input Validation

- Worker start: validates queue parameter
- All routes protected by CSRF

### XSS Prevention

- Job payloads displayed in `<pre>` tags
- Vue's automatic escaping
