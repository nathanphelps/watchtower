<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Watchtower - Queue Monitor</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--wt-bg-primary);
            color: var(--wt-text-primary);
            min-height: 100vh;
        }

        #app {
            min-height: 100vh;
        }
    </style>

    @vite(['resources/js/app.js'], 'vendor/watchtower')
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
