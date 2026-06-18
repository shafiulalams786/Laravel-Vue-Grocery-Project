<template>
  <div class="space-y-6">

    <!-- Date Range Picker -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-wrap gap-3 items-center">
      <div class="flex items-center gap-2">
        <label class="text-gray-400 text-sm">From</label>
        <input v-model="from" type="date" class="admin-input" />
      </div>
      <div class="flex items-center gap-2">
        <label class="text-gray-400 text-sm">To</label>
        <input v-model="to" type="date" class="admin-input" />
      </div>
      <button @click="fetchAll" :disabled="loading" class="px-4 py-2 bg-brand-600 text-white text-sm rounded-xl hover:bg-brand-700 disabled:opacity-50">
        {{ loading ? 'Loading...' : 'Generate Report' }}
      </button>
      <div class="flex gap-2 ml-2">
        <button v-for="r in quickRanges" :key="r.label" @click="setRange(r)"
          class="px-3 py-1.5 text-xs bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition-colors">
          {{ r.label }}
        </button>
      </div>
      <a v-if="from && to" :href="exportUrl" class="ml-auto flex items-center gap-1.5 px-3 py-2 text-xs bg-gray-800 text-gray-300 rounded-xl hover:bg-gray-700">
        <ArrowDownTrayIcon class="w-3.5 h-3.5" /> Export CSV
      </a>
    </div>

    <!-- Summary Cards -->
    <div v-if="summary" class="grid grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
        <p class="text-2xl font-bold text-white">${{ Number(summary.total_revenue).toFixed(2) }}</p>
        <p class="text-gray-500 text-xs mt-1">Total Revenue</p>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
        <p class="text-2xl font-bold text-white">{{ summary.total_orders }}</p>
        <p class="text-gray-500 text-xs mt-1">Total Orders</p>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
        <p class="text-2xl font-bold text-brand-400">${{ Number(summary.avg_order_value).toFixed(2) }}</p>
        <p class="text-gray-500 text-xs mt-1">Avg Order Value</p>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
        <p class="text-2xl font-bold text-blue-400">${{ Number(summary.total_tax).toFixed(2) }}</p>
        <p class="text-gray-500 text-xs mt-1">Total Tax</p>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
        <p class="text-2xl font-bold text-orange-400">${{ Number(summary.total_delivery).toFixed(2) }}</p>
        <p class="text-gray-500 text-xs mt-1">Delivery Fees</p>
      </div>
    </div>

    <!-- Daily Breakdown -->
    <div v-if="salesRows.length" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-white font-semibold">Daily Breakdown</h3>
        <p class="text-gray-500 text-xs">{{ salesRows.length }} days</p>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-800">
              <th class="px-5 py-3 text-left text-xs text-gray-400 uppercase tracking-wider">Date</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Orders</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Subtotal</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Delivery</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Tax</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Revenue</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800">
            <tr v-for="row in salesRows" :key="row.date" class="hover:bg-gray-800/40">
              <td class="px-5 py-3 text-gray-300">{{ fmtDate(row.date) }}</td>
              <td class="px-5 py-3 text-right text-gray-400">{{ row.orders }}</td>
              <td class="px-5 py-3 text-right text-gray-400">${{ Number(row.subtotal).toFixed(2) }}</td>
              <td class="px-5 py-3 text-right text-gray-400">${{ Number(row.delivery_fees).toFixed(2) }}</td>
              <td class="px-5 py-3 text-right text-gray-400">${{ Number(row.tax).toFixed(2) }}</td>
              <td class="px-5 py-3 text-right text-white font-semibold">${{ Number(row.revenue).toFixed(2) }}</td>
            </tr>
          </tbody>
          <tfoot class="border-t-2 border-gray-700">
            <tr class="bg-gray-800/50">
              <td class="px-5 py-3 text-gray-300 font-semibold">Total</td>
              <td class="px-5 py-3 text-right text-gray-300 font-semibold">{{ summary?.total_orders }}</td>
              <td colspan="2" class="px-5 py-3"></td>
              <td class="px-5 py-3 text-right text-gray-300 font-semibold">${{ Number(summary?.total_tax).toFixed(2) }}</td>
              <td class="px-5 py-3 text-right text-white font-bold">${{ Number(summary?.total_revenue).toFixed(2) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Top Products -->
    <div v-if="topProducts.length" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-800">
        <h3 class="text-white font-semibold">Top Products in Period</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-800">
              <th class="px-5 py-3 text-left text-xs text-gray-400 uppercase tracking-wider">#</th>
              <th class="px-5 py-3 text-left text-xs text-gray-400 uppercase tracking-wider">Product</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Units Sold</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Revenue</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">Share</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800">
            <tr v-for="(p, i) in topProducts" :key="p.product_id" class="hover:bg-gray-800/40">
              <td class="px-5 py-3 text-gray-600 font-mono text-xs">{{ i + 1 }}</td>
              <td class="px-5 py-3 text-gray-200">{{ p.product_name }}</td>
              <td class="px-5 py-3 text-right text-gray-400">{{ p.units_sold }}</td>
              <td class="px-5 py-3 text-right text-brand-400 font-semibold">${{ Number(p.revenue).toFixed(2) }}</td>
              <td class="px-5 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <div class="w-16 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500 rounded-full" :style="{ width: (p.revenue / topProducts[0].revenue * 100) + '%' }" />
                  </div>
                  <span class="text-gray-500 text-xs w-8 text-right">
                    {{ summary?.total_revenue ? Math.round(p.revenue / summary.total_revenue * 100) : 0 }}%
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- New Customer Registrations -->
    <div v-if="newCustomers.length" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-white font-semibold">New Customer Registrations</h3>
        <span class="text-gray-500 text-xs">{{ newCustomers.reduce((s, c) => s + c.count, 0) }} total</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-800">
              <th class="px-5 py-3 text-left text-xs text-gray-400 uppercase tracking-wider">Date</th>
              <th class="px-5 py-3 text-right text-xs text-gray-400 uppercase tracking-wider">New Customers</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800">
            <tr v-for="row in newCustomers" :key="row.date" class="hover:bg-gray-800/40">
              <td class="px-5 py-3 text-gray-300">{{ fmtDate(row.date) }}</td>
              <td class="px-5 py-3 text-right text-white font-semibold">{{ row.count }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && !salesRows.length && !summary" class="text-center py-20 text-gray-500">
      <div class="text-5xl mb-4">📊</div>
      <p class="text-lg font-medium text-gray-400">Select a date range to generate a report</p>
      <p class="text-sm mt-1">Use the pickers or quick range buttons above</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { adminReportAPI } from '@/services/adminApi'
import { ArrowDownTrayIcon } from '@heroicons/vue/24/outline'
import { useToast } from 'vue-toastification'

const toast        = useToast()
const loading      = ref(false)
const salesRows    = ref([])
const topProducts  = ref([])
const newCustomers = ref([])
const summary      = ref(null)

const today    = new Date().toISOString().slice(0, 10)
const monthAgo = new Date(Date.now() - 30 * 86400000).toISOString().slice(0, 10)
const from     = ref(monthAgo)
const to       = ref(today)

const exportUrl = computed(() => adminReportAPI.exportUrl({ from: from.value, to: to.value }))

const quickRanges = [
  { label: 'Today',     days: 0 },
  { label: 'Last 7d',   days: 7 },
  { label: 'Last 30d',  days: 30 },
  { label: 'Last 90d',  days: 90 },
  { label: 'This Year', year: true },
]

function setRange(r) {
  to.value   = today
  from.value = r.year
    ? new Date().getFullYear() + '-01-01'
    : new Date(Date.now() - r.days * 86400000).toISOString().slice(0, 10)
  fetchAll()
}

const fmtDate = (d) =>
  new Date(d + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })

async function fetchAll() {
  if (!from.value || !to.value) { toast.error('Select a date range'); return }
  loading.value = true
  try {
    const [salesRes, productsRes, customersRes] = await Promise.all([
      adminReportAPI.sales({ from: from.value, to: to.value }),
      adminReportAPI.products({ from: from.value, to: to.value }),
      adminReportAPI.customers({ from: from.value, to: to.value }),
    ])
    salesRows.value    = salesRes.data.orders
    summary.value      = salesRes.data.summary
    topProducts.value  = productsRes.data.data ?? productsRes.data
    newCustomers.value = customersRes.data.newCustomers
  } catch {
    toast.error('Failed to load report')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.admin-input { @apply bg-gray-800 border border-gray-700 text-gray-200 text-sm rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500; }
</style>
