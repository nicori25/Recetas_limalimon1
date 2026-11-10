import { test, expect } from '@playwright/test'
import { demoRecipes, demoAuthors } from '../../src/data/demo'
// Exercise the actual production bundle. Mock only the remote service so this
// check does not depend on project data, credentials, or network policy.
test.beforeEach(async ({ page }) => {
  await page.route('https://syojuxibmkarhtweuqfv.supabase.co/rest/v1/**', (route) => {
    const table = new URL(route.request().url()).pathname.split('/').pop()
    return route.fulfill({
      json: table === 'recipes' ? demoRecipes : table === 'profiles' ? demoAuthors : [],
    })
  })
})
test('el build de Pages carga código, imágenes, favicon y rutas con hash', async ({ page }) => {
  const failures: string[] = []
  page.on('pageerror', (error) => failures.push(error.message))
  await page.goto('./')
  await expect(page.locator('.recipe-card')).toHaveCount(6)
  await expect(page.locator('.demo-bar')).toHaveCount(0)
  for (const image of await page.locator('img').all()) {
    await expect(image).toHaveJSProperty('complete', true)
    expect(await image.evaluate((el) => (el as HTMLImageElement).naturalWidth)).toBeGreaterThan(0)
    expect(await image.getAttribute('src')).toMatch(/^\/Recetas_limalimon1\/images\//)
  }
  const favicon = await page.locator('link[rel="icon"]').getAttribute('href')
  expect(favicon).toBe('/Recetas_limalimon1/favicon.svg')
  expect((await page.request.get(favicon!)).status()).toBe(200)
  await page.getByRole('link', { name: 'Ver Ensalada de primavera', exact: true }).click()
  await expect(page).toHaveURL(/\/Recetas_limalimon1\/#\/receta\/ensalada-primavera$/)
  await page.reload()
  await expect(
    page.getByRole('heading', { name: 'Ensalada de primavera', exact: true }),
  ).toBeVisible()
  expect(failures).toEqual([])
})
test('la recuperación de contraseña retorna a la carpeta publicada', async ({ page }) => {
  let redirect = ''
  await page.route('https://syojuxibmkarhtweuqfv.supabase.co/auth/v1/recover**', (route) => {
    redirect = new URL(route.request().url()).searchParams.get('redirect_to') ?? ''
    return route.fulfill({ json: {} })
  })
  await page.goto('./#/cuenta')
  await page.getByRole('button', { name: 'Olvidé mi contraseña' }).click()
  await page.getByLabel('Correo electrónico').fill('prueba@example.invalid')
  await page.getByRole('button', { name: 'Enviar enlace' }).click()
  await expect(
    page.getByRole('status').filter({ hasText: 'Si el correo tiene una cuenta' }),
  ).toBeVisible()
  expect(redirect).toBe('http://127.0.0.1:5180/Recetas_limalimon1/?flow=recovery')
})
