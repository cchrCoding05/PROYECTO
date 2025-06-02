import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/api': {
        target: 'http://api.helpex.com:22193',
        changeOrigin: true,
        rewrite: (path) => path  // Esto mantiene la ruta /api para que coincida con las rutas de Symfony
      }
    }
  }
})
