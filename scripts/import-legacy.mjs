/** Private server-side migration. Never import this file into the Vue bundle. */
import { createClient } from '@supabase/supabase-js'
import { readFile, realpath } from 'node:fs/promises'
import { resolve, sep } from 'node:path'
const [file, imagesDirectory] = process.argv.slice(2).filter((arg) => !arg.startsWith('--'))
if (!file || !imagesDirectory) {
  console.error(
    'Uso: node scripts/import-legacy.mjs /ruta/privada/export.json /ruta/uploads [--dry-run] [--include-admin-roles]',
  )
  process.exit(1)
}
const data = JSON.parse(await readFile(file, 'utf8'))
if (
  data.format !== 'limalimon-legacy-v1' ||
  !['usuarios', 'recetas', 'favoritos', 'opiniones', 'valoraciones'].every((k) =>
    Array.isArray(data[k]),
  )
)
  throw new Error('Formato de exportación inválido.')
const lines = (value) =>
  String(value ?? '')
    .split(/\r?\n/)
    .map((s) => s.trim())
    .filter(Boolean)
const users = new Set(data.usuarios.map((u) => Number(u.id)))
const recipeIds = new Set(data.recetas.map((r) => Number(r.id)))
for (const row of data.recetas) {
  if (!users.has(Number(row.usuario_id)))
    throw new Error(
      `La receta ${row.id} no tiene un autor válido. Corregir el origen antes de importar.`,
    )
  if (
    !String(row.titulo ?? '').trim() ||
    !lines(row.ingredientes).length ||
    !lines(row.pasos).length
  )
    throw new Error(`La receta ${row.id} está incompleta. Corregir el origen antes de importar.`)
}
for (const table of ['favoritos', 'opiniones', 'valoraciones'])
  for (const row of data[table]) {
    if (!users.has(Number(row.usuario_id)) || !recipeIds.has(Number(row.receta_id)))
      throw new Error(`Hay una referencia huérfana en ${table}. Revisar el origen.`)
    if (
      table === 'valoraciones' &&
      (!Number.isInteger(Number(row.puntuacion)) ||
        Number(row.puntuacion) < 1 ||
        Number(row.puntuacion) > 5)
    )
      throw new Error('Valoración inválida en el origen.')
  }
console.log(
  `Exportación validada: ${data.usuarios.length} usuarios, ${data.recetas.length} recetas, ${data.favoritos.length} favoritos, ${data.opiniones.length} comentarios y ${data.valoraciones.length} valoraciones.`,
)
if (process.argv.includes('--dry-run')) {
  console.log('Simulación terminada. No se conectó ni se escribió en Supabase.')
  process.exit(0)
}
const { SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY } = process.env
if (!SUPABASE_URL || !SUPABASE_SERVICE_ROLE_KEY)
  throw new Error(
    'Configurá SUPABASE_URL y SUPABASE_SERVICE_ROLE_KEY en un entorno privado de servidor. No usar variables VITE_.',
  )
const client = createClient(SUPABASE_URL, SUPABASE_SERVICE_ROLE_KEY, {
  auth: { persistSession: false, autoRefreshToken: false },
})
const check = (result) => {
  if (result.error)
    throw new Error(
      `Supabase: ${result.error.code ?? result.error.status ?? 'error'}; revisar el proyecto y el esquema.`,
    )
  return result.data
}
const userMap = new Map(),
  recipeMap = new Map()
