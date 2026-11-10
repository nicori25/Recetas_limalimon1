import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
let stop: (() => void) | undefined
beforeEach(() => {
  vi.resetModules()
  vi.stubEnv('VITE_SUPABASE_URL', '')
  vi.stubEnv('VITE_SUPABASE_PUBLISHABLE_KEY', '')
  vi.stubEnv('VITE_SUPABASE_ANON_KEY', '')
})
afterEach(() => {
  stop?.()
  stop = undefined
  vi.unstubAllEnvs()
  vi.unstubAllGlobals()
})
describe('Selección explícita de demo e integración Supabase', () => {
  it('usa la demo únicamente si no hay configuración', async () => {
    const client = await import('../src/lib/supabase')
    expect(client.isDemo).toBe(true)
    expect(client.supabase).toBeNull()
  })
  it('muestra una configuración parcial como error, sin reemplazarla por la demo', async () => {
    vi.stubEnv('VITE_SUPABASE_URL', 'https://example.supabase.co')
    const client = await import('../src/lib/supabase')
    expect(client.isDemo).toBe(false)
    expect(client.supabase).toBeNull()
    expect(client.configurationError).toContain('incompleta')
  })
  it.each([
    'sb_secret_not-a-real-key',
    `header.${btoa(JSON.stringify({ role: 'service_role' }))}.signature`,
  ])('rechaza una clave privada de servidor (%s)', async (key) => {
    vi.stubEnv('VITE_SUPABASE_URL', 'https://example.supabase.co')
    vi.stubEnv('VITE_SUPABASE_PUBLISHABLE_KEY', key)
    const client = await import('../src/lib/supabase')
    expect(client.supabase).toBeNull()
    expect(client.configurationError).toContain('clave privada')
  })
  it('con una clave pública realiza solicitudes al proyecto configurado y devuelve fallos reales', async () => {
    vi.stubEnv('VITE_SUPABASE_URL', 'https://example.supabase.co')
    vi.stubEnv('VITE_SUPABASE_PUBLISHABLE_KEY', 'sb_publishable_example')
    const fetch = vi.fn().mockResolvedValue(
      new Response(JSON.stringify({ code: '42501', message: 'permission denied' }), {
        status: 403,
        headers: { 'Content-Type': 'application/json' },
      }),
    )
    vi.stubGlobal('fetch', fetch)
    const client = await import('../src/lib/supabase')
    stop = () => client.supabase?.auth.stopAutoRefresh()
    expect(client.isDemo).toBe(false)
    expect(client.configurationError).toBe('')
    const result = await client.supabase!.from('recipes').select('*')
    expect(fetch).toHaveBeenCalled()
    expect(String(fetch.mock.calls[0]![0])).toContain('https://example.supabase.co/rest/v1/recipes')
    expect(result.error?.code).toBe('42501')
    expect(result.data).toBeNull()
    expect(client.recipeImage('user/photo.jpg')).toContain(
      '/storage/v1/object/public/recipe-images/user/photo.jpg',
    )
  })
})
