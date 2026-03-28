import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/admin/admin.js',
                'resources/js/admin/page-layout.js',
                'resources/js/admin/project-index.js',
                'resources/js/front/main.js',
                'resources/js/front/home.js',

                'resources/css/app.css',
                'resources/css/admin/admin.css',
                'resources/css/admin/page-layout.css',
                'resources/css/variables.css',
                'resources/css/front/main.css',
                'resources/css/front/home.css',
            ],
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
