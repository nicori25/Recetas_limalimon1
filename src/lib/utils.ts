import type { Recipe } from './types'
export function normalize(value: string) {
  return value
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
}
export function currentSeason(date = new Date()) {
  const month = Number(
    new Intl.DateTimeFormat('en', {
      timeZone: 'America/Argentina/Buenos_Aires',
      month: 'numeric',
    }).format(date),
  )
  return month === 12 || month <= 2
    ? 'verano'
    : month <= 5
      ? 'otoño'
      : month <= 8
        ? 'invierno'
        : 'primavera'
}
export function filterRecipes(
  recipes: Recipe[],
  query: string,
  diets: string[],
  season: string,
  maxMinutes = 0,
) {
  const words = normalize(query).split(/\s+/).filter(Boolean)
  return recipes.filter(
    (r) =>
      words.every((word) =>
        normalize([r.title, r.description, ...r.ingredients].join(' ')).includes(word),
      ) &&
      diets.every((d) => r.dietary_tags.includes(d)) &&
      (!season || r.season === season || r.season === 'todo el año') &&
      (!maxMinutes || r.prep_minutes <= maxMinutes),
  )
}
export function lines(value: string) {
  return value
    .split('\n')
    .map((s) => s.trim())
    .filter(Boolean)
}
export function friendlyError(error: unknown): string {
  const message =
    error && typeof error === 'object' && 'message' in error ? String(error.message) : String(error)
  if (/Invalid login credentials/i.test(message))
    return 'El correo o la contraseña no son correctos.'
  if (/Email not confirmed/i.test(message)) return 'Confirmá tu correo antes de ingresar.'
  if (/already registered/i.test(message)) return 'Ya existe una cuenta con ese correo.'
  if (/rate limit/i.test(message))
    return 'Hubo demasiados intentos. Esperá unos minutos y volvé a probar.'
  if (/fetch|network/i.test(message))
    return 'No pudimos conectar. Revisá tu conexión e intentá nuevamente.'
  if (/row-level security|permission denied|JWT/i.test(message))
    return 'No tenés permiso para realizar esta acción. Revisá tu sesión.'
  return message || 'No pudimos completar la acción. Intentá de nuevo.'
}
