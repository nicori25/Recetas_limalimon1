import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
export default defineConfig({
  base: process.env.VITE_BASE_PATH || '/',
  plugins: [vue()],
  test: { include: ['tests/**/*.test.ts'] },
  build: { chunkSizeWarningLimit: 600 },
})
