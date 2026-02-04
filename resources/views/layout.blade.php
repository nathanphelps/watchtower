<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Watchtower') - Queue Monitor</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --wt-bg-primary: #0f172a;
            --wt-bg-secondary: #1e293b;
            --wt-bg-tertiary: #334155;
            --wt-text-primary: #f8fafc;
            --wt-text-secondary: #94a3b8;
            --wt-accent-primary: #3b82f6;
            --wt-accent-success: #22c55e;
            --wt-accent-warning: #f59e0b;
            --wt-accent-danger: #ef4444;
            --wt-border: #475569;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--wt-bg-primary);
            color: var(--wt-text-primary);
            min-height: 100vh;
        }

        .layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: var(--wt-bg-secondary);
            border-right: 1px solid var(--wt-border);
            padding: 1.5rem 0;
            flex-shrink: 0;
        }
        .sidebar-header { padding: 0 1.5rem 1.5rem; border-bottom: 1px solid var(--wt-border); }
        .logo { font-size: 1.25rem; font-weight: 700; }
        .nav-links { list-style: none; padding: 1rem 0; }
        .nav-links a {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: var(--wt-text-secondary);
            text-decoration: none;
            transition: all 0.2s;
        }
        .nav-links a:hover, .nav-links a.active {
            background: var(--wt-bg-tertiary);
            color: var(--wt-text-primary);
        }
        .nav-badge {
            margin-left: auto;
            background: var(--wt-bg-tertiary);
            padding: 0.125rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
        }
        .nav-badge.danger { background: var(--wt-accent-danger); }

        /* Main content */
        .main-content { flex: 1; padding: 2rem; overflow-y: auto; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .page-title { font-size: 1.5rem; font-weight: 600; }

        /* Status indicator */
        .status-indicator { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--wt-text-secondary); }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--wt-accent-danger); }
        .status-indicator.active .status-dot { background: var(--wt-accent-success); animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

        /* Stats grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; padding: 1.25rem; }
        .stat-value { font-size: 2rem; font-weight: 700; margin-bottom: 0.25rem; }
        .stat-value.success { color: var(--wt-accent-success); }
        .stat-value.danger { color: var(--wt-accent-danger); }
        .stat-value.processing { color: var(--wt-accent-primary); }
        .stat-label { font-size: 0.875rem; color: var(--wt-text-secondary); }

        /* Section */
        .section { margin-bottom: 2rem; }
        .section-title { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; }

        /* Tables */
        .table-container { background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--wt-border); }
        th { background: var(--wt-bg-tertiary); font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--wt-text-secondary); }
        .job-name { font-family: monospace; font-size: 0.875rem; }
        .queue-badge { background: var(--wt-bg-tertiary); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; }
        .time { color: var(--wt-text-secondary); font-size: 0.875rem; }
        .empty { text-align: center; color: var(--wt-text-secondary); padding: 2rem !important; }

        /* Status badges */
        .status-badge { display: inline-flex; padding: 0.25rem 0.625rem; font-size: 0.75rem; font-weight: 500; border-radius: 9999px; text-transform: capitalize; }
        .status-pending { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .status-processing { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .status-completed { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
        .status-failed { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        .status-running { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .status-paused { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .status-stopped { background: rgba(100, 116, 139, 0.2); color: #94a3b8; }

        /* Workers grid */
        .workers-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; }
        .worker-card { background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; padding: 1.25rem; }
        .worker-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--wt-border); }
        .worker-id { font-family: monospace; font-size: 0.875rem; font-weight: 600; }
        .worker-details { display: flex; flex-direction: column; gap: 0.5rem; }
        .worker-detail { display: flex; justify-content: space-between; font-size: 0.875rem; }
        .worker-detail .label { color: var(--wt-text-secondary); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border-radius: 0.375rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.75rem; }
        .btn-primary { background: var(--wt-accent-primary); color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: var(--wt-bg-tertiary); color: var(--wt-text-primary); }
        .btn-success { background: var(--wt-accent-success); color: white; }
        .btn-warning { background: var(--wt-accent-warning); color: white; }
        .btn-danger { background: var(--wt-accent-danger); color: white; }

        /* Links */
        .link { color: var(--wt-accent-primary); text-decoration: none; }
        .link:hover { text-decoration: underline; }

        /* Pagination */
        .pagination { display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1.5rem; }
        .page-info { color: var(--wt-text-secondary); font-size: 0.875rem; }

        /* Filters */
        .filters { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .filter-select, .filter-input {
            background: var(--wt-bg-secondary);
            border: 1px solid var(--wt-border);
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            color: var(--wt-text-primary);
            font-size: 0.875rem;
        }
        .filter-input { min-width: 200px; }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0, 0, 0, 0.75); display: flex; align-items: center; justify-content: center; z-index: 50; }
        .modal { background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; padding: 1.5rem; width: 100%; max-width: 600px; max-height: 80vh; overflow: auto; }
        .modal-title { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }

        /* Code block */
        .code-block { background: var(--wt-bg-primary); border: 1px solid var(--wt-border); border-radius: 0.375rem; padding: 1rem; font-family: monospace; font-size: 0.75rem; overflow-x: auto; white-space: pre-wrap; word-break: break-word; color: var(--wt-text-secondary); }
        .error-block { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 0.375rem; padding: 1rem; font-family: monospace; font-size: 0.75rem; overflow-x: auto; white-space: pre-wrap; word-break: break-word; color: #f87171; }

        /* Chart */
        .chart-container { background: var(--wt-bg-secondary); border: 1px solid var(--wt-border); border-radius: 0.5rem; padding: 1.5rem; margin-bottom: 1.5rem; }
        .bar-chart { display: flex; align-items: flex-end; gap: 0.25rem; height: 160px; padding-bottom: 1.5rem; }
        .bar-group { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; }
        .bars { flex: 1; display: flex; gap: 2px; align-items: flex-end; width: 100%; }
        .bar { flex: 1; min-height: 2px; border-radius: 2px 2px 0 0; }
        .bar.completed { background: var(--wt-accent-success); }
        .bar.failed { background: var(--wt-accent-danger); }
        .bar-label { font-size: 0.625rem; color: var(--wt-text-secondary); margin-top: 0.5rem; }
        .chart-legend { display: flex; justify-content: center; gap: 1.5rem; margin-top: 0.5rem; }
        .legend-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: var(--wt-text-secondary); }
        .legend-color { width: 12px; height: 12px; border-radius: 2px; }
        .legend-color.completed { background: var(--wt-accent-success); }
        .legend-color.failed { background: var(--wt-accent-danger); }

        /* Form */
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--wt-text-secondary); }
        .form-input { width: 100%; background: var(--wt-bg-primary); border: 1px solid var(--wt-border); border-radius: 0.375rem; padding: 0.5rem 0.75rem; color: var(--wt-text-primary); font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo">🗼 Watchtower</h1>
            </div>
            <ul class="nav-links">
                <li><a href="{{ route('watchtower.dashboard') }}" class="{{ request()->routeIs('watchtower.dashboard') ? 'active' : '' }}"><span>📊</span> Dashboard</a></li>
                <li><a href="{{ route('watchtower.jobs.index') }}" class="{{ request()->routeIs('watchtower.jobs.*') ? 'active' : '' }}"><span>📋</span> Jobs</a></li>
                <li><a href="{{ route('watchtower.failed.index') }}" class="{{ request()->routeIs('watchtower.failed.*') ? 'active' : '' }}"><span>❌</span> Failed Jobs</a></li>
                <li><a href="{{ route('watchtower.workers.index') }}" class="{{ request()->routeIs('watchtower.workers.*') ? 'active' : '' }}"><span>⚙️</span> Workers</a></li>
                <li><a href="{{ route('watchtower.metrics.index') }}" class="{{ request()->routeIs('watchtower.metrics.*') ? 'active' : '' }}"><span>📈</span> Metrics</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
