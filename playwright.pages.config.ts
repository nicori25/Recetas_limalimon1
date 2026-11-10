import { defineConfig } from '@playwright/test'
import { existsSync } from 'node:fs'
export default defineConfig({
  testDir: './tests/pages',
  use: {
    baseURL: 'http://127.0.0.1:5180/Recetas_limalimon1/',
    headless: true,
    launchOptions: {
      executablePath:
        process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE ||
        (existsSync('/usr/bin/chromium') ? '/usr/bin/chromium' : undefined),
      args: ['--no-sandbox'],
    },
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
  webServer: {
    command: 'npm run preview:pages -- --port 5180 --strictPort',
    url: 'http://127.0.0.1:5180/Recetas_limalimon1/',
    reuseExistingServer: false,
  },
})
