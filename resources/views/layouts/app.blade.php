<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Apel Guru SMKN 1 Ciamis')</title>
    
    <!-- PWA Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Apel Guru">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/app.css?v={{ time() }}">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Extra styles pushed by child views (e.g. Leaflet) -->
    @stack('styles')
</head>
<body class="@yield('body-class')">

    @yield('content')

    <!-- PWA Install Banner -->
    <div id="pwa-banner" class="pwa-banner">
        <div class="pwa-banner-text">
            <div class="pwa-banner-title">Pasang Aplikasi Apel</div>
            <div class="pwa-banner-desc">Instal aplikasi untuk akses absensi lebih cepat & mudah.</div>
        </div>
        <div class="pwa-banner-actions">
            <button id="pwa-btn-dismiss" class="btn btn-secondary btn-sm">Nanti</button>
            <button id="pwa-btn-install" class="btn btn-primary btn-sm">Instal</button>
        </div>
    </div>

    <!-- PWA Service Worker Registration & Installation Prompt -->
    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker terdaftar!', reg.scope))
                    .catch(err => console.error('Pendaftaran Service Worker gagal:', err));
            });
        }

        // Handle PWA Install Prompt
        let deferredPrompt;
        const pwaBanner = document.getElementById('pwa-banner');
        const btnInstall = document.getElementById('pwa-btn-install');
        const btnDismiss = document.getElementById('pwa-btn-dismiss');

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome from automatically showing the prompt
            e.preventDefault();
            deferredPrompt = e;
            // Update UI notify the user they can install the PWA
            if (pwaBanner && !localStorage.getItem('pwa-dismissed')) {
                pwaBanner.style.display = 'flex';
            }
        });

        if (btnInstall) {
            btnInstall.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`Pilihan user: ${outcome}`);
                deferredPrompt = null;
                pwaBanner.style.display = 'none';
            });
        }

        if (btnDismiss) {
            btnDismiss.addEventListener('click', () => {
                pwaBanner.style.display = 'none';
                localStorage.setItem('pwa-dismissed', 'true');
            });
        }
    </script>
</body>
</html>
