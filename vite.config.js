import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js', 'resources/js/admin-editor.js', 'resources/css/demos.css', 'resources/css/presentation.css', 'resources/css/g.css', 'resources/css/g-lean.css', 'resources/css/g-inner.css', 'resources/js/g-scroll.js', 'resources/js/g-3d.js', 'resources/js/g-bar.js', 'resources/js/g-kreo.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
