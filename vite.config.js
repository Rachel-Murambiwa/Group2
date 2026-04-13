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
        enabled: true // This is the magic line that lets us test the PWA locally!
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