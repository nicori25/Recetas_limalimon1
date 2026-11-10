<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeft,
  Clock3,
  Users,
  Heart,
  Star,
  ShoppingBasket,
  Pencil,
  Trash2,
  Check,
  ChefHat,
  ChevronRight,
  ChevronLeft,
  X,
  Printer,
} from '@lucide/vue'
import AppDialog from '../components/AppDialog.vue'
import { useApp } from '../stores/app'
import { recipeImage } from '../lib/supabase'
import { friendlyError } from '../lib/utils'
const route = useRoute(),
  router = useRouter(),
  app = useApp()
const recipe = computed(() => app.recipes.find((r) => r.id === route.params.id)),
  body = ref(''),
  error = ref(''),
  busy = ref(false),
  checked = ref<number[]>([]),
  cooking = ref(false),
  step = ref(0),
  confirmDelete = ref(false)
const recipeComments = computed(() => app.comments.filter((c) => c.recipe_id === recipe.value?.id))
const myRating = computed(
  () =>
    app.ratings.find((r) => r.recipe_id === recipe.value?.id && r.user_id === app.profile?.id)
      ?.score ?? 0,
)
const canEdit = computed(
  () => app.profile && (app.profile.id === recipe.value?.author_id || app.isAdmin),
)
async function action(fn: () => Promise<unknown>) {
  busy.value = true
  error.value = ''
  try {
    await fn()
  } catch (e) {
    error.value = friendlyError(e)
  } finally {
    busy.value = false
  }
}
function ingredient(i: number) {
  checked.value = checked.value.includes(i)
    ? checked.value.filter((n) => n !== i)
    : [...checked.value, i]
}
function startCooking() {
  cooking.value = true
  step.value = 0
}
function finishCooking() {
  cooking.value = false
  app.toast('¡A disfrutar! Que salga rico.')
}
function print() {
  window.print()
}
</script>
<template>
  <div v-if="recipe" class="container page recipe-page">
    <RouterLink to="/" class="back-link"><ArrowLeft :size="16" /> Todas las recetas</RouterLink>
    <div class="recipe-heading">
      <div>
        <div class="eyebrow">DE LA COMUNIDAD A TU MESA</div>
        <h1>{{ recipe.title }}</h1>
        <p>{{ recipe.description }}</p>
        <div class="detail-meta">
          <span><Clock3 :size="17" />{{ recipe.prep_minutes }} min</span
          ><span><Users :size="17" />{{ recipe.servings }} porciones</span
          ><span
            ><Star :size="17" />{{ app.average(recipe.id).toFixed(1) }} ({{
              app.ratings.filter((r) => r.recipe_id === recipe!.id).length
            }})</span
          ><span>Por {{ app.authorName(recipe.author_id) }}</span>
        </div>
      </div>
      <div class="recipe-toolbar">
        <button
          class="button secondary"
          :disabled="busy"
          @click="action(() => app.toggleFavorite(recipe!.id))"
        >
          <Heart :size="17" :fill="app.isFavorite(recipe.id) ? 'currentColor' : 'none'" />{{
            app.isFavorite(recipe.id) ? 'Guardada' : 'Guardar'
          }}</button
        ><button class="icon-button" aria-label="Imprimir receta" @click="print">
          <Printer :size="20" /></button
        ><RouterLink
          v-if="canEdit"
          :to="`/editar/${recipe.id}`"
          class="icon-button"
          aria-label="Editar receta"
          ><Pencil :size="20"
        /></RouterLink>
      </div>
    </div>
    <div v-if="error" class="notice error" role="alert">{{ error }}</div>
    <div class="recipe-detail-grid">
      <div>
        <img class="recipe-cover" :src="recipeImage(recipe.image_path)" :alt="recipe.title" />
        <div class="tag-list">
          <span v-for="tag in recipe.dietary_tags" :key="tag">{{ tag }}</span
          ><span>{{ recipe.season }}</span>
        </div>
        <div class="steps-heading">
          <h2>Manos a la obra</h2>
          <button class="button secondary small" @click="startCooking">
            <ChefHat :size="17" /> Modo cocina
          </button>
        </div>
        <ol class="steps-list">
          <li v-for="(text, i) in recipe.steps" :key="i">
            <span>{{ String(i + 1).padStart(2, '0') }}</span>
            <p>{{ text }}</p>
          </li>
        </ol>
      </div>
      <aside class="ingredients-panel">
        <div class="eyebrow">TODO LO QUE NECESITÁS</div>
        <h2>Ingredientes</h2>
        <p>Para {{ recipe.servings }} {{ recipe.servings === 1 ? 'porción' : 'porciones' }}</p>
        <label
          v-for="(text, i) in recipe.ingredients"
          :key="i"
          :class="{ crossed: checked.includes(i) }"
          ><input type="checkbox" :checked="checked.includes(i)" @change="ingredient(i)" /><span>{{
            text
          }}</span></label
        ><button class="button full" @click="app.addShopping(recipe)">
          <ShoppingBasket :size="17" /> A mi lista de compras</button
        ><small
          >Si tenés una alergia o intolerancia, revisá los ingredientes y evitá la contaminación
          cruzada. Las etiquetas las indica quien publica.</small
        >
      </aside>
    </div>
    <section class="community-section">
      <div class="rating-panel">
        <h2>¿Qué te pareció?</h2>
        <p>Tu opinión ayuda a otras personas a elegir.</p>
        <div class="stars">
          <button
            v-for="n in 5"
            :key="n"
            :aria-label="`Valorar con ${n} estrellas`"
            :disabled="busy"
            @click="action(() => app.rate(recipe!.id, n))"
          >
            <Star :size="29" :fill="n <= myRating ? 'currentColor' : 'none'" />
          </button>
        </div>
        <RouterLink v-if="!app.profile" to="/cuenta">Ingresá para valorar y comentar</RouterLink>
      </div>
      <div class="comments">
        <h2>
          Charlas de cocina <span class="count">{{ recipeComments.length }}</span>
        </h2>
        <form
          v-if="app.profile"
          @submit.prevent="
            action(async () => {
              await app.addComment(recipe!.id, body)
              body = ''
            })
          "
        >
          <label for="comment">Compartí tu experiencia</label
          ><textarea
            id="comment"
            v-model="body"
            maxlength="2000"
            required
            rows="3"
            placeholder="¿Le diste tu toque? Contanos…"
          ></textarea
          ><button class="button small" :disabled="busy || !body.trim()">
            Publicar comentario
          </button>
        </form>
        <p v-if="!recipeComments.length" class="muted">
          Esta charla recién empieza. ¿La inaugurás?
        </p>
        <article v-for="comment in recipeComments" :key="comment.id" class="comment">
          <span class="avatar">{{ app.authorName(comment.user_id).charAt(0) }}</span>
          <div>
            <strong>{{ app.authorName(comment.user_id) }}</strong
            ><small>{{ new Date(comment.created_at).toLocaleDateString('es-AR') }}</small>
            <p>{{ comment.body }}</p>
          </div>
          <button
            v-if="app.profile?.id === comment.user_id || app.isAdmin"
            class="icon-button"
            aria-label="Eliminar comentario"
            :disabled="busy"
            @click="action(() => app.deleteComment(comment.id))"
          >
            <Trash2 :size="16" />
          </button>
        </article>
      </div>
    </section>
    <div v-if="canEdit" class="danger-zone">
      <button class="text-button danger" @click="confirmDelete = true">
        <Trash2 :size="16" /> Eliminar receta
      </button>
    </div>
    <AppDialog v-if="confirmDelete" labelled-by="delete-title" @close="confirmDelete = false"
      ><h2 id="delete-title">¿Eliminar esta receta?</h2>
      <p>
        También se eliminarán sus favoritos, comentarios y valoraciones. Esta acción no se puede
        deshacer.
      </p>
      <div class="button-row">
        <button class="button secondary" @click="confirmDelete = false">Cancelar</button
        ><button
          class="button danger-button"
          :disabled="busy"
          @click="
            action(async () => {
              await app.deleteRecipe(recipe!.id)
              await router.push('/')
            })
          "
        >
          Eliminar receta
        </button>
      </div></AppDialog
    >
    <AppDialog v-if="cooking" labelled-by="cooking-title" cooking @close="cooking = false"
      ><button
        class="close-modal icon-button"
        aria-label="Salir del modo cocina"
        @click="cooking = false"
      >
        <X />
      </button>
      <div class="eyebrow">MODO COCINA · PASO {{ step + 1 }} DE {{ recipe.steps.length }}</div>
      <h2 id="cooking-title">{{ recipe.title }}</h2>
      <progress :value="step + 1" :max="recipe.steps.length"></progress>
      <p class="cooking-step">{{ recipe.steps[step] }}</p>
      <div class="button-row">
        <button class="button secondary" :disabled="step === 0" @click="step--">
          <ChevronLeft :size="18" /> Anterior</button
        ><button v-if="step < recipe.steps.length - 1" class="button" @click="step++">
          Siguiente <ChevronRight :size="18" /></button
        ><button v-else class="button" @click="finishCooking">
          ¡Listo! <Check :size="18" />
        </button></div
    ></AppDialog>
  </div>
  <section v-else class="container empty-state">
    <h1>Esta receta ya no está disponible</h1>
    <RouterLink to="/" class="button">Explorar recetas</RouterLink>
  </section>
</template>
