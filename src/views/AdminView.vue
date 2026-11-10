<script setup lang="ts">
import { ref } from 'vue'
import {
  Trash2,
  ShieldCheck,
  Users,
  ChefHat,
  MessageSquare,
  Star,
  Pencil,
  Ban,
  Check,
} from '@lucide/vue'
import AppDialog from '../components/AppDialog.vue'
import { useApp } from '../stores/app'
import { friendlyError } from '../lib/utils'
const app = useApp(),
  tab = ref('recipes'),
  busy = ref(false),
  error = ref(''),
  confirmation = ref<{ title: string; action: () => Promise<unknown> } | null>(null)
async function action(fn: () => Promise<unknown>) {
  busy.value = true
  error.value = ''
  try {
    await fn()
    confirmation.value = null
  } catch (e) {
    error.value = friendlyError(e)
  } finally {
    busy.value = false
  }
}
const tabs = [
  { id: 'recipes', label: 'Recetas', icon: ChefHat },
  { id: 'users', label: 'Usuarios', icon: Users },
  { id: 'comments', label: 'Comentarios', icon: MessageSquare },
  { id: 'ratings', label: 'Valoraciones', icon: Star },
]
</script>
<template>
  <section class="container page">
    <div v-if="!app.isAdmin" class="empty-state">
      <ShieldCheck :size="40" />
      <h1>Este espacio es de administración</h1>
      <p>Necesitás una cuenta con permisos de administrador.</p>
      <RouterLink to="/cuenta" class="button">Ir a mi cuenta</RouterLink>
    </div>
    <template v-else
      ><div class="eyebrow">CUIDEMOS LA COMUNIDAD</div>
      <h1>Todo en su lugar.</h1>
      <p class="intro">
        Recetas, personas y conversaciones. Una comunidad que se cuida entre todos.
      </p>
      <div class="admin-stats">
        <div>
          <ChefHat /><strong>{{ app.recipes.length }}</strong
          ><span>recetas</span>
        </div>
        <div>
          <Users /><strong>{{ app.profiles.length }}</strong
          ><span>personas</span>
        </div>
        <div>
          <MessageSquare /><strong>{{ app.comments.length }}</strong
          ><span>comentarios</span>
        </div>
        <div>
          <Star /><strong>{{ app.ratings.length }}</strong
          ><span>valoraciones</span>
        </div>
      </div>
      <div class="admin-tabs">
        <button
          v-for="item in tabs"
          :key="item.id"
          :class="{ active: tab === item.id }"
          @click="tab = item.id"
        >
          <component :is="item.icon" :size="17" />{{ item.label }}
        </button>
      </div>
      <div v-if="error" class="notice error" role="alert">{{ error }}</div>
      <div class="table-wrap">
        <table v-if="tab === 'recipes'">
          <caption class="sr-only">
            Administrar recetas
          </caption>
          <thead>
            <tr>
              <th>Receta</th>
              <th>Autor</th>
              <th>Temporada</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in app.recipes" :key="r.id">
              <td>
                <RouterLink :to="`/receta/${r.id}`">{{ r.title }}</RouterLink>
              </td>
              <td>{{ app.authorName(r.author_id) }}</td>
              <td>{{ r.season }}</td>
              <td class="table-actions">
                <RouterLink
                  :to="`/editar/${r.id}`"
                  class="icon-button"
                  :aria-label="`Editar ${r.title}`"
                  ><Pencil :size="16" /></RouterLink
                ><button
                  class="icon-button danger"
                  :aria-label="`Eliminar ${r.title}`"
                  @click="
                    confirmation = {
                      title: `¿Eliminar «${r.title}» y sus interacciones?`,
                      action: () => app.deleteRecipe(r.id),
                    }
                  "
                >
                  <Trash2 :size="16" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <table v-if="tab === 'users'">
          <caption class="sr-only">
            Administrar usuarios
          </caption>
          <thead>
            <tr>
              <th>Nombre público</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in app.profiles" :key="p.id">
              <td>{{ p.display_name }} <small v-if="p.id === app.profile?.id">(vos)</small></td>
              <td>{{ p.role }}</td>
              <td>{{ p.disabled ? 'Desactivado' : 'Activo' }}</td>
              <td class="table-actions">
                <template v-if="p.id !== app.profile?.id"
                  ><button
                    class="text-button"
                    :disabled="busy"
                    @click="
                      confirmation = {
                        title: `¿Cambiar el rol de ${p.display_name} a ${p.role === 'admin' ? 'usuario' : 'admin'}?`,
                        action: () =>
                          app.moderateUser(
                            p.id,
                            p.role === 'admin' ? 'usuario' : 'admin',
                            p.disabled,
                          ),
                      }
                    "
                  >
                    <ShieldCheck :size="16" /> Cambiar rol</button
                  ><button
                    class="icon-button"
                    :aria-label="
                      p.disabled ? `Activar ${p.display_name}` : `Desactivar ${p.display_name}`
                    "
                    @click="
                      confirmation = {
                        title: `¿${p.disabled ? 'Activar' : 'Desactivar'} a ${p.display_name}?`,
                        action: () => app.moderateUser(p.id, p.role, !p.disabled),
                      }
                    "
                  >
                    <Check v-if="p.disabled" :size="16" /><Ban v-else :size="16" /></button
                  ><button
                    class="icon-button danger"
                    :aria-label="`Eliminar cuenta de ${p.display_name}`"
                    @click="
                      confirmation = {
                        title: `¿Eliminar la cuenta de ${p.display_name} y todas sus recetas e interacciones? Esta acción es irreversible.`,
                        action: () => app.deleteUser(p.id),
                      }
                    "
                  >
                    <Trash2 :size="16" /></button
                ></template>
              </td>
            </tr>
          </tbody>
        </table>
        <table v-if="tab === 'comments'">
          <caption class="sr-only">
            Moderar comentarios
          </caption>
          <thead>
            <tr>
              <th>Comentario</th>
              <th>Autor</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in app.comments" :key="c.id">
              <td>{{ c.body }}</td>
              <td>{{ app.authorName(c.user_id) }}</td>
              <td>
                <button
                  class="icon-button danger"
                  aria-label="Eliminar comentario"
                  @click="
                    confirmation = {
                      title: '¿Eliminar este comentario?',
                      action: () => app.deleteComment(c.id),
                    }
                  "
                >
                  <Trash2 :size="16" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <table v-if="tab === 'ratings'">
          <caption class="sr-only">
            Moderar valoraciones
          </caption>
          <thead>
            <tr>
              <th>Receta</th>
              <th>Autor</th>
              <th>Valoración</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in app.ratings" :key="r.user_id + r.recipe_id">
              <td>{{ app.recipes.find((recipe) => recipe.id === r.recipe_id)?.title }}</td>
              <td>{{ app.authorName(r.user_id) }}</td>
              <td>{{ r.score }} / 5</td>
              <td>
                <button
                  class="icon-button danger"
                  aria-label="Eliminar valoración"
                  @click="
                    confirmation = {
                      title: '¿Eliminar esta valoración?',
                      action: () => app.deleteRating(r.recipe_id, r.user_id),
                    }
                  "
                >
                  <Trash2 :size="16" />
                </button>
              </td>
            </tr>
          </tbody>
        </table></div
    ></template>
    <AppDialog v-if="confirmation" labelled-by="confirm-title" @close="confirmation = null"
      ><h2 id="confirm-title">{{ confirmation.title }}</h2>
      <p>Revisá la acción antes de continuar.</p>
      <div class="button-row">
        <button class="button secondary" :disabled="busy" @click="confirmation = null">
          Cancelar</button
        ><button
          class="button danger-button"
          :disabled="busy"
          @click="action(confirmation!.action)"
        >
          {{ busy ? 'Procesando…' : 'Confirmar' }}
        </button>
      </div></AppDialog
    >
  </section>
</template>
