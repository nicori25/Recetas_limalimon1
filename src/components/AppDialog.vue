<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
defineProps<{ labelledBy: string; cooking?: boolean }>()
const emit = defineEmits<{ close: [] }>()
const dialog = ref<HTMLDialogElement>()
let previous: HTMLElement | null = null
onMounted(() => {
  previous = document.activeElement as HTMLElement
  dialog.value?.showModal()
})
onUnmounted(() => {
  dialog.value?.close()
  previous?.focus()
})
</script>
<template>
  <Teleport to="body"
    ><dialog
      ref="dialog"
      class="dialog-shell"
      :aria-labelledby="labelledBy"
      @cancel.prevent="emit('close')"
      @click="
        (e) => {
          if (e.target === dialog) emit('close')
        }
      "
    >
      <section class="modal" :class="{ 'cooking-modal': cooking }"><slot /></section></dialog
  ></Teleport>
</template>
