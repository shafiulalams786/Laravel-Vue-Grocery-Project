<template>
  <div class="space-y-4">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-wrap gap-3 items-center">
      <input v-model="search" @input="debouncedFetch" type="text"
        placeholder="Search name, email, phone…" class="admin-input w-64" />
      <select v-model="statusFilter" @change="fetch" class="admin-select">
        <option value="">All Customers</option>
        <option value="active">Active</option>
        <option value="banned">Banned</option>
      </select>
      <a :href="exportUrl" class="ml-auto px-3 py-2 text-xs bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700 flex items-center gap-1.5">
        <ArrowDownTrayIcon class="w-3.5 h-3.5" /> Export CSV
      </a>
    </div>

    <AdminTable :columns="columns" :rows="customers" :loading="loading"
      :current-page="page" :total-pages="lastPage"
      @page="p => { page = p; fetch() }">
      <template #default="{ row }">
        <td class="px-4 py-3">
          <div class="w-9 h-9 bg-brand-700 rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-white text-sm font-bold">{{ row.name[0].toUpperCase() }}</span>
          </div>
        </td>
        <td class="px-4 py-3">
          <p class="text-gray-200 text-sm font-medium">{{ row.name }}</p>
          <p class="text-gray-500 text-xs">{{ row.email }}</p>
        </td>
        <td class="px-4 py-3 text-gray-400 text-sm">{{ row.phone || '—' }}</td>
        <td class="px-4 py-3 text-gray-300 text-sm">{{ row.orders_count }}</td>
        <td class="px-4 py-3 text-brand-400 font-semibold text-sm">${{ Number(row.orders_sum_total ?? 0).toFixed(2) }}</td>
        <td class="px-4 py-3 text-gray-500 text-xs">{{ fmtDate(row.created_at) }}</td>
        <td class="px-4 py-3">
          <AdminBadge :color="row.is_banned ? 'red' : 'green'">{{ row.is_banned ? 'Banned' : 'Active' }}</AdminBadge>
        </td>
        <td class="px-4 py-3">
          <div class="flex gap-1">
            <button @click="viewCustomer(row)" class="p-1.5 text-gray-500 hover:text-white hover:bg-gray-700 rounded-lg">
              <EyeIcon class="w-4 h-4" />
            </button>
            <button @click="toggleBan(row)" :class="['p-1.5 hover:bg-gray-700 rounded-lg', row.is_banned ? 'text-gray-500 hover:text-brand-400' : 'text-gray-500 hover:text-red-400']">
              <NoSymbolIcon class="w-4 h-4" />
            </button>
          </div>
        </td>
      </template>
    </AdminTable>

    <!-- Customer Detail Modal -->
    <AdminModal v-model="showDetail" :title="selected?.name" size="lg">
      <div v-if="detail" class="space-y-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <div class="bg-gray-800 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-white">{{ detail.stats.total_orders }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Orders</p>
          </div>
          <div class="bg-gray-800 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-brand-400">${{ Number(detail.stats.total_spent).toFixed(0) }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Spent</p>
          </div>
          <div class="bg-gray-800 rounded-xl p-3 text-center">
            <p class="text-2xl font-bold text-white">${{ Number(detail.stats.avg_order_value).toFixed(0) }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Avg Order</p>
          </div>
          <div class="bg-gray-800 rounded-xl p-3 text-center">
            <p class="text-sm font-bold text-white">{{ fmtDate(detail.customer.created_at) }}</p>
            <p class="text-gray-500 text-xs mt-0.5">Joined</p>
          </div>
        </div>
        <div>
          <h4 class="text-gray-300 font-semibold text-sm mb-3">Recent Orders</h4>
          <div class="space-y-2">
            <div v-for="order in detail.orders" :key="order.id" class="flex items-center justify-between p-3 bg-gray-800 rounded-xl">
              <div>
                <p class="text-gray-200 text-sm font-mono">{{ order.order_number }}</p>
                <p class="text-gray-500 text-xs">{{ fmtDate(order.created_at) }}</p>
              </div>
              <AdminBadge :color="statusColor(order.status)">{{ order.status }}</AdminBadge>
              <span class="text-white font-bold">${{ order.total }}</span>
            </div>
          </div>
        </div>
      </div>
    </AdminModal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminCustomerAPI } from '@/services/adminApi'
import { useToast } from 'vue-toastification'
import AdminTable from '@/components/admin/ui/AdminTable.vue'
import AdminBadge from '@/components/admin/ui/AdminBadge.vue'
import AdminModal from '@/components/admin/ui/AdminModal.vue'
import { EyeIcon, NoSymbolIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

const toast = useToast()
const customers = ref([]), loading = ref(true)
const page = ref(1), lastPage = ref(1)
const search = ref(''), statusFilter = ref('')
const showDetail = ref(false), selected = ref(null), detail = ref(null)
const exportUrl = adminCustomerAPI.exportUrl()

const columns = [
  { key: 'av', label: '', width: '50px' },
  { key: 'name', label: 'Customer' },
  { key: 'phone', label: 'Phone' },
  { key: 'orders', label: 'Orders' },
  { key: 'spent', label: 'Total Spent' },
  { key: 'joined', label: 'Joined' },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '' },
]

const statusColor = (s) => ({ pending: 'yellow', confirmed: 'blue', processing: 'purple', out_for_delivery: 'orange', delivered: 'green', cancelled: 'red' }[s] ?? 'gray')
const fmtDate = (d) => new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })

async function fetch() {
  loading.value = true
  const { data } = await adminCustomerAPI.list({ search: search.value, status: statusFilter.value, page: page.value })
  customers.value = data.data; lastPage.value = data.last_page
  loading.value = false
}
let timer = null
function debouncedFetch() { clearTimeout(timer); timer = setTimeout(fetch, 400) }

async function viewCustomer(c) {
  selected.value = c; detail.value = null; showDetail.value = true
  const { data } = await adminCustomerAPI.show(c.id)
  detail.value = data
}

async function toggleBan(c) {
  const action = c.is_banned ? 'Unban' : 'Ban'
  if (!confirm(`${action} "${c.name}"?`)) return
  await adminCustomerAPI.toggleBan(c.id)
  c.is_banned = !c.is_banned
  toast.success(`Customer ${action.toLowerCase()}ned`)
}

onMounted(fetch)
</script>

<style scoped>
.admin-select { @apply bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500; }
.admin-input  { @apply bg-gray-800 border border-gray-700 text-gray-200 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-gray-600; }
</style>
