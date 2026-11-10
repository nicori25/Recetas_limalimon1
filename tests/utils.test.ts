import { describe, expect, it } from 'vitest'
import { filterRecipes, currentSeason, lines } from '../src/lib/utils'
import { demoRecipes } from '../src/data/demo'
describe('Búsqueda y preferencias alimentarias', () => {
  it('encuentra palabras con y sin tildes en título e ingredientes', () => {
    expect(filterRecipes(demoRecipes, 'budin banana', [], '').map((r) => r.id)).toEqual([
      'budin-banana',
    ])
    expect(filterRecipes(demoRecipes, 'quinoa', [], '')[0]?.id).toBe('bowl-calabaza')
  })
  it('combina todas las restricciones sin confundir vegetariano con vegano', () => {
    const rows = filterRecipes(demoRecipes, '', ['vegano', 'sin gluten'], '')
    expect(rows.length).toBe(3)
    expect(rows.some((r) => r.id === 'budin-banana')).toBe(false)
  })
  it('incluye recetas de todo el año en la temporada elegida', () => {
    const rows = filterRecipes(demoRecipes, '', [], 'primavera', 15)
    expect(rows.map((r) => r.id).sort()).toEqual(['ensalada-primavera', 'tostadas-palta'])
  })
  it('no inventa coincidencias', () => {
    expect(filterRecipes(demoRecipes, 'chocolate', [], '')).toEqual([])
  })
})
describe('Temporadas del hemisferio sur y pasos', () => {
  it.each([
    ['2026-01-15', 'verano'],
    ['2026-04-15', 'otoño'],
    ['2026-07-15', 'invierno'],
    ['2026-10-15', 'primavera'],
    ['2026-12-15', 'verano'],
  ])('%s corresponde a %s', (date, season) => {
    expect(currentSeason(new Date(date + 'T12:00:00Z'))).toBe(season)
  })
  it('usa Buenos Aires en el cambio de mes, no el huso del servidor', () => {
    expect(currentSeason(new Date('2026-03-01T01:00:00Z'))).toBe('verano')
  })
  it('descarta líneas vacías al crear una receta', () => {
    expect(lines(' 1 banana\n\n  2 huevos \r\n')).toEqual(['1 banana', '2 huevos'])
  })
})
