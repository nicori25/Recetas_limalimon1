<script setup lang="ts">
import { computed, onUnmounted, ref } from 'vue'
import { Shuffle, ArrowUpRight, Sparkles } from '@lucide/vue'
import { useApp } from '../stores/app'
import { currentSeason, filterRecipes } from '../lib/utils'
import { DIETS, SEASONS } from '../lib/types'
import type { Recipe } from '../lib/types'
import RecipeCard from '../components/RecipeCard.vue'
const app = useApp(),
  season = ref(currentSeason()),
  diet = ref(''),
  result = ref<Recipe | null>(null),
  spinning = ref(false)
const options = computed(() =>
  filterRecipes(app.recipes, '', diet.value ? [diet.value] : [], season.value),
)
let timer: ReturnType<typeof setTimeout>
function spin() {
  if (!options.value.length || spinning.value) return
  result.value = null
  spinning.value = true
  const candidates = [...options.value]
  timer = setTimeout(() => {
    result.value = candidates[Math.floor(Math.random() * candidates.length)]!
    spinning.value = false
  }, 900)
}
onUnmounted(() => clearTimeout(timer))
</script>
<template>
  <section class="container page roulette-page">
    <div class="eyebrow">DEJÁ QUE LA TEMPORADA TE SORPRENDA</div>
    <h1>Hoy decide la ruleta.</h1>
    <p>
      Menos «¿qué comemos?», más ganas de cocinar.<br />Encontrá una idea con lo mejor de cada
      estación.
    </p>
    <div class="roulette-layout">
      <div class="roulette-box">
        <span class="roulette-symbol" :class="{ spinning }"><Shuffle :size="60" /></span>
        <h2>Un giro, una nueva idea.</h2>
        <div class="form-grid">
          <label
            >Temporada<select v-model="season" :disabled="spinning" @change="result = null">
              <option v-for="s in SEASONS" :key="s" :value="s">{{ s }}</option>
            </select></label
          ><label
            >Tu preferencia<select v-model="diet" :disabled="spinning" @change="result = null">
              <option value="">Todas</option>
              <option v-for="d in DIETS" :key="d" :value="d">{{ d }}</option>
            </select></label
          >
        </div>
        <p>{{ options.length }} recetas para sorprenderte</p>
        <button class="button" :disabled="spinning || !options.length" @click="spin">
          <Sparkles :size="18" />{{ spinning ? 'Mezclando ideas…' : 'Girar la ruleta' }}
        </button>
        <p v-if="!options.length" class="notice">
          Todavía no hay recetas con esa combinación. Probá otra temporada.
        </p>
      </div>
      <div class="roulette-result" aria-live="polite">
        <RecipeCard v-if="result" :recipe="result" />
        <div v-else class="roulette-placeholder">
          <span>✳</span>
          <h3>Algo rico está por llegar.</h3>
          <p>Elegí tus preferencias y dale un giro.</p>
          <RouterLink to="/">O explorá todas las recetas <ArrowUpRight :size="16" /></RouterLink>
        </div>
      </div>
    </div>
  </section>
</template>
