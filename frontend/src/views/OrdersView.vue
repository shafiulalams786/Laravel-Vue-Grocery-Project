<template>
  <main class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="font-extrabold text-3xl text-ink mb-8 tracking-tight">{{ t('orders.title') }}</h1>

    <div v-if="loading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-32 skeleton rounded-2xl" />
    </div>

    <div v-else-if="!orders.length" class="text-center py-20">
      <div class="w-20 h-20 bg-surface-muted rounded-2xl flex items-center justify-center text-4xl mx-auto mb-4">📦</div>
      <h2 class="font-bold text-xl text-ink">{{ t('orders.noOrders') }}</h2>
      <p class="text-ink-muted text-sm mt-1">{{ t('orders.noOrdersHint') }}</p>
      <RouterLink to="/shop" class="btn-primary mt-4 inline-flex">{{ t('orders.shopNow') }}</RouterLink>
    </div>

    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id" class="card p-5 hover:shadow-lift transition-all">
        <div class="flex items-start justify-between flex-wrap gap-3 mb-4">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-mono font-bold text-ink text-sm">{{ order.order_number }}</span>
              <span :class="['badge', statusBadge(order.status)]">{{ t(`status.${order.status}`) }}</span>
              <span :class="['badge', payBadge(order.payment_status)]">{{ t(`status.${order.payment_status}`) }}</span>
            </div>
            <p class="text-xs text-ink-muted mt-1">
              {{ fmtDate(order.created_at) }} · {{ order.items?.length }} {{ t('orders.items') }} · {{ t('orders.via') }} {{ payLabel(order.payment_method) }}
            </p>
          </div>
          <p class="font-extrabold text-xl text-ink">${{ order.total }}</p>
        </div>

        <!-- Item preview chips -->
        <div class="flex flex-wrap gap-1.5 mb-4">
          <span v-for="item in order.items?.slice(0, 3)" :key="item.id"
            class="text-xs bg-surface-muted text-ink-secondary px-2.5 py-1 rounded-lg">
            {{ item.product_name }} ×{{ item.quantity }}
          </span>
          <span v-if="order.items?.length > 3"
            class="text-xs bg-surface-muted text-ink-muted px-2.5 py-1 rounded-lg">
            +{{ order.items.length - 3 }} more
          </span>
        </div>

        <div class="flex items-center gap-3 pt-3 border-t border-border">
          <RouterLink :to="`/track-order?order=${order.order_number}`" class="btn-secondary text-xs py-2 px-3">
            <TruckIcon class="w-3.5 h-3.5" /> {{ t('orders.trackOrder') }}
          </RouterLink>
          <button v-if="['pending', 'confirmed'].includes(order.status)"
            @click="cancelOrder(order)"
            class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
            {{ t('orders.cancelOrder') }}
          </button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { orderAPI } from '@/services/api'
import { useI18n } from '@/i18n/index.js'
import { useToast } from 'vue-toastification'
import { TruckIcon } from '@heroicons/vue/24/outline'

const { t }    = useI18n()
const toast    = useToast()
const orders   = ref([])
const loading  = ref(true)

const statusBadge = (s) => ({ pending:'badge-yellow', confirmed:'badge-blue', processing:'badge-purple', out_for_delivery:'badge-orange', delivered:'badge-green', cancelled:'badge-red' }[s] || 'badge-gray')
const payBadge    = (s) => ({ paid:'badge-green', pending:'badge-yellow', failed:'badge-red', refunded:'badge-purple' }[s] || 'badge-gray')
const payLabel    = (m) => ({ stripe: t('orders.stripe'), paypal: t('orders.paypal'), cod: t('orders.cod') }[m] || m)
const fmtDate     = (d) => new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' })

async function cancelOrder(order) {
  if (!confirm(`${t('orders.confirmCancel')} ${order.order_number}?`)) return
  try {
    await orderAPI.cancel(order.order_number)
    order.status = 'cancelled'
    toast.success(t('orders.cancelled'))
  } catch (e) { toast.error(e.response?.data?.message || t('orders.cannotCancel')) }
}

onMounted(async () => {
  try { const { data } = await orderAPI.list(); orders.value = data.data ?? data } catch {}
  loading.value = false
})
</script>
