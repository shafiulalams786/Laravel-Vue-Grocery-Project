<template>
  <div v-if="loading" class="flex items-center justify-center py-24 text-gray-500">
    <div class="w-6 h-6 border-2 border-gray-700 border-t-brand-500 rounded-full animate-spin mr-2" /> Loading…
  </div>

  <div v-else-if="order" class="space-y-5 max-w-4xl">

    <!-- Header -->
    <div class="flex items-center gap-4 flex-wrap">
      <button @click="$router.back()" class="text-gray-500 hover:text-white transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <div class="flex-1">
        <div class="flex items-center gap-3 flex-wrap">
          <h2 class="text-white font-bold text-xl font-mono">{{ order.order_number }}</h2>
          <AdminBadge :color="statusColor(order.status)">{{ order.status.replace('_', ' ') }}</AdminBadge>
          <AdminBadge :color="payColor(order.payment_status)">{{ order.payment_status }}</AdminBadge>
        </div>
        <p class="text-gray-500 text-sm mt-0.5">Placed {{ fmtDate(order.created_at) }}</p>
      </div>

      <!-- Status updater -->
      <div class="flex gap-2 items-center">
        <select v-model="statusUpdate" class="admin-select text-sm">
          <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
        <button @click="doUpdateStatus" :disabled="saving" class="px-4 py-2 text-sm bg-brand-600 text-white rounded-xl hover:bg-brand-700 disabled:opacity-50">
          {{ saving ? '…' : 'Update' }}
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

      <!-- Order items -->
      <div class="md:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h3 class="text-white font-semibold mb-4">Order Items</h3>
        <div class="space-y-3">
          <div v-for="item in order.items" :key="item.id" class="flex items-center gap-4 p-3 bg-gray-800/50 rounded-xl">
            <div class="w-10 h-10 bg-gray-700 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
              🛒
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-gray-200 text-sm font-medium">{{ item.product_name }}</p>
              <p class="text-gray-500 text-xs">×{{ item.quantity }} @ ${{ item.price }}</p>
            </div>
            <span class="text-white font-bold">${{ item.total }}</span>
          </div>
        </div>

        <!-- Totals -->
        <div class="mt-5 pt-4 border-t border-gray-800 space-y-2 text-sm">
          <div class="flex justify-between text-gray-400"><span>Subtotal</span><span>${{ order.subtotal }}</span></div>
          <div class="flex justify-between text-gray-400"><span>Delivery</span><span>{{ order.delivery_fee == 0 ? 'FREE' : '$' + order.delivery_fee }}</span></div>
          <div class="flex justify-between text-gray-400"><span>Tax</span><span>${{ order.tax }}</span></div>
          <div class="flex justify-between text-white font-bold text-base pt-2 border-t border-gray-800">
            <span>Total</span><span>${{ order.total }}</span>
          </div>
        </div>
      </div>

      <!-- Sidebar info -->
      <div class="space-y-4">

        <!-- Customer -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
          <h3 class="text-white font-semibold mb-3 text-sm">Customer</h3>
          <div class="space-y-1.5 text-sm">
            <p class="text-gray-200 font-medium">{{ order.user?.name || order.guest_name }}</p>
            <p class="text-gray-500">{{ order.user?.email || order.guest_email }}</p>
            <p class="text-gray-500">{{ order.user?.phone || order.guest_phone }}</p>
            <span class="inline-block mt-1">
              <AdminBadge :color="order.user ? 'green' : 'gray'">{{ order.user ? 'Registered' : 'Guest' }}</AdminBadge>
            </span>
          </div>
        </div>

        <!-- Address -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
          <h3 class="text-white font-semibold mb-3 text-sm">Delivery Address</h3>
          <div class="text-gray-400 text-sm space-y-0.5">
            <p>{{ order.delivery_address?.street }}</p>
            <p>{{ order.delivery_address?.city }}, {{ order.delivery_address?.state }}</p>
            <p>{{ order.delivery_address?.zip }}</p>
          </div>
        </div>

        <!-- Payment -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
          <h3 class="text-white font-semibold mb-3 text-sm">Payment</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Method</span>
              <span class="text-gray-200 capitalize">{{ order.payment_method }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Status</span>
              <AdminBadge :color="payColor(order.payment_status)">{{ order.payment_status }}</AdminBadge>
            </div>
            <div v-if="order.payment_id" class="flex justify-between">
              <span class="text-gray-500">ID</span>
              <span class="text-gray-400 text-xs font-mono truncate max-w-24">{{ order.payment_id }}</span>
            </div>
          </div>

          <!-- Mark as paid button for COD -->
          <button v-if="order.payment_method === 'cod' && order.payment_status === 'pending'"
            @click="markPaid"
            class="mt-3 w-full px-3 py-2 text-xs bg-brand-600 text-white rounded-xl hover:bg-brand-700">
            ✓ Mark as Paid
          </button>
        </div>

        <!-- Notes -->
        <div v-if="order.notes" class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
          <h3 class="text-white font-semibold mb-2 text-sm">Notes</h3>
          <p class="text-gray-400 text-sm">{{ order.notes }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { adminOrderAPI } from '@/services/adminApi'
import { useToast } from 'vue-toastification'
import AdminBadge from '@/components/admin/ui/AdminBadge.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const route   = useRoute()
const toast   = useToast()
const order   = ref(null)
const loading = ref(true)
const saving  = ref(false)
const statusUpdate = ref('')

const statuses = [
  { value: 'pending', label: 'Pending' }, { value: 'confirmed', label: 'Confirmed' },
  { value: 'processing', label: 'Processing' }, { value: 'out_for_delivery', label: 'Out for Delivery' },
  { value: 'delivered', label: 'Delivered' }, { value: 'cancelled', label: 'Cancelled' },
]

const statusColor = (s) => ({ pending: 'yellow', confirmed: 'blue', processing: 'purple', out_for_delivery: 'orange', delivered: 'green', cancelled: 'red' }[s] ?? 'gray')
const payColor    = (s) => ({ paid: 'green', pending: 'yellow', failed: 'red', refunded: 'purple' }[s] ?? 'gray')
const fmtDate     = (d) => new Date(d).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })

async function doUpdateStatus() {
  saving.value = true
  await adminOrderAPI.updateStatus(order.value.id, statusUpdate.value)
  order.value.status = statusUpdate.value
  toast.success('Status updated')
  saving.value = false
}

async function markPaid() {
  await adminOrderAPI.updatePayment(order.value.id, 'paid')
  order.value.payment_status = 'paid'
  toast.success('Marked as paid')
}

onMounted(async () => {
  const { data } = await adminOrderAPI.show(route.params.id)
  order.value = data
  statusUpdate.value = data.status
  loading.value = false
})
</script>

<style scoped>
.admin-select { @apply bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500; }
</style>
