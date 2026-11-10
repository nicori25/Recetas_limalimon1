<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import {
  ArrowUpRight,
  Plus,
  ShoppingBasket,
  Menu,
  X,
  Sprout,
  Heart,
  Shuffle,
  ChefHat,
  UserRound,
  ShieldCheck,
  Check,
  RotateCcw,
} from '@lucide/vue'
import { useApp } from './stores/app'
import { isDemo } from './lib/supabase'
function focusMain() {
  document.getElementById('main-content')?.focus()
}
const app = useApp(),
  route = useRoute(),
  menu = ref(false)
watch(
  () => route.fullPath,
  () => {
    menu.value = false
  },
)
onMounted(() => {
  app.initialize().catch((e) => {
    app.error = String(e)
    app.loading = false
  })
})
</script>
<template>
  <a href="#main-content" class="skip-link" @click.prevent="focusMain">Saltar al contenido</a>
  <div v-if="isDemo" class="demo-bar">
    <span
      ><span class="status-dot"></span> Estás explorando la demo · Los cambios se guardan en este
      navegador</span
    ><button @click="app.resetDemo()" title="Restablecer demo">
      <RotateCcw :size="13" /> Reiniciar demo
    </button>
  </div>
  <header class="site-header">
    <RouterLink to="/" class="brand" aria-label="LimaLimón, inicio"
      ><span class="brand-mark"><Sprout :size="25" /></span
      ><span
        >Lima<span class="brand-light">Limón</span><small>COCINÁ A TU MANERA</small></span
      ></RouterLink
    >
    <button
      class="icon-button mobile-menu"
      aria-label="Abrir menú"
      :aria-expanded="menu"
      @click="menu = !menu"
    >
      <X v-if="menu" /><Menu v-else />
    </button>
    <nav :class="{ open: menu }" aria-label="Navegación principal">
      <RouterLink to="/" :class="{ active: route.path === '/' }">Explorar</RouterLink
      ><RouterLink to="/ruleta"><Shuffle :size="15" /> Ruleta</RouterLink
      ><RouterLink to="/favoritos"><Heart :size="15" /> Favoritos</RouterLink
      ><RouterLink to="/mis-recetas"><ChefHat :size="15" /> Mis recetas</RouterLink>
      <RouterLink v-if="app.isAdmin" to="/admin" title="Administración"
        ><ShieldCheck :size="17" /><span class="sr-only">Administración</span></RouterLink
      >
    </nav>
    <div class="header-actions">
      <RouterLink to="/compras" class="icon-button basket" aria-label="Lista de compras"
        ><ShoppingBasket :size="21" /><span v-if="app.shopping.length">{{
          app.shopping.length
        }}</span></RouterLink
      ><RouterLink to="/publicar" class="button small"
        ><Plus :size="16" /> Compartir receta</RouterLink
      ><RouterLink
        to="/cuenta"
        class="avatar"
        :aria-label="app.profile ? 'Mi cuenta' : 'Iniciar sesión'"
        ><span v-if="app.profile">{{ app.profile.display_name.charAt(0) }}</span
        ><UserRound v-else :size="19"
      /></RouterLink>
    </div>
  </header>
  <main id="main-content" tabindex="-1">
    <div v-if="app.loading" class="loading-state" role="status">
      <span class="spinner"></span> Preparando algo rico…
    </div>
    <div v-else-if="app.error" class="page container">
      <div class="notice error" role="alert">{{ app.error }}</div>
      <button class="button" @click="app.refresh()">Volver a intentar</button>
      <p>
        Si estás conectando Supabase por primera vez, aplicá la migración que figura en la
        documentación.
      </p>
    </div>
    <RouterView v-else :key="route.path" />
  </main>
  <footer class="site-footer container">
    <RouterLink to="/" class="footer-brand"
      ><Sprout :size="21" /> LimaLimón<span>Hecho para compartir.</span></RouterLink
    >
    <p>Comida real. Para todas las formas de comer.</p>
    <RouterLink to="/publicar">Sumá tu receta <ArrowUpRight :size="15" /></RouterLink>
  </footer>
  <Transition name="toast"
    ><div v-if="app.notice" class="toast" role="status">
      <Check :size="18" />{{ app.notice }}
    </div></Transition
  >
</template>
