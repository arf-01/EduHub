import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { svelte } from '@sveltejs/vite-plugin-svelte';
import { VitePWA } from 'vite-plugin-pwa';
import fs from 'fs';
import path from 'path';

export default defineConfig({
    server: {
        host: 'localhost',  // Use localhost instead of true to avoid IPv6 issues
        https: false,
        hmr: {
            host: 'localhost',
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        svelte(),
        VitePWA({
            outDir: 'public',
            buildBase: '/',
            registerType: 'autoUpdate',
            injectRegister: 'script',
            manifest: false,
            workbox: {
                globPatterns: ['**/*.{js,css,ico,png,svg,jpg,jpeg,webp,ttf,woff,woff2}'],
                // Precache the Blade view so it is available offline immediately after install
                additionalManifestEntries: [{ url: '/student-app', revision: null }],
                navigateFallback: null, // Disable default index.html fallback
                runtimeCaching: [
                    {
                        // Cache other HTML routes just in case (e.g., Home)
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages-cache',
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 Days
                            },
                        },
                    }
                ],
            },
        }),
    ],
});
