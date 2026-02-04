import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
    plugins: [vue()],
    build: {
        outDir: 'public',
        manifest: true,
        rollupOptions: {
            input: resolve(__dirname, 'resources/js/app.js'),
            output: {
                entryFileNames: 'js/[name]-[hash].js',
                chunkFileNames: 'js/chunks/[name]-[hash].js',
                assetFileNames: 'css/[name]-[hash][extname]',
            },
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
});
