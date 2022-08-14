import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vuePlugin from '@vitejs/plugin-vue';
import fs from 'fs';
import {resolve} from 'path';
import {homedir} from 'os';

let host = loadEnv('', './', 'APP_URL')
    .APP_URL.replace('https://', '')
    .replace('http://', '');

function detectServerConfig(host: string) {
    let keyPath = resolve(homedir(), loadEnv('', './', 'VITE_KEY_PATH').VITE_KEY_PATH)
    let certificatePath = resolve(homedir(), loadEnv('', './', 'VITE_CERT_PATH').VITE_CERT_PATH)

    if (!fs.existsSync(keyPath)) {
        return {}
    }

    if (!fs.existsSync(certificatePath)) {
        return {}
    }

    return {
        hmr: {host},
        host,
        https: {
            key: fs.readFileSync(keyPath),
            cert: fs.readFileSync(certificatePath),
        },
    }
}

export default defineConfig({
    server: detectServerConfig(host),
    plugins: [
        laravel({
            input: ['resources/scripts/admin.ts'],
            refresh: true,
        }),
        vuePlugin({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/scripts',
            '@admin': '/resources/scripts/admin',
        },
    },
});
