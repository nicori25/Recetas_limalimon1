import { createClient } from '@supabase/supabase-js'
import { publicSupabaseProject } from './supabase.public'

// Sin overrides, tanto desarrollo como producción usan el proyecto publicado.
// Si se define alguna variable, se usa exclusivamente ese par de configuración
// para evitar mezclar la URL de un proyecto con la clave de otro.
const env = import.meta.env
const hasOverride =
  env.VITE_SUPABASE_URL !== undefined ||
  env.VITE_SUPABASE_PUBLISHABLE_KEY !== undefined ||
  env.VITE_SUPABASE_ANON_KEY !== undefined
const url = (hasOverride ? env.VITE_SUPABASE_URL : publicSupabaseProject.url)?.trim()
const key = (
  hasOverride
    ? env.VITE_SUPABASE_PUBLISHABLE_KEY || env.VITE_SUPABASE_ANON_KEY
    : publicSupabaseProject.publishableKey
)?.trim()
export const isDemo = !url && !key
export let configurationError = ''
function createPublicClient() {
  if (isDemo) return null
  if (!url || !key) {
    configurationError =
      'Configuración incompleta: definí VITE_SUPABASE_URL y la clave pública, o quitá ambas para usar la demo.'
    return null
  }
  try {
    let serviceRole = key.startsWith('sb_secret_')
    if (key.split('.').length === 3) {
      try {
        serviceRole ||=
          JSON.parse(atob(key.split('.')[1]!.replace(/-/g, '+').replace(/_/g, '/'))).role ===
          'service_role'
      } catch {
        /* Let the server validate malformed public tokens. */
      }
    }
    if (serviceRole) {
      configurationError =
        'Se detectó una clave privada en la configuración del navegador. Quitala, revocala en Supabase y usá una clave publishable o anon.'
      return null
    }
    return createClient(url, key, {
      auth: {
        persistSession: true,
        autoRefreshToken: true,
        detectSessionInUrl: true,
        flowType: 'pkce',
      },
    })
  } catch {
    configurationError =
      'No se pudo configurar Supabase. Revisá la URL del proyecto y su clave pública.'
    return null
  }
}
export const supabase = createPublicClient()
export function recipeImage(path: string | null) {
  if (!path) return '/images/ensalada.svg'
  if (isDemo && /^data:image\/(jpeg|png|webp);base64,/.test(path)) return path
  if (path.startsWith('/images/')) return path
  return (
    supabase?.storage.from('recipe-images').getPublicUrl(path).data.publicUrl ?? '/images/budin.svg'
  )
}