for (const u of data.usuarios) {
  let profile = check(
    await client.from('profiles').select('id').eq('legacy_id', Number(u.id)).maybeSingle(),
  )
  if (!profile) {
    // Refuse ambiguous linking of an existing email; never take over an unrelated account.
    // The temporary metadata marker makes reruns recoverable after createUser succeeded.
    let existing,
      page = 1
    do {
      const batch = check(await client.auth.admin.listUsers({ page, perPage: 1000 }))
      existing = batch.users.find((a) => a.email?.toLowerCase() === String(u.email).toLowerCase())
      if (existing || batch.users.length < 1000) break
      page++
    } while (true)
    if (existing && Number(existing.user_metadata?.limalimon_legacy_id) !== Number(u.id))
      throw new Error(
        `El usuario antiguo ${u.id} coincide con una cuenta existente no vinculada. Resolver manualmente antes de continuar.`,
      )
    const user =
      existing ??
      check(
        await client.auth.admin.createUser({
          email: u.email,
          email_confirm: false,
          user_metadata: {
            display_name: String(u.nombre || 'Cocinero/a').slice(0, 80),
            limalimon_legacy_id: Number(u.id),
          },
        }),
      ).user
    if (!user) throw new Error(`No se pudo crear el usuario antiguo ${u.id}`)
    const role =
      process.argv.includes('--include-admin-roles') && u.rol === 'admin' ? 'admin' : 'usuario'
    check(
      await client
        .from('profiles')
        .update({
          legacy_id: Number(u.id),
          display_name: String(u.nombre || 'Cocinero/a').slice(0, 80),
          dietary_preference: u.preferencia_tipo ?? '',
          role,
        })
        .eq('id', user.id),
    )
    profile = { id: user.id }
  }
  userMap.set(Number(u.id), profile.id)
}
const imageRoot = await realpath(imagesDirectory)
for (const r of data.recetas) {
  const author_id = userMap.get(Number(r.usuario_id))
  let image_path = null
  if (r.imagen) {
    if (!/^[\w. -]+\.(jpe?g|png|webp)$/i.test(r.imagen))
      throw new Error(`Nombre de imagen no admitido para receta ${r.id}`)
    const absolute = await realpath(resolve(imageRoot, r.imagen))
    if (!absolute.startsWith(imageRoot + sep))
      throw new Error('La imagen sale del directorio permitido.')
    const bytes = await readFile(absolute)
    if (bytes.length > 5242880) throw new Error(`La imagen de la receta ${r.id} supera 5 MB.`)
    const ext = r.imagen.split('.').pop().toLowerCase()
    image_path = `${author_id}/legacy-${r.id}.${ext}`
    check(
      await client.storage.from('recipe-images').upload(image_path, bytes, {
        contentType: ext === 'jpg' || ext === 'jpeg' ? 'image/jpeg' : `image/${ext}`,
        upsert: true,
      }),
    )
  }
  const row = {
    legacy_id: Number(r.id),
    author_id,
    title: String(r.titulo).trim(),
    description: String(r.descripcion || 'Una receta de la comunidad LimaLimón.').trim(),
    ingredients: lines(r.ingredientes),
    steps: lines(r.pasos),
    dietary_tags: r.tipo ? [String(r.tipo).trim()] : [],
    season: ['primavera', 'verano', 'otoño', 'invierno'].includes(r.estacion)
      ? r.estacion
      : 'todo el año',
    image_path,
    prep_minutes: 30,
    servings: 2,
  }
  // The original database has no time or servings columns. Review these defaults after import.
  const recipe = check(
    await client.from('recipes').upsert(row, { onConflict: 'legacy_id' }).select('id').single(),
  )
  recipeMap.set(Number(r.id), recipe.id)
}
for (const f of data.favoritos)
  check(
    await client.from('favorites').upsert(
      {
        user_id: userMap.get(Number(f.usuario_id)),
        recipe_id: recipeMap.get(Number(f.receta_id)),
      },
      { onConflict: 'user_id,recipe_id' },
    ),
  )
for (const r of data.valoraciones)
  check(
    await client.from('ratings').upsert(
      {
        user_id: userMap.get(Number(r.usuario_id)),
        recipe_id: recipeMap.get(Number(r.receta_id)),
        score: Number(r.puntuacion),
      },
      { onConflict: 'user_id,recipe_id' },
    ),
  )
for (const c of data.opiniones) {
  if (!String(c.comentario ?? '').trim()) continue
  check(
    await client.from('comments').upsert(
      {
        legacy_id: Number(c.id),
        user_id: userMap.get(Number(c.usuario_id)),
        recipe_id: recipeMap.get(Number(c.receta_id)),
        body: String(c.comentario).trim(),
      },
      { onConflict: 'legacy_id' },
    ),
  )
}
console.log(
  'Importación terminada. Los usuarios deben usar «Olvidé mi contraseña» para verificar su correo y crear una contraseña nueva. Revisar tiempos y porciones, que no existían en MySQL. No se enviaron correos automáticamente.',
)
