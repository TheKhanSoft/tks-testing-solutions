import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [`resources/views/**/*`],
        }),
        tailwindcss(),
    ],
    server: {
        cors: true,
        hmr: {
            host: 'localhost',
        },
    },
    optimizeDeps: {
        include: [
            'katex',
            'katex/dist/contrib/auto-render'
        ],
        // Force Vite to bundle these dependencies separately to avoid conflicts
        exclude: []
    },
    build: {
        rollupOptions: {
            external: /\.svg$/, // Keep if you handle SVGs specially
            output: {
                manualChunks: {
                    katex: ['katex', 'katex/dist/contrib/auto-render'],

                }
            }
        },
        chunkSizeWarningLimit: 1000 // Optional: Keep if needed
    },
});
