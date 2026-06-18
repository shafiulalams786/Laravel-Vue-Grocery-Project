<template>
  <main class="min-h-screen hero-bg flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md animate-scale-in">
      <div class="text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center gap-2.5 group">
          <div class="w-11 h-11 bg-brand-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
            <span class="text-xl">🌿</span>
          </div>
          <span class="font-extrabold text-2xl text-ink">Fresh<span class="text-brand-600">Basket</span></span>
        </RouterLink>
        <h1 class="font-extrabold text-3xl text-ink mt-5 mb-1">{{ t('auth.welcomeBack') }}</h1>
        <p class="text-ink-secondary text-sm">{{ t('auth.signInSubtitle') }}</p>
      </div>

      <div class="card p-8">
        <div v-if="error" class="mb-5 p-3.5 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm flex items-center gap-2.5">
          <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" /> {{ error }}
        </div>

        <div class="space-y-4">
          <div>
            <label class="label">{{ t('auth.emailLabel') }}</label>
            <input v-model="form.email" type="email" @keyup.enter="submit" class="input" :placeholder="`${t('auth.emailLabel').toLowerCase()}@example.com`" />
          </div>
          <div>
            <label class="label">{{ t('auth.passwordLabel') }}</label>
            <div class="relative">
              <input v-model="form.password" :type="show ? 'text' : 'password'" @keyup.enter="submit" class="input pr-11" placeholder="••••••••" />
              <button @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-muted hover:text-ink-secondary transition-colors">
                <EyeIcon v-if="!show" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
          <button @click="submit" :disabled="loading"
            class="btn-primary w-full py-3.5 text-sm font-bold mt-2">
            <span v-if="loading" class="flex items-center gap-2 justify-center">
              <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              {{ t('auth.signingIn') }}
            </span>
            <span v-else>{{ t('auth.signInBtn') }}</span>
          </button>
        </div>

        <div class="mt-6 space-y-3 text-center">
          <p class="text-sm text-ink-secondary">
            {{ t('auth.noAccount') }}
            <RouterLink to="/register" class="text-brand-600 font-semibold hover:text-brand-700">{{ t('auth.createFree') }}</RouterLink>
          </p>
          <div class="relative">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-border" /></div>
            <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-ink-muted">or</span></div>
          </div>
          <RouterLink to="/checkout" class="block text-sm text-ink-muted hover:text-brand-600 transition-colors">
            {{ t('auth.continueGuest') }}
          </RouterLink>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from '@/i18n/index.js'
import { ExclamationCircleIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'

const { t }     = useI18n()
const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()
const form      = ref({ email: '', password: '' })
const error     = ref('')
const loading   = ref(false)
const show      = ref(false)

async function submit() {
  if (!form.value.email || !form.value.password) { error.value = t('auth.fillAll'); return }
  loading.value = true; error.value = ''
  const res = await authStore.login(form.value)
  loading.value = false
  if (res.success) router.push(route.query.redirect || '/')
  else error.value = res.message || t('auth.invalidCredentials')
}
</script>
