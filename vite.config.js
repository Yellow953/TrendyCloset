import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Two bundles, because they are two design systems: the shop
            // (blush, Jost, `.tc-*`) and the back office (slate, Inter,
            // `.ad-*`). Sign-in ships with the back office it opens into.
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/js/admin.js',
            ],
            refresh: true,
            // Self-hosted at build time, so the storefront's critical path holds
            // no third-party stylesheet: Vite::fonts() inlines the @font-face
            // rules and preloads only the faces the first screen actually needs.
            // Weights mirror what the views use — adding one here costs every
            // visitor a file, so check the markup before you do.
            fonts: [
                bunny('Jost', {
                    weights: [200, 300, 400, 500, 600],
                    // Body copy is 300/400; the rest can arrive with the swap.
                    preload: [{ weight: 300 }, { weight: 400 }],
                }),
                bunny('Cormorant Garamond', {
                    weights: [400, 500, 600],
                    styles: ['normal', 'italic'],
                    // A handful of accent lines — never the LCP text.
                    preload: false,
                }),
                bunny('Space Grotesk', {
                    weights: [400, 500],
                    preload: false,
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
