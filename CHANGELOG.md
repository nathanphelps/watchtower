# Changelog

All notable changes to `watchtower` will be documented in this file.

## [Unreleased]

### Phase 1 - Foundation (Completed 2026-02-04)

#### Added
- Package skeleton structure with proper Laravel package conventions
- Composer configuration for Laravel 11 and 12 support
- Configuration file with comprehensive settings for workers, retention, and supervisors
- Database migrations for `watchtower_jobs` and `watchtower_workers` tables
- `Job` Eloquent model with status tracking and helper methods
- `Worker` Eloquent model with health checking and statistics
- `JobMonitor` service to capture and record queue events
- `WatchtowerServiceProvider` with event listener registration
- MIT License and README documentation
- Design document outlining full architecture

#### Technical Details
- Cross-platform support (Windows, Linux, macOS)
- No PCNTL dependency (uses polling approach)
- Proper database indexes for performance
- Event-driven job tracking
- Configurable retention policies
