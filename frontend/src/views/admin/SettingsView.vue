<template>
  <div class="max-w-3xl space-y-6">

    <div v-if="loading" class="flex items-center justify-center py-24 text-gray-500">
      <div class="w-6 h-6 border-2 border-gray-700 border-t-brand-500 rounded-full animate-spin mr-2" />
      Loading settings...
    </div>

    <template v-else>

      <!-- Store Info -->
      <section class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h3 class="text-white font-semibold mb-5 flex items-center gap-2">
          <BuildingStorefrontIcon class="w-5 h-5 text-brand-400" /> Store Information
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="form-label">Store Name</label>
            <input v-model="form.store_name" class="admin-input w-full" />
          </div>
          <div>
            <label class="form-label">Contact Email</label>
            <input v-model="form.store_email" type="email" class="admin-input w-full" />
          </div>
          <div>
            <label class="form-label">Phone Number</label>
            <input v-model="form.store_phone" class="admin-input w-full" />
          </div>
          <div class="sm:col-span-2">
            <label class="form-label">Store Address</label>
            <input v-model="form.store_address" class="admin-input w-full" />
          </div>
        </div>
      </section>

      <!-- Pricing & Delivery -->
      <section class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h3 class="text-white font-semibold mb-5 flex items-center gap-2">
          <TruckIcon class="w-5 h-5 text-blue-400" /> Pricing & Delivery
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Tax Rate (%)</label>
            <input v-model="form.tax_rate" type="number" step="0.1" min="0" max="100" class="admin-input w-full" />
            <p class="text-gray-600 text-xs mt-1">Applied to all orders</p>
          </div>
          <div>
            <label class="form-label">Base Delivery Fee ($)</label>
            <input v-model="form.base_delivery_fee" type="number" step="0.01" min="0" class="admin-input w-full" />
          </div>
          <div>
            <label class="form-label">Free Delivery Threshold ($)</label>
            <input v-model="form.free_delivery_threshold" type="number" step="0.01" min="0" class="admin-input w-full" />
            <p class="text-gray-600 text-xs mt-1">Orders above this = free delivery</p>
          </div>
          <div>
            <label class="form-label">Low Stock Alert (units)</label>
            <input v-model="form.low_stock_threshold" type="number" min="1" class="admin-input w-full" />
            <p class="text-gray-600 text-xs mt-1">Alert when stock falls below this</p>
          </div>
          <div>
            <label class="form-label">Currency</label>
            <select v-model="form.currency" class="admin-select w-full">
              <option value="USD">USD ($)</option>
              <option value="EUR">EUR (€)</option>
              <option value="GBP">GBP (£)</option>
              <option value="BDT">BDT (৳)</option>
              <option value="CAD">CAD (CA$)</option>
              <option value="AUD">AUD (A$)</option>
            </select>
          </div>
        </div>
      </section>

      <!-- Payment Methods -->
      <section class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h3 class="text-white font-semibold mb-5 flex items-center gap-2">
          <CreditCardIcon class="w-5 h-5 text-purple-400" /> Payment Methods
        </h3>
        <div class="space-y-3">
          <div v-for="pm in paymentMethods" :key="pm.key"
            class="flex items-center justify-between p-4 bg-gray-800 rounded-xl">
            <div class="flex items-center gap-3">
              <span :class="pm.labelClass">{{ pm.label }}</span>
              <div>
                <p class="text-gray-200 text-sm font-medium">{{ pm.name }}</p>
                <p class="text-gray-500 text-xs">{{ pm.desc }}</p>
              </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="form[pm.key]" class="sr-only peer"
                :true-value="1" :false-value="0" />
              <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-brand-600
                after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                after:bg-white after:rounded-full after:h-5 after:w-5
                after:transition-all peer-checked:after:translate-x-5" />
            </label>
          </div>
        </div>
      </section>

      <!-- SEO -->
      <section class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h3 class="text-white font-semibold mb-5 flex items-center gap-2">
          <GlobeAltIcon class="w-5 h-5 text-orange-400" /> SEO & Meta
        </h3>
        <div class="space-y-4">
          <div>
            <label class="form-label">Meta Title</label>
            <input v-model="form.meta_title" class="admin-input w-full" placeholder="FreshBasket - Farm to Door" />
          </div>
          <div>
            <label class="form-label">Meta Description</label>
            <textarea v-model="form.meta_description" rows="3"
              class="admin-input w-full resize-none"
              placeholder="Premium grocery delivery from local farms..." />
          </div>
        </div>
      </section>

      <!-- Maintenance Mode -->
      <section class="bg-gray-900 border border-red-900/40 rounded-2xl p-6">
        <h3 class="text-white font-semibold mb-3 flex items-center gap-2">
          <ExclamationTriangleIcon class="w-5 h-5 text-red-400" /> Maintenance Mode
        </h3>
        <p class="text-gray-500 text-sm mb-4">
          When enabled, visitors see a maintenance page. The admin panel stays accessible.
        </p>
        <div class="flex items-center justify-between p-4 bg-gray-800 rounded-xl">
          <div>
            <p class="text-gray-200 font-medium">Maintenance Mode</p>
            <p class="text-xs mt-0.5" :class="form.maintenance_mode ? 'text-red-400' : 'text-brand-400'">
              {{ form.maintenance_mode ? '🔴 Store is OFFLINE' : '🟢 Store is ONLINE' }}
            </p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" v-model="form.maintenance_mode" class="sr-only peer"
              :true-value="1" :false-value="0" />
            <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-red-600
              after:content-[''] after:absolute after:top-0.5 after:left-[2px]
              after:bg-white after:rounded-full after:h-5 after:w-5
              after:transition-all peer-checked:after:translate-x-5" />
          </label>
        </div>
      </section>

      <!-- Admin Account -->
      <section class="bg-gray-900 border border-gray-800 rounded-2xl p-6">
        <h3 class="text-white font-semibold mb-5 flex items-center gap-2">
          <ShieldCheckIcon class="w-5 h-5 text-yellow-400" /> Admin Account
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Current Password</label>
            <input v-model="pwForm.current_password" type="password" class="admin-input w-full" placeholder="••••••••" />
          </div>
          <div />
          <div>
            <label class="form-label">New Password</label>
            <input v-model="pwForm.new_password" type="password" class="admin-input w-full" placeholder="••••••••" />
          </div>
          <div>
            <label class="form-label">Confirm New Password</label>
            <input v-model="pwForm.new_password_confirmation" type="password" class="admin-input w-full" placeholder="••••••••" />
          </div>
        </div>
        <button @click="changePassword" :disabled="pwSaving"
          class="mt-4 px-4 py-2 text-sm bg-gray-700 text-gray-200 rounded-xl hover:bg-gray-600 disabled:opacity-50 transition-colors">
          {{ pwSaving ? 'Updating...' : 'Update Password' }}
        </button>
      </section>

      <!-- Save -->
      <div class="flex items-center justify-between pb-4">
        <transition name="fade">
          <p v-if="saved" class="text-brand-400 text-sm flex items-center gap-1.5">
            <CheckCircleIcon class="w-4 h-4" /> Settings saved successfully
          </p>
        </transition>
        <div v-if="!saved" />
        <button @click="save" :disabled="saving"
          class="ml-auto px-6 py-2.5 bg-brand-600 text-white font-medium text-sm rounded-xl hover:bg-brand-700 disabled:opacity-50 transition-colors flex items-center gap-2">
          <span v-if="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          {{ saving ? 'Saving...' : 'Save All Settings' }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminSettingsAPI } from '@/services/adminApi'
