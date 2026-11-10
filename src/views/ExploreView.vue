<script setup lang="ts">
import { computed, ref } from 'vue'
import {
  Search,
  SlidersHorizontal,
  ArrowDown,
  ArrowUpRight,
  Leaf,
  WheatOff,
  MilkOff,
  Sprout,
  X,
  Sparkles,
} from '@lucide/vue'
import { publicAsset } from '../lib/urls'
import { useApp } from '../stores/app'
import { currentSeason, filterRecipes } from '../lib/utils'
import { DIETS, SEASONS } from '../lib/types'
import RecipeCard from '../components/RecipeCard.vue'
const app = useApp(),
  query = ref(''),
  diets = ref<string[]>([]),
  season = ref(''),
  maxMinutes = ref(0),
  sort = ref('recent'),
  filtersOpen = ref(false)
const icons = [Sprout, Leaf, WheatOff, MilkOff]
const availableDiets = computed(() => [
  ...DIETS,
  ...[...new Set(app.recipes.flatMap((r) => r.dietary_tags))]
    .filter((t) => !DIETS.includes(t as (typeof DIETS)[number]))
    .sort(),
])
const filtered = computed(() => {
  const rows = filterRecipes(app.recipes, query.value, diets.value, season.value, maxMinutes.value)
  return [...rows].sort((a, b) =>
    sort.value === 'rating'
      ? app.average(b.id) - app.average(a.id)
      : sort.value === 'quick'
        ? a.prep_minutes - b.prep_minutes
        : b.created_at.localeCompare(a.created_at),
  )
})
const featured = computed(
  () => app.recipes.find((r) => r.id === 'ensalada-primavera') ?? app.recipes[0],
)
function toggle(diet: string) {
  diets.value = diets.value.includes(diet)
    ? diets.value.filter((d) => d !== diet)
    : [...diets.value, diet]
}
function clear() {
  query.value = ''
  diets.value = []
  season.value = ''
  maxMinutes.value = 0
}
function discover() {
  document.querySelector('#recetas')?.scrollIntoView({ behavior: 'smooth' })
}
</script>
<template>
  <section class="hero container">
    <div class="hero-copy">
      <div class="eyebrow"><span class="tiny-leaf">✳</span> RICO, SIMPLE Y A TU MANERA</div>
      <h1>Una buena receta.<br />Mil formas de<br /><em>sentirte bien.</em></h1>
      <p>
        Descubrí platos que van con vos. Sin complicaciones,<br class="desktop-only" />
        con ingredientes reales y mucho para compartir.
      </p>
      <button class="button hero-button" @click="discover">
        Encontrá tu próxima receta <ArrowUpRight :size="18" />
      </button>
      <div class="hero-note">
        <span class="mini-avatars"><i>A</i><i>M</i><i>L</i></span
        ><span>Una comunidad con <strong>hambre de compartir.</strong></span>
      </div>
    </div>
    <div class="hero-art">
      <div class="hero-circle"></div>
      <span class="hero-script">un poquito de verde,<br />un montón de sabor.</span
      ><img
        :src="publicAsset('/images/hero-salad.svg')"
        alt="Ilustración de una ensalada fresca con vegetales y limón"
        class="hero-food"
      /><span class="hero-stamp"
        >100%<small>GANAS DE<br />COCINAR</small></span
      ><RouterLink v-if="featured" :to="`/receta/${featured.id}`" class="hero-feature"
        ><span class="feature-icon"><Leaf :size="23" /></span
        ><span
          ><small>HOY TE RECOMENDAMOS</small><strong>{{ featured.title }}</strong
          ><span>{{ featured.prep_minutes }} min · Fresco y fácil</span></span
        ><ArrowUpRight :size="21"
      /></RouterLink>
    </div>
  </section>
  <section class="values-strip">
    <div class="container">
      <span><Sprout :size="19" /> Para cada forma de comer</span><i>✳</i
      ><span><Leaf :size="19" /> Ingredientes de verdad</span><i>✳</i
      ><span><Sparkles :size="19" /> Hecho en comunidad</span>
    </div>
  </section>
  <section id="recetas" class="container recipes-section">
    <div class="section-title">
      <div>
        <div class="eyebrow">TU PRÓXIMA COMIDA EMPIEZA ACÁ</div>
        <h2>¿Qué cocinamos hoy?<span>✳</span></h2>
      </div>
      <span class="season-note"
        ><span class="status-dot"></span> Estamos en {{ currentSeason() }}</span
      >
    </div>
    <div class="search-row">
      <div class="search-input">
        <Search :size="21" /><input
          v-model="query"
          type="search"
          aria-label="Buscar recetas o ingredientes"
          placeholder="Buscá una receta, un ingrediente, una idea…"
        /><kbd>/</kbd>
      </div>
      <button
        class="button secondary filter-button"
        :aria-expanded="filtersOpen"
        @click="filtersOpen = !filtersOpen"
      >
        <SlidersHorizontal :size="18" /> Filtros
        <span v-if="season || maxMinutes" class="filter-dot"></span>
      </button>
    </div>
    <div class="diet-row">
      <button class="diet-chip" :class="{ selected: !diets.length }" @click="diets = []">
        Todas las recetas</button
      ><button
        v-for="(diet, index) in availableDiets"
        :key="diet"
        class="diet-chip"
        :class="{ selected: diets.includes(diet) }"
        :aria-pressed="diets.includes(diet)"
        @click="toggle(diet)"
      >
        <component :is="icons[index] || Leaf" :size="16" />{{ diet }}
      </button>
    </div>
    <div v-if="filtersOpen" class="filters-panel">
      <label
        >Temporada<select v-model="season">
          <option value="">Todo el año</option>
          <option v-for="s in SEASONS" :key="s" :value="s">{{ s }}</option>
        </select></label
      ><label
        >Tiempo máximo<select v-model.number="maxMinutes">
          <option :value="0">Sin límite</option>
          <option :value="15">15 minutos</option>
          <option :value="30">30 minutos</option>
          <option :value="60">1 hora</option>
        </select></label
      ><button class="text-button" @click="clear"><X :size="15" /> Limpiar filtros</button>
      <p>Los filtros alimentarios se combinan. Revisá siempre las etiquetas de tus ingredientes.</p>
    </div>
    <div class="results-row">
      <p>
        <strong>{{ filtered.length }}</strong> recetas para inspirarte
      </p>
      <label
        >Ordenar por
        <select v-model="sort" aria-label="Ordenar recetas">
          <option value="recent">Más recientes</option>
          <option value="rating">Mejor valoradas</option>
          <option value="quick">Más rápidas</option></select
        ><ArrowDown :size="14"
      /></label>
    </div>
    <div v-if="filtered.length" class="recipe-grid">
      <RecipeCard v-for="recipe in filtered" :key="recipe.id" :recipe="recipe" />
    </div>
    <div v-else class="empty-state">
      <Search :size="36" />
      <h3>Probemos con otros ingredientes</h3>
      <p>No encontramos recetas con estos filtros.</p>
      <button class="button secondary" @click="clear">Ver todas las recetas</button>
    </div>
    <div class="community-banner">
      <div>
        <div class="eyebrow">DE TU COCINA A LA COMUNIDAD</div>
        <h2>Las mejores recetas<br />son las que se comparten.</h2>
        <p>Ese plato que siempre te piden también tiene un lugar acá.</p>
      </div>
      <RouterLink to="/publicar" class="button light"
        >Compartí tu receta <ArrowUpRight :size="18" /></RouterLink
      ><span class="banner-decoration">✳</span>
    </div>
  </section>
</template>
