import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa' 

export default defineConfig({
  plugins: [
    react(),
    tailwindcss(),
    VitePWA({
      registerType: 'autoUpdate', 
      devOptions: {
        enabled: true 
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg}'],
        navigateFallback: '/index.html',
        // INCREASED FILE SIZE LIMIT TO 10MB to allow your logos to pass the build
        maximumFileSizeToCacheInBytes: 10485760 
      },
      manifest: {
        name: 'CharleeDash+',
        short_name: 'CharleeDash+',
        description: 'Secure peer-to-peer lending for Ashesi students.',
        theme_color: '#8A1538', 
        background_color: '#f8fafc', 
        display: 'standalone', 
        icons: [
          {
            src: '/logo.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: '/pwa2.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any maskable'
          }
        ]
      }
    })
  ],
})