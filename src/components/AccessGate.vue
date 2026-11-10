<script setup lang="ts">
import { ChefHat, ArrowRight } from '@lucide/vue'
defineProps<{ title?: string }>()
</script>
<template>
  <section class="empty-state">
    <span class="empty-icon"><ChefHat :size="32" /></span>
    <h1>{{ title || 'Tu lugar en la cocina' }}</h1>
    <p>
      Ingresá a tu cuenta para guardar tus favoritos, compartir recetas y ser parte de la comunidad.
    </p>
    <RouterLink to="/cuenta" class="button">Ingresar <ArrowRight :size="16" /></RouterLink>
  </section>
</template>
