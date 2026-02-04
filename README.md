# Watchtower

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nathanphelps/watchtower.svg?style=flat-square)](https://packagist.org/packages/nathanphelps/watchtower)
[![Total Downloads](https://img.shields.io/packagist/dt/nathanphelps/watchtower.svg?style=flat-square)](https://packagist.org/packages/nathanphelps/watchtower)

Cross-platform Laravel queue monitoring and worker management. Like Horizon, but works on Windows, Linux, and macOS.

## Features

- 🖥️ **Cross-Platform** - Works on Windows, Linux, and macOS (no PCNTL dependency)
- 📊 **Queue Monitoring Dashboard** - Real-time job tracking and metrics
- ⚙️ **Worker Management** - Start, stop, pause, resume workers from the dashboard
- 🎯 **Manual Scaling** - Adjust worker count on the fly
- 📝 **Job Tracking** - Monitor job status, payload, exceptions, and retries
- 🔄 **Failed Job Management** - Retry failed jobs from the dashboard
- ⏱️ **Configurable Retention** - Automatic pruning of old job records
- 🎨 **Modern UI** - Built with Inertia.js + Vue.js

## Why Watchtower?

Laravel Horizon is excellent but relies on PCNTL signals which don't work on Windows. Watchtower uses a polling-based approach for worker control, making it fully cross-platform while maintaining similar functionality.

## Requirements

- PHP 8.2+
- Laravel 11.x or 12.x
- Redis

## Installation

```bash
composer require nathanphelps/watchtower
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --tag="watchtower-config"
php artisan vendor:publish --tag="watchtower-migrations"
```

Run the migrations:

```bash
php artisan migrate
```

## Configuration

Configure access control in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewWatchtower', function ($user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```

## Usage

Start the supervisor to manage workers:

```bash
php artisan watchtower:supervisor
```

Access the dashboard at `/watchtower` (configurable in config file).

## Documentation

See the [design document](docs/plans/2026-02-04-watchtower-design.md) for detailed architecture and implementation details.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security issues, please email nathan@example.com instead of using the issue tracker.

## Credits

- [Nathan Phelps](https://github.com/nathanphelps)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
