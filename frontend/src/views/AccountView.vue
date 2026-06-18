<template>
  <main class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="font-extrabold text-3xl text-ink mb-8 tracking-tight">{{ t('account.title') }}</h1>

    <div class="space-y-5">

      <!-- Profile card -->
      <div class="card p-6">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-16 h-16 bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
            <span class="text-2xl font-extrabold text-white">{{ authStore.user?.name?.[0]?.toUpperCase() }}</span>
          </div>
          <div>
            <h2 class="font-extrabold text-xl text-ink">{{ authStore.user?.name }}</h2>
            <p class="text-ink-muted text-sm">{{ authStore.user?.email }}</p>
          </div>
        </div>

        <div v-if="!editing">
          <div class="grid grid-cols-2 gap-4 text-sm mb-5">
            <div class="p-3 bg-surface-soft rounded-xl">
              <p class="text-xs text-ink-muted mb-1 uppercase tracking-wider font-semibold">{{ t('account.phone') }}</p>
              <p class="font-semibold text-ink">{{ authStore.user?.phone || t('account.notSet') }}</p>
            </div>
            <div class="p-3 bg-surface-soft rounded-xl">
              <p class="text-xs text-ink-muted mb-1 uppercase tracking-wider font-semibold">{{ t('account.memberSince') }}</p>
              <p class="font-semibold text-ink">{{ fmtDate(authStore.user?.created_at) }}</p>
            </div>
          </div>
          <button @click="startEdit" class="btn-secondary text-sm">
            <PencilIcon class="w-4 h-4" /> {{ t('account.editProfile') }}
          </button>
        </div>

        <div v-else class="space-y-4">
          <div>
            <label class="label">{{ t('auth.fullName') }}</label>
            <input v-model="editForm.name" class="input" />
          </div>
          <div>
            <label class="label">{{ t('account.phone') }}</label>
            <input v-model="editForm.phone" class="input" />
          </div>
          <div class="flex gap-3">
            <button @click="saveProfile" :disabled="saving" class="btn-primary text-sm">
              {{ saving ? t('account.saving') : t('account.save') }}
            </button>
            <button @click="editing = false" class="btn-secondary text-sm">{{ t('account.cancel') }}</button>
          </div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="grid grid-cols-2 gap-4">
        <RouterLink to="/account/orders"
          class="card p-5 hover:border-brand-200 border border-border transition-all group">
          <div class="text-3xl mb-3">📦</div>
          <h3 class="font-bold text-ink text-sm group-hover:text-brand-600 transition-colors">{{ t('account.myOrders') }}</h3>
          <p class="text-xs text-ink-muted mt-0.5">{{ t('account.viewHistory') }}</p>
        </RouterLink>
        <RouterLink to="/track-order"
          class="card p-5 hover:border-brand-200 border border-border transition-all group">
          <div class="text-3xl mb-3">🚚</div>
          <h3 class="font-bold text-ink text-sm group-hover:text-brand-600 transition-colors">{{ t('account.trackOrder') }}</h3>
          <p class="text-xs text-ink-muted mt-0.5">{{ t('account.checkStatus') }}</p>
        </RouterLink>
      </div>

      <!-- Sign out -->
      <button @click="handleLogout"
        class="w-full card p-4 text-red-500 hover:bg-red-50 hover:border-red-100 border border-border transition-all text-sm font-semibold flex items-center justify-center gap-2">
        <ArrowRightOnRectangleIcon class="w-4 h-4" /> {{ t('account.signOut') }}
      </button>
    </div>
  </main>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { authAPI } from '@/services/api'
import { useI18n } from '@/i18n/index.js'
import { useToast } from 'vue-toastification'
import { PencilIcon, ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'

const { t }     = useI18n()
const authStore = useAuthStore()
const router    = useRouter()
const toast     = useToast()
const editing   = ref(false)
const saving    = ref(false)
const editForm  = ref({ name: '', phone: '' })

const fmtDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) : ''

function startEdit() {
  editForm.value = { name: authStore.user?.name || '', phone: authStore.user?.phone || '' }
  editing.value  = true
}

async function saveProfile() {
  saving.value = true
  try {
    await authAPI.updateProfile(editForm.value)
    await authStore.fetchUser()
    editing.value = false
    toast.success(t('account.profileUpdated'))
  } catch { toast.error(t('account.failed')) }
  saving.value = false
}

async function handleLogout() {
  await authStore.logout()
  router.push('/')
}
</script>
