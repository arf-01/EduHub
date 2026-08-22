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
                globPatterns: ['build/**/*.{js,css,ico,png,svg,webp,woff,woff2}', 'favicon.ico', 'manifest.json', 'images/**/*.{png,jpg,jpeg,svg,webp}'],
                globIgnores: ['storage/**/*', 'vendor/**/*', '**/node_modules/**/*'],
                // Precache the student app shell
                additionalManifestEntries: [{ url: '/student-app', revision: 'v3' }],
                navigateFallback: '/student-app',
                navigateFallbackAllowlist: [/^\/student-app/, /^\/quiz-listStud/, /^\/enter-room/],
                clientsClaim: true,
                skipWaiting: true,
                cleanupOutdatedCaches: true,
                runtimeCaching: [
                    // 1. Navigation requests fallback / network-first
                    {
                        urlPattern: ({ request }) => request.mode === 'navigate',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pages-cache',
                            networkTimeoutSeconds: 3,
                            expiration: {
                                maxEntries: 50,
                                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 Days
                            },
                        },
                    },
                    // 2. Static Images & Assets
                    {
                        urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp|ico)$/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'static-images',
                            expiration: {
                                maxEntries: 60,
                                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 Days
                            },
                        },
                    },
                    // 3. Google Fonts Stylesheets
                    {
                        urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'google-fonts-stylesheets',
                            expiration: {
                                maxEntries: 20,
                                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 Days
                            },
                        },
                    },
                    // 4. Google Fonts Webfont files (woff2, etc.)
                    {
                        urlPattern: /^https:\/\/fonts\.gstatic\.com\/.*/i,
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'google-fonts-webfonts',
                            cacheableResponse: {
                                statuses: [0, 200],
                            },
                            expiration: {
                                maxEntries: 30,
                                maxAgeSeconds: 365 * 24 * 60 * 60, // 1 Year
                            },
                        },
                    },
                ],
            },
        }),
    ],
});
