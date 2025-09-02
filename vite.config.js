// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import path from 'path'

export default defineConfig({
    plugins: [
        laravel({
            // Single entry: JS imports your SCSS
            input: ['resources/js/back.js'],
            refresh: true,
            // ⬇️ Output to public/admin/theme1 (instead of public/build)
            buildDirectory: 'admin/theme1',
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources',
            // Optional: shorter imports to your theme root
            '@theme': path.resolve(__dirname, 'resources/css/themes/able-pro-vanila-bootstrap-9.6.0'),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                // Helps when the theme SCSS does @use/@import of its own partials or node_modules
                includePaths: [
                    'resources/css/themes/able-pro-vanila-bootstrap-9.6.0/src/assets/scss',
                    'node_modules',
                ],
            },
        },
    },
})
