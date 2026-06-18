<template>
  <div class="space-y-4">

    <!-- Filters -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-wrap gap-3 items-center">
      <input v-model="filters.search" @input="debouncedFetch" type="text"
        placeholder="Search order, customer, email…"
        class="bg-gray-800 border border-gray-700 text-white text-sm rounded-xl px-4 py-2 w-56 focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-500" />

      <select v-model="filters.status" @change="fetchOrders" class="admin-select">
        <option value="">All Statuses</option>
        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>

      <select v-model="filters.payment_method" @change="fetchOrders" class="admin-select">
        <option value="">All Payments</option>
        <option value="stripe">Stripe</option>
        <option value="paypal">PayPal</option>
        <option value="cod">Cash on Delivery</option>
      </select>

      <input v-model="filters.date_from" @change="fetchOrders" type="date" class="admin-select" />
      <input v-model="filters.date_to"   @change="fetchOrders" type="date" class="admin-select" />

      <button @click="resetFilters" class="px-3 py-2 text-xs text-gray-400 hover:text-white bg-gray-800 rounded-xl transition-colors">
        Reset
      </button>

      <div class="ml-auto flex gap-2">
        <!-- Bulk action -->
        <div v-if="selected.length" class="flex items-center gap-2">
          <span class="text-gray-400 text-sm">{{ selected.length }} selected</span>
          <select v-model="bulkAction" class="admin-select text-xs">
            <option value="">Bulk Action</option>
            <option v-for="s in statuses" :key="s.value" :value="s.value">→ {{ s.label }}</option>
          </select>
          <button @click="applyBulk" :disabled="!bulkAction" class="px-3 py-2 text-xs bg-brand-600 text-white rounded-xl hover:bg-brand-700 disabled:opacity-40">Apply</button>
        </div>

        <a :href="exportUrl" class="px-3 py-2 text-xs bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 transition-colors flex items-center gap-1.5">
          <ArrowDownTrayIcon class="w-3.5 h-3.5" /> Export CSV
        </a>
      </div>
    </div>

    <!-- Table -->
    <AdminTable
      :columns="columns" :rows="orders" :loading="loading"
      selectable :selected-ids="selected" :all-selected="allSelected"
      :current-page="page" :total-pages="lastPage"
      @select-all="toggleAll" @select-row="toggleRow" @page="p => { page = p; fetchOrders() }"
    >
      <template #toolbar>
        <p class="text-gray-400 text-sm">{{ total }} orders found</p>
      </template>

      <template #default="{ row }">
        <td class="px-4 py-3">
          <RouterLink :to="'/admin/orders/' + row.id" class="text-brand-400 hover:text-brand-300 font-mono text-xs font-medium">
            {{ row.order_number }}
          </RouterLink>
        </td>
        <td class="px-4 py-3">
          <p class="text-gray-200 text-sm">{{ row.user?.name || row.guest_name || '—' }}</p>
          <p class="text-gray-500 text-xs">{{ row.user?.email || row.guest_email }}</p>
        </td>
        <td class="px-4 py-3">
          <AdminBadge :color="statusColor(row.status)">{{ row.status.replace('_', ' ') }}</AdminBadge>
        </td>
        <td class="px-4 py-3">
          <AdminBadge :color="payColor(row.payment_status)">{{ row.payment_status }}</AdminBadge>
        </td>
        <td class="px-4 py-3 text-gray-300 text-xs capitalize">{{ row.payment_method }}</td>
        <td class="px-4 py-3 text-white font-semibold">${{ row.total }}</td>
        <td class="px-4 py-3 text-gray-500 text-xs">{{ fmtDate(row.created_at) }}</td>
        <td class="px-4 py-3">
          <div class="flex items-center gap-1">
            <RouterLink :to="'/admin/orders/' + row.id"
              class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
              <EyeIcon class="w-4 h-4" />
            </RouterLink>
            <button @click="openStatusModal(row)"
              class="p-1.5 text-gray-500 hover:text-brand-400 hover:bg-gray-700 rounded-lg transition-colors">
              <PencilIcon class="w-4 h-4" />
            </button>
            <button @click="deleteOrder(row)"
              class="p-1.5 text-gray-500 hover:text-red-400 hover:bg-gray-700 rounded-lg transition-colors">
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </td>
      </template>

      <template #empty>No orders match your filters</template>
    </AdminTable>

    <!-- Status Update Modal -->
    <AdminModal v-model="showStatusModal" title="Update Order Status" size="sm">
      <div class="space-y-4">
        <div>
          <p class="text-gray-400 text-sm mb-1">Order: <span class="text-white font-mono">{{ editingOrder?.order_number }}</span></p>
          <label class="block text-gray-400 text-sm mb-2">New Status</label>
          <select v-model="newStatus" class="admin-select w-full">
            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-gray-400 text-sm mb-2">Payment Status</label>
          <select v-model="newPaymentStatus" class="admin-select w-full">
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="failed">Failed</option>
            <option value="refunded">Refunded</option>
          </select>
        </div>
      </div>
      <template #footer>
        <div class="flex gap-3 justify-end">
          <button @click="showStatusModal = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white bg-gray-800 rounded-xl">Cancel</button>
          <button @click="saveStatus" :disabled="saving" class="px-4 py-2 text-sm bg-brand-600 text-white rounded-xl hover:bg-brand-700 disabled:opacity-50">
            {{ saving ? 'Saving…' : 'Save Changes' }}
          </button>
        </div>
      </template>
    </AdminModal>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { adminOrderAPI } from '@/services/adminApi'
