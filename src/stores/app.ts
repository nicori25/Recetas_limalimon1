import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { supabase, isDemo, configurationError } from '../lib/supabase'
import { demoAuthors, demoProfile, demoRatings, demoRecipes } from '../data/demo'
import type { Comment, Favorite, Profile, Rating, Recipe, ShoppingItem } from '../lib/types'
import { friendlyError } from '../lib/utils'
import type { Session } from '@supabase/supabase-js'
const DEMO_KEY = 'limalimon-demo-v2'
export const useApp = defineStore('app', () => {
  const recipes = ref<Recipe[]>([]),
    profiles = ref<Profile[]>([]),
    comments = ref<Comment[]>([]),
    ratings = ref<Rating[]>([]),
    favorites = ref<Favorite[]>([])
  const profile = ref<Profile | null>(null),
    session = ref<Session | null>(null),
    loading = ref(true),
    error = ref(''),
    notice = ref('')
  const shopping = ref<ShoppingItem[]>(readShopping())
  const isAdmin = computed(() => profile.value?.role === 'admin' && !profile.value.disabled)
  let noticeTimer: ReturnType<typeof setTimeout> | undefined
  function toast(message: string) {
    notice.value = message
    clearTimeout(noticeTimer)
    noticeTimer = setTimeout(() => {
      notice.value = ''
    }, 4500)
  }
  function persistDemo() {
    if (isDemo)
      localStorage.setItem(
        DEMO_KEY,
        JSON.stringify({
          recipes: recipes.value,
          profiles: profiles.value,
          comments: comments.value,
          ratings: ratings.value,
          favorites: favorites.value,
          loggedIn: !!profile.value,
        }),
      )
  }
  function readShopping(): ShoppingItem[] {
    try {
      return JSON.parse(localStorage.getItem('limalimon-shopping-v1') || '[]')
    } catch {
      return []
    }
  }
  function saveShopping() {
    localStorage.setItem('limalimon-shopping-v1', JSON.stringify(shopping.value))
  }
  function addShopping(recipe: Recipe) {
    for (const text of recipe.ingredients) {
      if (!shopping.value.some((i) => i.text === text && i.recipe === recipe.title))
        shopping.value.push({ id: crypto.randomUUID(), text, recipe: recipe.title, checked: false })
    }
    saveShopping()
    toast('Ingredientes agregados a tu lista de compras')
  }
  function toggleShopping(id: string) {
    const item = shopping.value.find((i) => i.id === id)
    if (item) item.checked = !item.checked
    saveShopping()
  }
  function clearShopping() {
    shopping.value = []
    saveShopping()
  }
  async function initialize() {
    if (configurationError) {
      error.value = configurationError
      loading.value = false
      return
    }
    if (isDemo) {
      let data
      try {
        data = JSON.parse(localStorage.getItem(DEMO_KEY) || 'null')
      } catch {
        localStorage.removeItem(DEMO_KEY)
      }
      recipes.value = data?.recipes ?? structuredClone(demoRecipes)
      profiles.value = data?.profiles ?? structuredClone(demoAuthors)
      comments.value = data?.comments ?? []
      ratings.value = data?.ratings ?? structuredClone(demoRatings)
      favorites.value = data?.favorites ?? []
      if (data?.loggedIn)
        profile.value = profiles.value.find((p) => p.id === demoProfile.id) ?? { ...demoProfile }
      loading.value = false
      return
    }
    const { data, error: authError } = await supabase!.auth.getSession()
    if (authError) error.value = friendlyError(authError)
    session.value = data.session
    if (data.session && new URLSearchParams(window.location.search).get('flow') === 'recovery')
      window.location.hash = '#/cuenta?recovery=1'
    await refresh()
    supabase!.auth.onAuthStateChange((event, next) => {
      session.value = next
      if (
        event === 'PASSWORD_RECOVERY' ||
        (next && new URLSearchParams(window.location.search).get('flow') === 'recovery')
      ) {
        window.location.hash = '#/cuenta?recovery=1'
      }
      // Defer queries: awaiting Supabase calls inside this callback can deadlock its auth lock.
      setTimeout(() => {
        void refresh()
      }, 0)
    })
  }
  // Supabase defaults to a maximum of 1000 rows per response. Page through the
  // catalogue and interactions so a growing community is not silently truncated.
  async function allRows<T>(table: string, columns = '*', sortKey = 'id', secondaryKey?: string) {
    const data: T[] = []
    for (let start = 0; ; start += 1000) {
      let query = supabase!.from(table).select(columns).order(sortKey)
      if (secondaryKey) query = query.order(secondaryKey)
      const result = await query.range(start, start + 999).overrideTypes<T[], { merge: false }>()
      if (result.error) return { data: null, error: result.error }
      data.push(...result.data)
      if (result.data.length < 1000) return { data, error: null }
    }
  }
  async function refresh() {
    if (configurationError) {
      error.value = configurationError
      loading.value = false
      return
    }
    if (isDemo) return
    loading.value = true
    error.value = ''
    try {
      const results = await Promise.all([
        allRows<Recipe>('recipes'),
        allRows<Profile>('profiles', 'id,display_name,dietary_preference,role,disabled'),
        allRows<Comment>('comments', '*', 'created_at', 'id'),
        allRows<Rating>('ratings', '*', 'user_id', 'recipe_id'),
        session.value
          ? allRows<Favorite>('favorites', '*', 'user_id', 'recipe_id')
          : Promise.resolve({ data: [], error: null }),
      ])
      for (const result of results) if (result.error) throw result.error
      recipes.value = results[0].data as Recipe[]
      profiles.value = results[1].data as Profile[]
      comments.value = results[2].data as Comment[]
      ratings.value = results[3].data as Rating[]
      favorites.value = results[4].data as Favorite[]
      profile.value = profiles.value.find((p) => p.id === session.value?.user.id) ?? null
      if (profile.value?.disabled) {
        await signOut()
        throw new Error('Tu cuenta está desactivada. Contactá a la administración.')
      }
    } catch (e) {
      error.value = friendlyError(e)
    } finally {
      loading.value = false
    }
  }
  function enterDemo() {
    profile.value = profiles.value.find((p) => p.id === demoProfile.id) ?? { ...demoProfile }
    persistDemo()
    toast('Entraste a la demo. Los cambios se guardan solo en este navegador.')
  }
  async function signOut() {
    if (supabase) {
      const { error } = await supabase.auth.signOut()
      if (error) throw error
    }
    session.value = null
    profile.value = null
    if (!isDemo) favorites.value = []
    persistDemo()
  }
  function requireUser() {
    if (!profile.value || profile.value.disabled) throw new Error('Iniciá sesión para continuar.')
    return profile.value.id
  }
  function authorName(id: string) {
    return profiles.value.find((p) => p.id === id)?.display_name ?? 'Comunidad LimaLimón'
  }
  function average(id: string) {
    const votes = ratings.value.filter((r) => r.recipe_id === id)
    return votes.length ? votes.reduce((n, r) => n + r.score, 0) / votes.length : 0
  }
  function isFavorite(id: string) {
    return favorites.value.some((f) => f.recipe_id === id && f.user_id === profile.value?.id)
  }
  async function toggleFavorite(id: string) {
    const user_id = requireUser(),
      exists = isFavorite(id)
    if (supabase) {
      const { error } = exists
        ? await supabase.from('favorites').delete().match({ user_id, recipe_id: id })
        : await supabase.from('favorites').insert({ user_id, recipe_id: id })
      if (error) throw error
    }
    favorites.value = favorites.value.filter((f) => !(f.user_id === user_id && f.recipe_id === id))
    if (!exists) favorites.value.push({ user_id, recipe_id: id })
    persistDemo()
  }
  async function rate(id: string, score: number) {
    const user_id = requireUser()
    if (!Number.isInteger(score) || score < 1 || score > 5)
      throw new Error('Elegí entre 1 y 5 estrellas.')
    if (supabase) {
      const { error } = await supabase
        .from('ratings')
        .upsert({ user_id, recipe_id: id, score }, { onConflict: 'user_id,recipe_id' })
      if (error) throw error
    }
    ratings.value = ratings.value.filter((r) => !(r.user_id === user_id && r.recipe_id === id))
    ratings.value.push({ user_id, recipe_id: id, score })
    persistDemo()
    toast('¡Gracias por tu valoración!')
  }
  async function addComment(recipe_id: string, body: string) {
    const user_id = requireUser()
    body = body.trim()
    if (!body || body.length > 2000)
      throw new Error('Escribí un comentario de hasta 2000 caracteres.')
    const item = {
      id: crypto.randomUUID(),
      recipe_id,
      user_id,
      body,
      created_at: new Date().toISOString(),
    }
    if (supabase) {
      const { error } = await supabase.from('comments').insert(item)
      if (error) throw error
    }
    comments.value.push(item)
    persistDemo()
  }
  async function deleteComment(id: string) {
    requireUser()
    if (supabase) {
      const { error } = await supabase.from('comments').delete().eq('id', id)
      if (error) throw error
    }
    comments.value = comments.value.filter((c) => c.id !== id)
    persistDemo()
  }
  async function saveRecipe(
    input: Omit<Recipe, 'id' | 'created_at' | 'author_id'>,
    id?: string,
    image?: File,
  ) {
    const user_id = requireUser()
    let uploaded: string | null = null
    const existing = recipes.value.find((r) => r.id === id)
    if (id && !existing) throw new Error('La receta ya no existe.')
    if (existing && existing.author_id !== user_id && !isAdmin.value)
      throw new Error('Solo podés editar tus propias recetas.')
    if (image) {
      if (
        !['image/jpeg', 'image/png', 'image/webp'].includes(image.type) ||
        image.size > 5 * 1024 * 1024
      )
        throw new Error('Elegí una imagen JPG, PNG o WebP de hasta 5 MB.')
      if (supabase) {
        const extension = image.type.split('/')[1]
        uploaded = `${user_id}/${crypto.randomUUID()}.${extension}`
        const { error } = await supabase.storage
          .from('recipe-images')
          .upload(uploaded, image, { contentType: image.type, upsert: false })
        if (error) throw error
      } else {
        uploaded = await new Promise<string>((resolve, reject) => {
          const reader = new FileReader()
          reader.onload = () => resolve(String(reader.result))
          reader.onerror = reject
          reader.readAsDataURL(image)
        })
      }
    }
    const recipe: Recipe = {
      ...input,
      id: id ?? crypto.randomUUID(),
      author_id: existing?.author_id ?? user_id,
      created_at: existing?.created_at ?? new Date().toISOString(),
      image_path: uploaded ?? input.image_path,
    }
    try {
      if (supabase) {
        const { error } = id
          ? await supabase.from('recipes').update(inputWithImage()).eq('id', id)
          : await supabase.from('recipes').insert(recipe)
        if (error) throw error
      }
      function inputWithImage() {
        return { ...input, image_path: recipe.image_path }
      }
    } catch (e) {
      if (uploaded && supabase) await supabase.storage.from('recipe-images').remove([uploaded])
      throw e
    }
    recipes.value = [recipe, ...recipes.value.filter((r) => r.id !== recipe.id)]
    persistDemo()
    toast(id ? 'Receta actualizada' : '¡Tu receta ya es parte de LimaLimón!')
    return recipe.id
  }
  async function deleteRecipe(id: string) {
    const user_id = requireUser()
    const r = recipes.value.find((r) => r.id === id)
    if (!r || (r.author_id !== user_id && !isAdmin.value)) throw new Error('No tenés permiso.')
    if (supabase) {
      const { error } = await supabase.from('recipes').delete().eq('id', id)
      if (error) throw error
    }
    recipes.value = recipes.value.filter((r) => r.id !== id)
    favorites.value = favorites.value.filter((f) => f.recipe_id !== id)
    comments.value = comments.value.filter((c) => c.recipe_id !== id)
    ratings.value = ratings.value.filter((r) => r.recipe_id !== id)
    persistDemo()
    toast('Receta eliminada')
  }
  async function saveProfile(display_name: string, dietary_preference: string) {
    const id = requireUser()
    if (!display_name.trim()) throw new Error('Ingresá tu nombre.')
    if (supabase) {
      const { error } = await supabase
        .from('profiles')
        .update({ display_name: display_name.trim(), dietary_preference })
        .eq('id', id)
      if (error) throw error
    }
    Object.assign(profile.value!, { display_name: display_name.trim(), dietary_preference })
    const row = profiles.value.find((p) => p.id === id)
    if (row) Object.assign(row, profile.value)
    persistDemo()
    toast('Perfil actualizado')
  }
  async function moderateUser(id: string, role: string, disabled: boolean) {
    if (!isAdmin.value) throw new Error('No tenés permiso.')
    if (id === profile.value?.id) throw new Error('No podés modificar tus propios permisos.')
    if (supabase) {
      const { error } = await supabase.rpc('admin_update_user', {
        target_id: id,
        new_role: role,
        new_disabled: disabled,
      })
      if (error) throw error
    }
    const p = profiles.value.find((p) => p.id === id)
    if (p) Object.assign(p, { role, disabled })
    persistDemo()
    toast('Usuario actualizado')
  }
  async function deleteUser(id: string) {
    if (!isAdmin.value || id === profile.value?.id)
      throw new Error('No podés eliminar esta cuenta.')
    if (supabase) {
      const { error } = await supabase.rpc('admin_delete_user', { target_id: id })
      if (error) throw error
    }
    profiles.value = profiles.value.filter((p) => p.id !== id)
    const ids = recipes.value.filter((r) => r.author_id === id).map((r) => r.id)
    recipes.value = recipes.value.filter((r) => r.author_id !== id)
    comments.value = comments.value.filter((c) => c.user_id !== id && !ids.includes(c.recipe_id))
    favorites.value = favorites.value.filter((f) => f.user_id !== id && !ids.includes(f.recipe_id))
    ratings.value = ratings.value.filter((r) => r.user_id !== id && !ids.includes(r.recipe_id))
    persistDemo()
    toast('Cuenta eliminada')
  }
  async function deleteRating(recipe_id: string, user_id: string) {
    requireUser()
    if (supabase) {
      const { error } = await supabase.from('ratings').delete().match({ recipe_id, user_id })
      if (error) throw error
    }
    ratings.value = ratings.value.filter(
      (r) => !(r.recipe_id === recipe_id && r.user_id === user_id),
    )
    persistDemo()
  }
  function resetDemo() {
    if (
      window.confirm(
        '¿Restablecer la demo? Se borrarán las recetas y las interacciones de prueba de este navegador. Tu lista de compras se conserva.',
      )
    ) {
      localStorage.removeItem(DEMO_KEY)
      window.location.reload()
    }
  }
  return {
    recipes,
    profiles,
    comments,
    ratings,
    favorites,
    profile,
    session,
    loading,
    error,
    notice,
    isAdmin,
    shopping,
    initialize,
    refresh,
    enterDemo,
    signOut,
    authorName,
    average,
    isFavorite,
    toggleFavorite,
    rate,
    addComment,
    deleteComment,
    saveRecipe,
    deleteRecipe,
    saveProfile,
    moderateUser,
    deleteUser,
    deleteRating,
    addShopping,
    toggleShopping,
    clearShopping,
    resetDemo,
    toast,
  }
})
