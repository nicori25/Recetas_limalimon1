import { describe, expect, it } from 'vitest'
import { authRedirectUrl, publicAsset } from '../src/lib/urls'
describe('Rutas públicas y autenticación bajo GitHub Pages', () => {
  it('resuelve ilustraciones en la carpeta del repositorio', () => {
    expect(publicAsset('/images/budin.svg', '/Recetas_limalimon1/')).toBe(
      '/Recetas_limalimon1/images/budin.svg',
    )
    expect(publicAsset('favicon.svg', '/Recetas_limalimon1')).toBe(
      '/Recetas_limalimon1/favicon.svg',
    )
  })
  it('mantiene las rutas de desarrollo en la raíz', () => {
    expect(publicAsset('/images/pasta.svg', '/')).toBe('/images/pasta.svg')
  })
  it('devuelve Auth a la aplicación, sin perder la carpeta del repositorio', () => {
    expect(authRedirectUrl('https://nicori25.github.io', '/Recetas_limalimon1/')).toBe(
      'https://nicori25.github.io/Recetas_limalimon1/',
    )
    expect(authRedirectUrl('http://localhost:5173', '/')).toBe('http://localhost:5173/')
  })
})
