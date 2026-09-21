<?php

namespace App\Http\Controllers;

class PwaController extends Controller
{
    public function manifest()
    {
        $manifest = [
            'name'             => 'Marell Academy',
            'short_name'       => 'Marell',
            'description'      => 'Parent Portal · Fee payment · Results · Timetable',
            'start_url'        => '/parent',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#0B3D91',
            'theme_color'      => '#0B3D91',
            'icons' => [
                ['src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    public function serviceWorker()
    {
        $js = <<<'JS'
const CACHE = 'marell-v1';
const OFFLINE_URLS = ['/', '/parent/login'];

self.addEventListener('install', e => {
    e.waitUntil(caches.open(CACHE).then(c => c.addAll(OFFLINE_URLS)));
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(caches.keys().then(keys =>
        Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    ));
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;
    if (e.request.url.includes('/api/') || e.request.url.includes('/pay/')) return;

    e.respondWith(
        fetch(e.request).catch(() =>
            caches.match(e.request).then(r => r || caches.match('/'))
        )
    );
});
JS;

        return response($js, 200, ['Content-Type' => 'application/javascript']);
    }

    public function icon($size)
    {
        $size = in_array((int) $size, [192, 512]) ? (int) $size : 192;

        // Simple SVG-based PNG: since we don't have GD rendering, generate an SVG and label it
        // For a pitch demo, SVG icons work fine in Chrome
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">'
            . '<rect width="' . $size . '" height="' . $size . '" fill="#0B3D91" rx="' . ($size * 0.2) . '"/>'
            . '<circle cx="' . ($size / 2) . '" cy="' . ($size / 2) . '" r="' . ($size * 0.32) . '" fill="#D4AF37"/>'
            . '<text x="50%" y="' . ($size * 0.65) . '" font-family="Arial, sans-serif" font-size="' . ($size * 0.5) . '" font-weight="bold" fill="#0B3D91" text-anchor="middle">M</text>'
            . '</svg>';

        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}
