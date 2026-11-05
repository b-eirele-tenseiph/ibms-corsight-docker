import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import yaml from '@rollup/plugin-yaml';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig, loadEnv } from 'vite';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            wayfinder({
                formVariants: true,
            }),
            laravel({
                input: ['resources/js/app.ts'],
                refresh: true,
            }),
            tailwindcss(),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
            yaml(),
        ],
        resolve: {
            alias: {
                '@': path.resolve(__dirname, './resources/js'),
            },
        },
        server: {
            host: env.VITE_HOST,
        },
    };
});
