import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],

  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },

  server: {
    port: 5173,
    proxy: {
      // Proxy all /api calls to Laravel during development
      // This avoids CORS entirely in dev mode
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
        // Handle OPTIONS preflight
        configure: (proxy) => {
          proxy.on('error', (err) => console.error('Proxy error:', err))
        },
      },
      '/storage': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },

  build: {
    outDir: 'dist',
    sourcemap: false,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['vue', 'vue-router', 'pinia', 'axios'],
          stripe: ['@stripe/stripe-js'],
        },
      },
    },
  },
})
