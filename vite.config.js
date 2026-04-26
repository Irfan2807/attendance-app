import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Optimize build output
        minify: 'esbuild',
        // Enable CSS code splitting for better caching
        cssCodeSplit: true,
        // Increase chunk size warning threshold
        chunkSizeWarningLimit: 1000,
        // Optimize rollup output
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separate vendor code for better caching
                    'vendor': ['axios'],
                },
            },
        },
    },
    server: {
        // Configure development server
        hmr: {
            host: 'localhost',
        },
    },
});
