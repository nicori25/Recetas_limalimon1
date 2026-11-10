<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Mail, ArrowUpRight, LogOut, Sprout, Eye, EyeOff, ShieldCheck } from '@lucide/vue'
import { useApp } from '../stores/app'
import { isDemo, supabase } from '../lib/supabase'
import { friendlyError } from '../lib/utils'
import { DIETS } from '../lib/types'
const app = useApp(),
  route = useRoute(),
  router = useRouter(),
  mode = ref<'login' | 'register' | 'reset' | 'recovery'>(
    route.query.recovery ? 'recovery' : 'login',
  ),
  email = ref(''),
  password = ref(''),
  name = ref(''),
  preference = ref(''),
  busy = ref(false),
  error = ref(''),
  message = ref(''),
  showPassword = ref(false)
const recovery = computed(() => mode.value === 'recovery')
watch(
  () => app.profile,
  (p) => {
    if (p) {
      name.value = p.display_name
      preference.value = p.dietary_preference
    }
  },
  { immediate: true },
)
watch(mode, () => {
  error.value = ''
  message.value = ''
  password.value = ''
})
watch(
  () => route.query.recovery,
  (v) => {
    if (v) mode.value = 'recovery'
  },
)
const callback = () => `${window.location.origin}${window.location.pathname}`
function enterDemo() {
  app.enterDemo()
  void router.push('/')
}
async function submit() {
  if (!supabase) return
  busy.value = true
  error.value = ''
  message.value = ''
  try {
    if (mode.value === 'register') {
      const { data, error } = await supabase.auth.signUp({
        email: email.value,
        password: password.value,
        options: { data: { display_name: name.value.trim() }, emailRedirectTo: callback() },
      })
      if (error) throw error
      message.value = data.session
        ? '¡Tu cuenta está lista!'
        : 'Revisá tu correo para confirmar la cuenta. Si ya tenías una cuenta, podés iniciar sesión o recuperar tu contraseña.'
    } else if (mode.value === 'reset') {
      const { error } = await supabase.auth.resetPasswordForEmail(email.value, {
        redirectTo: `${callback()}?flow=recovery`,
      })
      if (error) throw error
      message.value =
        'Si el correo tiene una cuenta, vas a recibir un enlace para elegir una nueva contraseña.'
    } else if (mode.value === 'recovery') {
      const { error } = await supabase.auth.updateUser({ password: password.value })
      if (error) throw error
      window.history.replaceState(null, '', callback() + '#/cuenta')
      await router.replace('/cuenta')
      mode.value = 'login'
      message.value = 'Contraseña actualizada.'
    } else {
      const { error } = await supabase.auth.signInWithPassword({
        email: email.value,
        password: password.value,
      })
      if (error) throw error
      await app.refresh()
      await router.push('/')
    }
  } catch (e) {
    error.value = friendlyError(e)
  } finally {
    busy.value = false
  }
}
async function saveProfile() {
  busy.value = true
  error.value = ''
  try {
    await app.saveProfile(name.value, preference.value)
  } catch (e) {
    error.value = friendlyError(e)
  } finally {
    busy.value = false
  }
}
async function signOut() {
  busy.value = true
  try {
    await app.signOut()
    await router.push('/')
  } catch (e) {
    error.value = friendlyError(e)
  } finally {
    busy.value = false
  }
}
</script>
<template>
  <section class="container page account-page">
    <div class="account-story">
      <div class="eyebrow">HAY UN LUGAR PARA VOS EN LA MESA</div>
      <h1>El ingrediente<br />que falta<br /><em>sos vos.</em></h1>
      <p>
        Guardá lo que te gusta, compartí lo que sabés<br />y descubrí nuevas formas de comer bien.
      </p>
      <span class="account-sprout"><Sprout :size="110" :stroke-width="1" /></span>
    </div>
    <div class="account-panel">
      <template v-if="app.profile && !recovery"
        ><span class="profile-avatar">{{ app.profile.display_name.charAt(0) }}</span>
        <h2>¡Hola, {{ app.profile.display_name }}!</h2>
        <p>Tu cocina, tus preferencias.</p>
        <form @submit.prevent="saveProfile">
          <label
            >Cómo te llamamos<input
              v-model="name"
              required
              maxlength="80"
              autocomplete="name" /></label
          ><label
            >Tu preferencia<select v-model="preference">
              <option value="">Como de todo</option>
              <option v-for="diet in DIETS" :key="diet" :value="diet">{{ diet }}</option>
              <option
                v-if="preference && !DIETS.includes(preference as (typeof DIETS)[number])"
                :value="preference"
              >
                {{ preference }}
              </option>
            </select></label
          ><button class="button full" :disabled="busy">Guardar perfil</button>
        </form>
        <RouterLink v-if="app.isAdmin" to="/admin" class="text-button"
          ><ShieldCheck :size="17" /> Administrar comunidad</RouterLink
        ><button class="text-button" :disabled="busy" @click="signOut">
          <LogOut :size="17" /> Cerrar sesión
        </button></template
      >
      <template v-else-if="isDemo"
        ><div class="eyebrow">PROBÁ LIMALIMÓN</div>
        <h2>Una muestra de<br />lo que podemos cocinar.</h2>
        <p>
          Esta es una demo local. Podés publicar recetas, guardar favoritos, comentar y probar la
          administración. Todo queda en este navegador.
        </p>
        <div class="notice">
          <strong>Sin cuentas reales</strong>
          <p>
            El acceso con correo se habilita al conectar tu proyecto de Supabase. No ingreses
            contraseñas reales en la demo.
          </p>
        </div>
        <button class="button full" @click="enterDemo">
          Entrar a la demo <ArrowUpRight :size="18" /></button
      ></template>
      <template v-else
        ><div v-if="!recovery" class="account-tabs">
          <button :class="{ active: mode === 'login' }" @click="mode = 'login'">Ingresar</button
          ><button :class="{ active: mode === 'register' }" @click="mode = 'register'">
            Crear cuenta
          </button>
        </div>
        <h2>
          {{
            mode === 'register'
              ? 'Empezá a compartir.'
              : mode === 'reset'
                ? 'Volvamos a tu cocina.'
                : recovery
                  ? 'Una nueva contraseña.'
                  : 'Qué bueno verte.'
          }}
        </h2>
        <p>
          {{
            mode === 'reset'
              ? 'Te enviamos un enlace a tu correo.'
              : 'Tu próxima receta favorita está por acá.'
          }}
        </p>
        <form @submit.prevent="submit">
          <label v-if="mode === 'register'"
            >Tu nombre<input v-model="name" required maxlength="80" autocomplete="name" /></label
          ><label v-if="!recovery"
            >Correo electrónico<input
              v-model="email"
              type="email"
              required
              autocomplete="email"
              placeholder="vos@ejemplo.com" /></label
          ><label v-if="mode !== 'reset'"
            >{{ recovery ? 'Nueva contraseña' : 'Contraseña' }}
            <div class="password-input">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                :minlength="mode === 'login' ? 1 : 8"
                :autocomplete="mode === 'login' ? 'current-password' : 'new-password'"
              /><button
                type="button"
                class="icon-button"
                :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
                @click="showPassword = !showPassword"
              >
                <EyeOff v-if="showPassword" :size="18" /><Eye v-else :size="18" />
              </button>
            </div>
            <small v-if="mode !== 'login'">Usá al menos 8 caracteres.</small></label
          ><button class="button full" :disabled="busy">
            <Mail v-if="mode === 'reset'" :size="17" />{{
              busy
                ? 'Un momento…'
                : mode === 'register'
                  ? 'Crear mi cuenta'
                  : mode === 'reset'
                    ? 'Enviar enlace'
                    : recovery
                      ? 'Guardar contraseña'
                      : 'Ingresar'
            }}
          </button>
        </form>
        <button v-if="mode === 'login'" class="text-button" @click="mode = 'reset'">
          Olvidé mi contraseña
        </button></template
      >
      <div v-if="error" class="notice error" role="alert">{{ error }}</div>
      <div v-if="message" class="notice success" role="status">{{ message }}</div>
    </div>
  </section>
</template>
