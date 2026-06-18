<template>
  <div class="min-h-screen bg-gray-950 flex items-center justify-center px-4">
    <div class="w-full max-w-sm animate-scale-in">

      <div class="text-center mb-8">
        <div class="w-14 h-14 bg-brand-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-brand-900/50">
          <span class="text-2xl">🌿</span>
        </div>
        <h1 class="font-extrabold text-2xl text-white">FreshAdmin</h1>
        <p class="text-gray-500 text-sm mt-1">Store management panel</p>
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8">
        <div v-if="error" class="mb-5 p-3.5 bg-red-900/30 border border-red-800 rounded-xl text-red-400 text-sm flex items-center gap-2">
          <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" /> {{ error }}
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-gray-400 text-xs font-semibold mb-1.5 uppercase tracking-wider">Email</label>
            <input v-model="form.email" type="email" @keyup.enter="submit"
              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all"
              placeholder="admin@freshbasket.com" />
          </div>
          <div>
            <label class="block text-gray-400 text-xs font-semibold mb-1.5 uppercase tracking-wider">Password</label>
            <div class="relative">
              <input v-model="form.password" :type="show ? 'text' : 'password'" @keyup.enter="submit"
                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-xl text-white text-sm placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all pr-11"
                placeholder="••••••••" />
              <button @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                <EyeIcon v-if="!show" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
          <button @click="submit" :disabled="loading"
            class="w-full flex items-center justify-center gap-2 py-3.5 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-700 disabled:opacity-50 transition-all active:scale-95 text-sm mt-2">
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
            <span>{{ loading ? 'Signing in...' : 'Sign In to Admin' }}</span>
          </button>
        </div>

        <div class="mt-5 pt-5 border-t border-gray-800 flex items-center justify-between">
          <RouterLink to="/" class="text-xs text-gray-500 hover:text-brand-400 transition-colors flex items-center gap-1">
            <ArrowLeftIcon class="w-3.5 h-3.5" /> Back to Store
          </RouterLink>
          <div class="text-xs text-gray-600">
            admin@freshbasket.com
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { ExclamationCircleIcon, EyeIcon, EyeSlashIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

const router    = useRouter()
const authStore = useAuthStore()
const form      = ref({ email: '', password: '' })
const error     = ref('')
const loading   = ref(false)
const show      = ref(false)

async function submit() {
  if (!form.value.email || !form.value.password) { error.value = 'Please fill in all fields'; return }
  loading.value = true; error.value = ''
  const res = await authStore.login(form.value)
  loading.value = false
  if (res.success) {
    if (authStore.user?.is_admin) router.push('/admin')
    else { await authStore.logout(); error.value = 'Access denied. Admin account required.' }
  } else {
    error.value = res.message || 'Invalid credentials'
  }
}
</script>
