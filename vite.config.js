import { defineConfig, loadEnv } from 'vite'
import laravel from 'laravel-vite-plugin'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '')

    const isProduction = mode === 'production'

    return {
        base: isProduction ? '/tehokas-workflow/' : '/',
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js'],
                refresh: true,
            }),
            vue(),
            tailwindcss(),
        ],
        server: {
            host: '0.0.0.0',
            port: 5173,
            hmr: isProduction
                ? {
                      host: 'www.promptshieldai.com',
                      protocol: 'wss',
                      clientPort: 443,
                      path: '/tehokas-workflow/',
                  }
                : {
                      host: 'localhost',
                  },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    }
})
