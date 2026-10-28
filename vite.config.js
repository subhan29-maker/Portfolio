import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            // The public site and the admin dashboard are two entry points
            // sharing one stylesheet pipeline: admin.css starts from the same
            // design tokens so both halves of the application look related.
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/css/admin.css', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                bunny('Plus Jakarta Sans', {
                    alias: 'display',
                    variable: '--font-display',
                    weights: [500, 600, 700, 800],
                }),
                bunny('Inter', {
                    alias: 'body',
                    variable: '--font-body',
                    weights: [400, 500, 600],
                }),
            ],
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
