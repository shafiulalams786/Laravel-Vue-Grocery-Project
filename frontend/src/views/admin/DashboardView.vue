<template>
  <div class="space-y-6">

    <!-- Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      <StatCard
        label="Total Revenue"
        :value="'$' + (stats?.revenue?.total ?? 0).toLocaleString()"
        :sub="'$' + (stats?.revenue?.month ?? 0).toFixed(2) + ' this month'"
        :icon="CurrencyDollarIcon"
        icon-bg="bg-brand-900/40"
        icon-color="text-brand-400"
        :change="stats?.revenue?.change_percent"
      />
      <StatCard
        label="Total Orders"
        :value="stats?.orders?.total ?? 0"
        :sub="(stats?.orders?.today ?? 0) + ' today'"
        :icon="ShoppingBagIcon"
        icon-bg="bg-blue-900/40"
        icon-color="text-blue-400"
      />
      <StatCard
        label="Customers"
        :value="stats?.customers?.total ?? 0"
        :sub="(stats?.customers?.new_this_month ?? 0) + ' new this month'"
        :icon="UsersIcon"
        icon-bg="bg-purple-900/40"
        icon-color="text-purple-400"
      />
      <StatCard
        label="Avg Order Value"
        :value="'$' + (stats?.avg_order_value ?? 0).toFixed(2)"
        :sub="(stats?.orders?.pending ?? 0) + ' pending orders'"
        :icon="ChartBarIcon"
        icon-bg="bg-orange-900/40"
        icon-color="text-orange-400"
      />
    </div>

    <!-- Revenue Chart + Donut row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2">
        <RevenueChart
          :data="chartData"
          :loading="chartLoading"
          :period="chartPeriod"
          @period-change="onPeriodChange"
        />
      </div>
      <DonutChart
        title="Orders by Status"
        :items="orderStatusItems"
        :loading="statsLoading"
      />
    </div>

    <!-- Payment breakdown + Category revenue -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

      <!-- Payment methods -->
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h3 class="text-white font-semibold mb-4">Revenue by Payment Method</h3>
        <div v-if="statsLoading" class="space-y-3">
          <div v-for="i in 3" :key="i" class="h-10 bg-gray-800 rounded-xl animate-pulse" />
        </div>
        <div v-else class="space-y-3">
          <div v-for="pm in paymentMethods" :key="pm.payment_method" class="flex items-center gap-3">
            <div :class="['w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0', pm.bg]">
              <span class="text-sm">{{ pm.emoji }}</span>
            </div>
            <div class="flex-1">
              <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-300 capitalize font-medium">{{ pm.label }}</span>
                <span class="text-white font-bold">${{ Number(pm.revenue ?? 0).toFixed(2) }}</span>
              </div>
              <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500" :class="pm.bar"
                  :style="{ width: maxPmRevenue ? (pm.revenue / maxPmRevenue * 100) + '%' : '0%' }" />
              </div>
            </div>
            <span class="text-gray-500 text-xs w-8 text-right">{{ pm.count }}</span>
          </div>
        </div>
      </div>

      <!-- Top products -->
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h3 class="text-white font-semibold mb-4">Top Selling Products</h3>
        <div v-if="statsLoading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-8 bg-gray-800 rounded animate-pulse" />
        </div>
        <div v-else class="space-y-2">
          <div v-for="(p, i) in topProducts" :key="p.product_id" class="flex items-center gap-3">
            <span class="text-gray-600 text-xs w-4 text-center font-mono">{{ i + 1 }}</span>
            <div class="flex-1 min-w-0">
              <p class="text-gray-300 text-sm truncate">{{ p.product_name }}</p>
              <p class="text-gray-600 text-xs">{{ p.units_sold }} units</p>
            </div>
            <span class="text-brand-400 text-sm font-bold">${{ Number(p.revenue).toFixed(2) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Low stock + Recent orders -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

      <!-- Low stock alerts -->
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-white font-semibold">Low Stock Alerts</h3>
          <RouterLink to="/admin/products?stock=low" class="text-xs text-brand-400 hover:text-brand-300">View all →</RouterLink>
        </div>
        <div v-if="!lowStock.length" class="text-center py-6 text-gray-500 text-sm">
          ✅ All products are well stocked
        </div>
        <div v-else class="space-y-2">
          <div v-for="p in lowStock.slice(0, 6)" :key="p.id" class="flex items-center justify-between p-2.5 bg-gray-800/50 rounded-xl">
            <span class="text-gray-300 text-sm truncate flex-1">{{ p.name }}</span>
            <span :class="['text-xs font-bold px-2 py-0.5 rounded-full ml-2', p.stock === 0 ? 'bg-red-900/50 text-red-400' : 'bg-yellow-900/50 text-yellow-400']">
              {{ p.stock === 0 ? 'Out' : p.stock + ' left' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Recent orders -->
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-white font-semibold">Recent Orders</h3>
          <RouterLink to="/admin/orders" class="text-xs text-brand-400 hover:text-brand-300">View all →</RouterLink>
        </div>
        <div v-if="statsLoading" class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-10 bg-gray-800 rounded-xl animate-pulse" />
        </div>
        <div v-else class="space-y-2">
          <div v-for="order in recentOrders" :key="order.id"
            class="flex items-center gap-3 p-2.5 bg-gray-800/50 rounded-xl hover:bg-gray-800 transition-colors cursor-pointer"
            @click="$router.push('/admin/orders/' + order.id)">
            <div class="flex-1 min-w-0">
              <p class="text-gray-300 text-sm font-medium truncate">{{ order.order_number }}</p>
              <p class="text-gray-500 text-xs">{{ order.guest_name || order.user?.name || 'Guest' }}</p>
            </div>
            <AdminBadge :color="statusColor(order.status)">{{ order.status }}</AdminBadge>
            <span class="text-white text-sm font-bold">${{ order.total }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { dashboardAPI } from '@/services/adminApi'
import { useAdminStore } from '@/stores/admin/adminStore'
import StatCard     from '@/components/admin/ui/StatCard.vue'
import AdminBadge   from '@/components/admin/ui/AdminBadge.vue'
import RevenueChart from '@/components/admin/charts/RevenueChart.vue'
import DonutChart   from '@/components/admin/charts/DonutChart.vue'
import {
  CurrencyDollarIcon, ShoppingBagIcon, UsersIcon, ChartBarIcon,
} from '@heroicons/vue/24/outline'

const adminStore  = useAdminStore()
const stats       = computed(() => adminStore.stats)
const statsLoading = ref(true)
const chartLoading = ref(true)
const chartData    = ref([])
const chartPeriod  = ref(30)
const topProducts  = ref([])
const lowStock     = ref([])
const recentOrders = ref([])
const paymentData  = ref([])

const orderStatusItems = computed(() => {
  const s = adminStore.stats?.orders
  if (!s) return []
  return [
    { label: 'Pending',      value: s.pending ?? 0 },
    { label: 'Processing',   value: s.processing ?? 0 },
    { label: 'Delivered',    value: (s.total ?? 0) - (s.pending ?? 0) - (s.processing ?? 0) },
  ].filter(i => i.value > 0)
})

const pmMeta = {
  stripe:  { label: 'Stripe (Card)', emoji: '💳', bg: 'bg-blue-900/40',   bar: 'bg-blue-500' },
  paypal:  { label: 'PayPal',        emoji: '🟡', bg: 'bg-yellow-900/40', bar: 'bg-yellow-500' },
  cod:     { label: 'Cash on Delivery', emoji: '💵', bg: 'bg-brand-900/40', bar: 'bg-brand-500' },
}
const paymentMethods = computed(() =>
  paymentData.value.map(p => ({ ...p, ...(pmMeta[p.payment_method] ?? { label: p.payment_method, emoji: '💰', bg: 'bg-gray-800', bar: 'bg-gray-500' }) }))
)
const maxPmRevenue = computed(() => Math.max(...paymentMethods.value.map(p => Number(p.revenue ?? 0)), 1))

function statusColor(s) {
  return { pending: 'yellow', confirmed: 'blue', processing: 'purple', out_for_delivery: 'orange', delivered: 'green', cancelled: 'red' }[s] ?? 'gray'
}

async function onPeriodChange(p) {
  chartPeriod.value = p
  chartLoading.value = true
  const { data } = await dashboardAPI.revenueChart(p)
  chartData.value = data
  chartLoading.value = false
}

onMounted(async () => {
  await adminStore.fetchStats()
  statsLoading.value = false

  const [chart, top, low, recent, payment] = await Promise.all([
    dashboardAPI.revenueChart(30),
    dashboardAPI.topProducts(5),
    dashboardAPI.lowStock(),
    dashboardAPI.recentOrders(),
    dashboardAPI.ordersByPayment(),
  ])
  chartData.value   = chart.data
  topProducts.value = top.data
  lowStock.value    = low.data
  recentOrders.value = recent.data
  paymentData.value  = payment.data
  chartLoading.value = false
})
</script>
