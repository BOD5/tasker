import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        host: true,
        // origin: 'http://localhost:5173', // Force origin
        hmr: { // Explicitly set HMR host too
             host: 'localhost',
             port: 5173 // Ensure HMR port matches
        }
    }
});
