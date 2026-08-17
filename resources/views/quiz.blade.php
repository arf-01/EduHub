<!DOCTYPE html>
<html lang="en" class="h-full" style="background:#080b14;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Portal — EduHub</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
</head>
<body class="font-sans antialiased min-h-screen" style="background:var(--edu-bg, #080b14); color:var(--edu-text, #e2e8f0);">
    <div id="app"></div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then(registration => {
                    registration.onupdatefound = () => {
                        const installingWorker = registration.installing;
                        if (installingWorker) {
                            installingWorker.onstatechange = () => {
                                if (installingWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('New content available; reloading.');
                                    window.location.reload();
                                }
                            };
                        }
                    };
                }).catch(err => {
                    console.warn('SW registration failed:', err);
                });
            });
        }
    </script>
</body>
</html>

