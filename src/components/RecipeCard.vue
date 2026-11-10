<script setup lang="ts">
import { computed, ref } from 'vue'
import { Heart, Clock3, Star, ArrowUpRight } from '@lucide/vue'
import type { Recipe } from '../lib/types'
import { recipeImage } from '../lib/supabase'
import { friendlyError } from '../lib/utils'
import { useApp } from '../stores/app'
const props = defineProps<{ recipe: Recipe }>(),
  app = useApp(),
  busy = ref(false)
const average = computed(() => app.average(props.recipe.id))
async function favorite() {
  busy.value = true
  try {
    await app.toggleFavorite(props.recipe.id)
  } catch (e) {
    app.toast(friendlyError(e))
  } finally {
    busy.value = false
  }
}
</script>
<template>
  <article class="recipe-card">
    <div class="card-image">
      <RouterLink :to="`/receta/${recipe.id}`" :aria-label="`Ver ${recipe.title}`"
        ><img
          :src="recipeImage(recipe.image_path)"
          :alt="recipe.title"
          loading="lazy" /></RouterLink
      ><span class="image-label">{{ recipe.dietary_tags[0] || 'Casero' }}</span
      ><button
        class="favorite-button"
        :class="{ saved: app.isFavorite(recipe.id) }"
        :aria-label="
          app.isFavorite(recipe.id)
            ? `Quitar ${recipe.title} de favoritos`
            : `Guardar ${recipe.title} en favoritos`
        "
        :aria-pressed="app.isFavorite(recipe.id)"
        :disabled="busy"
        @click="favorite"
      >
        <Heart :size="19" :fill="app.isFavorite(recipe.id) ? 'currentColor' : 'none'" />
      </button>
    </div>
    <div class="card-body">
      <div class="recipe-meta">
        <span><Clock3 :size="13" />{{ recipe.prep_minutes }} min</span
        ><span
          ><Star :size="13" fill="currentColor" />{{ average ? average.toFixed(1) : 'Nueva' }}</span
        >
      </div>
      <h3>
        <RouterLink :to="`/receta/${recipe.id}`">{{ recipe.title }}</RouterLink>
      </h3>
      <p>{{ recipe.description }}</p>
      <div class="card-bottom">
        <span class="author-avatar">{{ app.authorName(recipe.author_id).charAt(0) }}</span
        ><span>{{ app.authorName(recipe.author_id) }}</span
        ><RouterLink
          :to="`/receta/${recipe.id}`"
          class="card-arrow"
          :aria-label="`Cocinar ${recipe.title}`"
          ><ArrowUpRight :size="18"
        /></RouterLink>
      </div>
    </div>
  </article>
</template>
