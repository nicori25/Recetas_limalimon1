<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Upload, Check, Plus, X } from '@lucide/vue'
import { useApp } from '../stores/app'
import { DIETS, SEASONS } from '../lib/types'
import { friendlyError, lines } from '../lib/utils'
import { recipeImage } from '../lib/supabase'
import AccessGate from '../components/AccessGate.vue'
const app = useApp(),
  route = useRoute(),
  router = useRouter(),
  id = route.params.id as string | undefined
const existing = computed(() => app.recipes.find((r) => r.id === id))
const allowed = computed(
  () => !id || (existing.value && (existing.value.author_id === app.profile?.id || app.isAdmin)),
)
const title = ref(existing.value?.title ?? ''),
  description = ref(existing.value?.description ?? ''),
  ingredients = ref(existing.value?.ingredients.join('\n') ?? ''),
  steps = ref(existing.value?.steps.join('\n') ?? ''),
  tags = ref<string[]>([...(existing.value?.dietary_tags ?? [])]),
  customTag = ref(''),
  season = ref(existing.value?.season ?? 'todo el año'),
  minutes = ref(existing.value?.prep_minutes ?? 30),
  servings = ref(existing.value?.servings ?? 2),
  image = ref<File>(),
  imagePreview = ref(''),
  busy = ref(false),
  error = ref('')
