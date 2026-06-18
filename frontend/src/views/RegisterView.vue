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
        <h1 class="font-extrabold text-3xl text-ink mt-5 mb-1">{{ t('auth.createAccount') }}</h1>
        <p class="text-ink-secondary text-sm">{{ t('auth.registerSubtitle') }}</p>
      </div>

      <div class="card p-8">
        <div v-if="error" class="mb-5 p-3.5 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm">{{ error }}</div>

        <div class="space-y-4">
          <div>
            <label class="label">{{ t('auth.fullName') }}</label>
            <input v-model="form.name" type="text" class="input" :class="{ 'input-error': errs.name }" placeholder="John Doe" />
            <p v-if="errs.name" class="text-red-500 text-xs mt-1">{{ errs.name[0] }}</p>
          </div>
          <div>
            <label class="label">{{ t('auth.emailLabel') }}</label>
            <input v-model="form.email" type="email" class="input" :class="{ 'input-error': errs.email }" placeholder="your@email.com" />
            <p v-if="errs.email" class="text-red-500 text-xs mt-1">{{ errs.email[0] }}</p>
          </div>
          <div>
            <label class="label">{{ t('auth.phoneOptional') }}</label>
            <input v-model="form.phone" type="tel" class="input" placeholder="+1 555 000 0000" />
          </div>
          <div>
            <label class="label">{{ t('auth.passwordLabel') }}</label>
            <input v-model="form.password" type="password" class="input" :class="{ 'input-error': errs.password }" placeholder="Min. 8 characters" />
            <p v-if="errs.password" class="text-red-500 text-xs mt-1">{{ errs.password[0] }}</p>
          </div>
          <div>
            <label class="label">{{ t('auth.confirmPassword') }}</label>
            <input v-model="form.password_confirmation" type="password" class="input" placeholder="Repeat password" @keyup.enter="submit" />
          </div>
          <button @click="submit" :disabled="loading" class="btn-primary w-full py-3.5 text-sm font-bold mt-2">
            <span v-if="loading" class="flex items-center gap-2 justify-center">
              <span class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
              {{ t('auth.creating') }}
            </span>
            <span v-else>{{ t('auth.createBtn') }}</span>
          </button>
        </div>

        <p class="mt-5 text-center text-sm text-ink-secondary">
          {{ t('auth.alreadyHave') }}
          <RouterLink to="/login" class="text-brand-600 font-semibold hover:text-brand-700">{{ t('auth.signInBtn') }}</RouterLink>
        </p>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from '@/i18n/index.js'

const { t }     = useI18n()
const router    = useRouter()
const authStore = useAuthStore()
const form      = ref({ name:'', email:'', phone:'', password:'', password_confirmation:'' })
const error     = ref('')
const errs      = ref({})
const loading   = ref(false)

async function submit() {
  loading.value = true; error.value = ''; errs.value = {}
  const res = await authStore.register(form.value)
  loading.value = false
  if (res.success) router.push('/')
  else { errs.value = res.errors || {}; error.value = res.message || t('common.error') }
}
</script>
