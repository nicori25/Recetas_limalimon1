<script setup lang="ts">
import { computed, ref } from 'vue'
import { ShoppingBasket, Copy, Trash2 } from '@lucide/vue'
import { useApp } from '../stores/app'
const app = useApp(),
  copied = ref(false),
  confirmation = ref(false)
const groups = computed(() => [...new Set(app.shopping.map((i) => i.recipe))]),
  remaining = computed(() => app.shopping.filter((i) => !i.checked).length)
function clearList() {
  app.clearShopping()
  confirmation.value = false
}
async function copy() {
  try {
    await navigator.clipboard.writeText(
      app.shopping
        .filter((i) => !i.checked)
        .map((i) => `☐ ${i.text} (${i.recipe})`)
        .join('\n'),
    )
    copied.value = true
  } catch {
    app.toast('No se pudo copiar. Podés seleccionar el texto de la lista.')
  }
}
</script>
<template>
  <section class="container page narrow">
    <div class="eyebrow">UN POQUITO DE ORGANIZACIÓN</div>
    <h1>Del recetario al mercado.</h1>
    <p class="intro">Tu lista de compras, siempre a mano. Se guarda en este navegador.</p>
    <div v-if="app.shopping.length" class="shopping-panel">
      <div class="shopping-toolbar">
        <strong>{{ remaining }} ingredientes pendientes</strong
        ><button class="text-button" @click="copy">
          <Copy :size="16" />{{ copied ? '¡Copiado!' : 'Copiar lista' }}
        </button>
      </div>
      <section v-for="group in groups" :key="group" class="shopping-group">
        <h2>{{ group }}</h2>
        <label
          v-for="item in app.shopping.filter((i) => i.recipe === group)"
          :key="item.id"
          :class="{ crossed: item.checked }"
          ><input type="checkbox" :checked="item.checked" @change="app.toggleShopping(item.id)" />{{
            item.text
          }}</label
        >
      </section>
      <button class="text-button danger" @click="confirmation = true">
        <Trash2 :size="16" /> Vaciar lista
      </button>
      <div v-if="confirmation" class="notice">
        <p>¿Querés borrar todos los ingredientes?</p>
        <div class="button-row">
          <button class="button secondary small" @click="confirmation = false">Cancelar</button
          ><button class="button small" @click="clearList">Vaciar</button>
        </div>
      </div>
    </div>
    <div v-else class="empty-state">
      <ShoppingBasket :size="40" />
      <h2>Una lista vacía, mil posibilidades.</h2>
      <p>Agregá ingredientes desde cualquier receta y prepará tu próxima compra.</p>
      <RouterLink to="/" class="button">Buscar algo rico</RouterLink>
    </div>
  </section>
</template>