import { useToast } from 'vue-toastification'
import AdminTable  from '@/components/admin/ui/AdminTable.vue'
import AdminBadge  from '@/components/admin/ui/AdminBadge.vue'
import AdminModal  from '@/components/admin/ui/AdminModal.vue'
import { EyeIcon, PencilIcon, TrashIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

const toast  = useToast()
const route  = useRoute()
const orders = ref([])
const loading = ref(true)
const page   = ref(1)
const lastPage = ref(1)
const total  = ref(0)
const selected  = ref([])
const bulkAction = ref('')
const saving     = ref(false)
const showStatusModal = ref(false)
const editingOrder    = ref(null)
const newStatus       = ref('')
const newPaymentStatus = ref('')

const filters = ref({ search: '', status: route.query.status || '', payment_method: '', date_from: '', date_to: '' })

const statuses = [
  { value: 'pending',          label: 'Pending' },
  { value: 'confirmed',        label: 'Confirmed' },
  { value: 'processing',       label: 'Processing' },
  { value: 'out_for_delivery', label: 'Out for Delivery' },
  { value: 'delivered',        label: 'Delivered' },
  { value: 'cancelled',        label: 'Cancelled' },
]

const columns = [
  { key: 'order_number', label: 'Order #' },
  { key: 'customer',     label: 'Customer' },
  { key: 'status',       label: 'Status' },
  { key: 'payment',      label: 'Payment' },
  { key: 'method',       label: 'Method' },
  { key: 'total',        label: 'Total' },
  { key: 'date',         label: 'Date' },
  { key: 'actions',      label: '' },
]

const allSelected = computed(() => orders.value.length > 0 && orders.value.every(o => selected.value.includes(o.id)))
const exportUrl   = computed(() => adminOrderAPI.exportUrl({ ...filters.value }))

const statusColor = (s) => ({ pending: 'yellow', confirmed: 'blue', processing: 'purple', out_for_delivery: 'orange', delivered: 'green', cancelled: 'red' }[s] ?? 'gray')
const payColor    = (s) => ({ paid: 'green', pending: 'yellow', failed: 'red', refunded: 'purple' }[s] ?? 'gray')
const fmtDate     = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

async function fetchOrders() {
  loading.value = true
  const { data } = await adminOrderAPI.list({ ...filters.value, page: page.value, per_page: 20 })
  orders.value  = data.data
  lastPage.value = data.last_page
  total.value   = data.total
  selected.value = []
  loading.value = false
}

let debounceTimer = null
function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => { page.value = 1; fetchOrders() }, 400)
}

function resetFilters() {
  filters.value = { search: '', status: '', payment_method: '', date_from: '', date_to: '' }
  page.value = 1
  fetchOrders()
}

function toggleAll(checked) { selected.value = checked ? orders.value.map(o => o.id) : [] }
function toggleRow(id, checked) {
  if (checked) selected.value.push(id)
  else selected.value = selected.value.filter(i => i !== id)
}

async function applyBulk() {
  if (!bulkAction.value || !selected.value.length) return
  await adminOrderAPI.bulkStatus(selected.value, bulkAction.value)
  toast.success('Bulk update applied')
  bulkAction.value = ''
  fetchOrders()
}

function openStatusModal(order) {
  editingOrder.value    = order
  newStatus.value       = order.status
  newPaymentStatus.value = order.payment_status
  showStatusModal.value = true
}

async function saveStatus() {
  saving.value = true
  await adminOrderAPI.updateStatus(editingOrder.value.id, newStatus.value)
  await adminOrderAPI.updatePayment(editingOrder.value.id, newPaymentStatus.value)
  toast.success('Order updated')
  showStatusModal.value = false
  saving.value = false
  fetchOrders()
}

async function deleteOrder(order) {
  if (!confirm(`Delete order ${order.order_number}?`)) return
  await adminOrderAPI.destroy(order.id)
  toast.success('Order deleted')
  fetchOrders()
}

onMounted(fetchOrders)
</script>

<style scoped>
.admin-select {
  @apply bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500;
}
</style>
