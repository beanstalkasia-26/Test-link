import inertia from '@inertiajs/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

const backend = '../backend';

export default defineConfig(({ command }) => ({
    envDir: backend,
    plugins: [
        laravel({
            input: ['src/app.jsx', 'src/styles/site.scss', 'src/styles/admin.scss'],
            ssr: 'src/ssr.jsx',
            publicDirectory: `${backend}/public`,
            hotFile: `${backend}/public/hot`,
            ssrOutputDirectory: `${backend}/bootstrap/ssr`,
            refresh: [`${backend}/resources/views/**`, `${backend}/routes/**`],
        }),
        inertia({ ssr: 'src/ssr.jsx' }),
        react(),
    ],
    ssr: command === 'build' ? { noExternal: true } : {},
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function'],
                quietDeps: true,
            },
        },
    },
}));
