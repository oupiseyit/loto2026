import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const host = env.VITE_DEV_SERVER_HOST || '0.0.0.0';
    const port = Number(env.VITE_DEV_SERVER_PORT || 5173);
    const hmrHost = env.VITE_HMR_HOST || undefined;
    const origin = env.VITE_DEV_SERVER_ORIGIN || undefined;

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
        ],
        server: {
            host,
            port,
            strictPort: true,
            origin,
            hmr: hmrHost
                ? {
                      host: hmrHost,
                      port,
                      protocol: origin?.startsWith('https://') ? 'wss' : 'ws',
                  }
                : undefined,
        },
    };
});
