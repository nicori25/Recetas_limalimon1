<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { Heart, Plus } from '@lucide/vue'
import { useApp } from '../stores/app'
import RecipeCard from '../components/RecipeCard.vue'
import AccessGate from '../components/AccessGate.vue'
const app = useApp(),
  route = useRoute(),
  favorites = computed(() => route.path === '/favoritos')
const recipes = computed(() =>
  app.recipes.filter((r) =>
    favorites.value ? app.isFavorite(r.id) : r.author_id === app.profile?.id,
  ),
)
</script>
<template>
  <section class="container page">
    <AccessGate v-if="!app.profile" /><template v-else
      ><div class="page-heading">
        <div>
          <div class="eyebrow">TU RECETARIO PERSONAL</div>
          <h1>{{ favorites ? 'Para volver a cocinar.' : 'De tu cocina, con amor.' }}</h1>
          <p>
            {{
              favorites
                ? 'Todas esas recetas que te dieron ganas de probar.'
                : 'Tus ideas, tus sabores y tu forma de compartir.'
            }}
          </p>
        </div>
        <RouterLink v-if="!favorites" to="/publicar" class="button"
          ><Plus :size="18" /> Nueva receta</RouterLink
        >
      </div>
      <div v-if="recipes.length" class="recipe-grid">
        <RecipeCard v-for="recipe in recipes" :key="recipe.id" :recipe="recipe" />
      </div>
      <div v-else class="empty-state">
        <Heart :size="36" />
        <h2>
          {{
            favorites
              ? 'Tu próxima favorita te está esperando'
              : 'Todos tenemos una receta para compartir'
          }}
        </h2>
        <p>
          {{
            favorites
              ? 'Tocá el corazón en una receta para guardarla acá.'
              : 'Empezá con ese plato que siempre te sale bien.'
          }}
        </p>
        <RouterLink :to="favorites ? '/' : '/publicar'" class="button">{{
          favorites ? 'Explorar recetas' : 'Compartir mi primera receta'
        }}</RouterLink>
      </div></template
    >
  </section>
</template>