import { authAPI } from '@/services/api'
import { useToast } from 'vue-toastification'
import {
  BuildingStorefrontIcon, TruckIcon, CreditCardIcon, GlobeAltIcon,
  ExclamationTriangleIcon, CheckCircleIcon, ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

const toast   = useToast()
const loading = ref(true)
const saving  = ref(false)
const saved   = ref(false)
const pwSaving = ref(false)

const form = ref({
  store_name: '', store_email: '', store_phone: '', store_address: '',
  currency: 'USD', tax_rate: 8,
  free_delivery_threshold: 50, base_delivery_fee: 4.99,
  low_stock_threshold: 10,
  maintenance_mode: 0, stripe_enabled: 1, paypal_enabled: 1, cod_enabled: 1,
  meta_title: '', meta_description: '',
})

const pwForm = ref({ current_password: '', new_password: '', new_password_confirmation: '' })

const paymentMethods = [
  { key: 'stripe_enabled',  label: 'STRIPE',  labelClass: 'text-blue-400 font-bold text-xs w-12',   name: 'Credit & Debit Cards',  desc: 'Visa, Mastercard, Amex via Stripe Elements' },
  { key: 'paypal_enabled',  label: 'PayPal',  labelClass: 'text-yellow-400 font-bold italic text-xs w-12', name: 'PayPal Checkout', desc: 'Redirect flow to PayPal sandbox/live' },
  { key: 'cod_enabled',     label: 'COD',     labelClass: 'text-brand-400 font-bold text-xs w-12',    name: 'Cash on Delivery',      desc: 'Customer pays in cash on arrival' },
]

async function load() {
  loading.value = true
  try {
    const { data } = await adminSettingsAPI.get()
    Object.assign(form.value, data)
  } catch { toast.error('Failed to load settings') }
  loading.value = false
}

async function save() {
  saving.value = true; saved.value = false
  try {
    await adminSettingsAPI.update(form.value)
    saved.value = true
    toast.success('Settings saved!')
    setTimeout(() => { saved.value = false }, 3000)
  } catch { toast.error('Failed to save settings') }
  saving.value = false
}

async function changePassword() {
  if (!pwForm.value.current_password || !pwForm.value.new_password) {
    toast.error('Fill in all password fields'); return
  }
  if (pwForm.value.new_password !== pwForm.value.new_password_confirmation) {
    toast.error('New passwords do not match'); return
  }
  pwSaving.value = true
  try {
    await authAPI.updateProfile(pwForm.value)
    toast.success('Password updated!')
    pwForm.value = { current_password: '', new_password: '', new_password_confirmation: '' }
  } catch (e) { toast.error(e.response?.data?.message || 'Failed to update password') }
  pwSaving.value = false
}

onMounted(load)
</script>

<style scoped>
.admin-select { @apply bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500; }
.admin-input  { @apply bg-gray-800 border border-gray-700 text-gray-200 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-600; }
.form-label   { @apply block text-gray-400 text-xs font-medium mb-1.5 uppercase tracking-wide; }
</style>