function chooseImage(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  if (
    !['image/jpeg', 'image/png', 'image/webp'].includes(file.type) ||
    file.size > 5 * 1024 * 1024
  ) {
    error.value = 'La imagen debe ser JPG, PNG o WebP y pesar hasta 5 MB.'
    return
  }
  image.value = file
  const reader = new FileReader()
  reader.onload = () => {
    imagePreview.value = String(reader.result)
  }
  reader.readAsDataURL(file)
  error.value = ''
}
function addTag() {
  const tag = customTag.value.trim().toLowerCase()
  if (tag && tag.length <= 40 && !tags.value.includes(tag)) tags.value.push(tag)
  customTag.value = ''
}
async function save() {
  busy.value = true
  error.value = ''
  try {
    if (
      !title.value.trim() ||
      !description.value.trim() ||
      !lines(ingredients.value).length ||
      !lines(steps.value).length
    )
      throw new Error('Completá el título, la descripción, los ingredientes y los pasos.')
    if (lines(ingredients.value).length > 100 || lines(steps.value).length > 100)
      throw new Error('Podés agregar hasta 100 ingredientes y 100 pasos.')
    const nextId = await app.saveRecipe(
      {
        title: title.value.trim(),
        description: description.value.trim(),
        ingredients: lines(ingredients.value),
        steps: lines(steps.value),
        dietary_tags: tags.value,
        season: season.value,
        prep_minutes: minutes.value,
        servings: servings.value,
        image_path: existing.value?.image_path ?? null,
      },
      id,
      image.value,
    )
    await router.push(`/receta/${nextId}`)
  } catch (e) {
    error.value = friendlyError(e)
  } finally {
    busy.value = false
  }
}
</script>
<template>
  <section class="container page editor-page">
    <AccessGate v-if="!app.profile" title="Tu receta merece ser compartida" />
    <div v-else-if="!allowed" class="empty-state">
      <h1>Esta receta no está disponible para editar</h1>
      <RouterLink to="/mis-recetas" class="button">Ir a mis recetas</RouterLink>
    </div>
    <template v-else
      ><RouterLink to="/mis-recetas" class="back-link"
        ><ArrowLeft :size="16" /> Mis recetas</RouterLink
      >
      <div class="eyebrow">LAS BUENAS IDEAS SE COMPARTEN</div>
      <h1>{{ id ? 'Un toque más a tu receta.' : '¿Qué sale de tu cocina?' }}</h1>
      <p class="intro">No hace falta ser chef. Solo tener algo rico para compartir.</p>
      <form class="editor-form" @submit.prevent="save">
        <div>
          <section class="form-section">
            <h2><span>01</span> Lo primero, lo rico.</h2>
            <label
              >Nombre de tu receta<input
                v-model="title"
                required
                maxlength="140"
                placeholder="Ej. La tarta de verduras de los domingos" /></label
            ><label
              >Contanos un poquito<textarea
                v-model="description"
                required
                maxlength="1500"
                rows="3"
                placeholder="¿Qué la hace especial? ¿Cuándo te gusta prepararla?"
              ></textarea>
            </label>
            <div class="form-grid">
              <label
                >Tiempo total (minutos)<input
                  v-model.number="minutes"
                  type="number"
                  min="1"
                  max="2880"
                  required /></label
              ><label
                >Porciones<input v-model.number="servings" type="number" min="1" max="100" required
              /></label>
            </div>
          </section>
          <section class="form-section">
            <h2><span>02</span> Ingredientes y un poco de magia.</h2>
            <label
              >Ingredientes <small>Uno por línea, con su cantidad.</small
              ><textarea
                v-model="ingredients"
                rows="7"
                required
                maxlength="15000"
                placeholder="2 tazas de harina&#10;1 cucharada de aceite de oliva&#10;Una pizca de sal"
              ></textarea></label
            ><label
              >Paso a paso <small>Un paso por línea. Lo simple también cuenta.</small
              ><textarea
                v-model="steps"
                rows="8"
                required
                maxlength="30000"
                placeholder="Precalentá el horno a 180 °C.&#10;Mezclá los ingredientes secos en un bowl."
              ></textarea>
            </label>
          </section>
        </div>
        <aside>
          <section class="form-section">
            <h2><span>03</span> Dale tu toque.</h2>
            <label class="upload-area"
              ><img
                v-if="imagePreview || existing?.image_path"
                :src="imagePreview || recipeImage(existing?.image_path ?? null)"
                alt="Vista previa de tu receta" /><template v-else
                ><Upload :size="30" /><strong>Una foto que dé ganas</strong
                ><span>JPG, PNG o WebP · Hasta 5 MB</span></template
              ><input
                type="file"
                accept="image/jpeg,image/png,image/webp"
                aria-label="Imagen de la receta"
                @change="chooseImage"
            /></label>
            <fieldset>
              <legend>Formas de comer</legend>
              <label v-for="diet in DIETS" :key="diet" class="checkbox-label"
                ><input v-model="tags" type="checkbox" :value="diet" />{{ diet }}</label
              >
            </fieldset>
            <label
              >Otra preferencia <small>Ej. sin maní</small>
              <div class="inline-input">
                <input v-model="customTag" maxlength="40" @keydown.enter.prevent="addTag" /><button
                  type="button"
                  class="icon-button"
                  aria-label="Agregar preferencia"
                  @click="addTag"
                >
                  <Plus :size="18" />
                </button></div
            ></label>
            <div class="tag-list">
              <button
                v-for="tag in tags.filter((t) => !DIETS.includes(t as (typeof DIETS)[number]))"
                :key="tag"
                type="button"
                @click="tags = tags.filter((t) => t !== tag)"
              >
                {{ tag }}<X :size="13" />
              </button>
            </div>
            <label
              >Mejor temporada<select v-model="season">
                <option v-for="s in SEASONS" :key="s" :value="s">{{ s }}</option>
              </select></label
            >
            <p class="form-hint">
              Revisá bien las etiquetas alimentarias. Ayudan a otras personas a elegir lo que pueden
              comer.
            </p>
          </section>
          <div v-if="error" class="notice error" role="alert">{{ error }}</div>
          <button class="button full" :disabled="busy">
            <Check :size="18" />{{
              busy ? 'Guardando tu receta…' : id ? 'Guardar cambios' : 'Publicar receta'
            }}
          </button>
        </aside>
      </form></template
    >
  </section>
</template>
