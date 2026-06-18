<template>
  <main class="max-w-2xl mx-auto px-4 sm:px-6 py-12">
    <h1 class="font-extrabold text-3xl text-ink mb-2">{{ t('track.title') }}</h1>
    <p class="text-ink-secondary text-sm mb-8">{{ t('track.subtitle') }}</p>

    <div class="card p-5 mb-6">
      <div class="flex gap-3">
        <input v-model="num" @keyup.enter="doTrack" type="text" :placeholder="t('track.placeholder')" class="input flex-1" />
        <button @click="doTrack" :disabled="loading"
          class="btn-primary px-6 flex-shrink-0 font-bold">
          <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
          <span v-else>{{ t('track.trackBtn') }}</span>
        </button>
      </div>
    </div>

    <div v-if="err" class="p-4 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm flex items-center gap-2 mb-6">
      <ExclamationCircleIcon class="w-5 h-5 flex-shrink-0" /> {{ err }}
    </div>

    <div v-if="order" class="space-y-5 animate-fade-up">
      <!-- Status banner -->
      <div :class="['rounded-2xl p-5 text-white', statusBg(order.status)]">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-mono font-bold text-lg">{{ order.order_number }}</p>
            <p class="text-white/70 text-xs mt-0.5">{{ fmtDate(order.created_at) }}</p>
          </div>
          <span class="bg-white/20 backdrop-blur px-3 py-1 rounded-full text-xs font-bold capitalize">
            {{ t(`status.${order.status}`) }}
          </span>
        </div>
      </div>

      <!-- Progress timeline -->
      <div class="card p-6">
        <h3 class="font-bold text-ink mb-6">{{ t('track.progress') }}</h3>
        <div class="relative pl-6">
          <div class="absolute left-4 top-4 bottom-4 w-0.5 bg-border" />
          <div class="space-y-6">
            <div v-for="(step, i) in steps" :key="i" class="relative flex items-start gap-4">
              <div :class="['absolute -left-6 w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 z-10 transition-all',
                isDone(step.key) ? 'bg-brand-600 border-brand-600' : isCurrent(step.key) ? 'bg-white border-brand-500 shadow-glow-sm' : 'bg-white border-border']">
                <CheckIcon v-if="isDone(step.key)" class="w-2.5 h-2.5 text-white" />
                <div v-else-if="isCurrent(step.key)" class="w-2 h-2 rounded-full bg-brand-500" />
              </div>
              <div :class="['pt-0.5', isDone(step.key) || isCurrent(step.key) ? 'opacity-100' : 'opacity-40']">
                <p class="font-semibold text-sm text-ink">{{ step.label }}</p>
                <p class="text-xs text-ink-muted">{{ step.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="card p-5">
        <h3 class="font-bold text-ink mb-4">{{ t('track.itemsOrdered') }}</h3>
        <div class="space-y-2.5">
          <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between text-sm py-1 border-b border-border last:border-0">
            <span class="text-ink-secondary">{{ item.product_name }}</span>
            <span class="text-ink-muted">× {{ item.quantity }}</span>
            <span class="font-semibold text-ink">${{ item.total }}</span>
          </div>
        </div>
        <div class="flex items-center justify-between font-bold text-ink mt-3 pt-3 border-t border-border">
          <span>{{ t('track.totalPaid') }}</span><span>${{ order.total }}</span>
        </div>
      </div>

      <!-- Address -->
      <div class="card p-5">
        <h3 class="font-bold text-ink mb-3">{{ t('track.deliveryAddress') }}</h3>
        <div class="flex items-start gap-3 text-sm text-ink-secondary">
          <MapPinIcon class="w-5 h-5 text-brand-600 flex-shrink-0 mt-0.5" />
          <div>
            <p>{{ order.delivery_address?.street }}</p>
            <p>{{ order.delivery_address?.city }}, {{ order.delivery_address?.state }} {{ order.delivery_address?.zip }}</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { orderAPI } from '@/services/api'
import { useI18n } from '@/i18n/index.js'
import { ExclamationCircleIcon, CheckIcon, MapPinIcon } from '@heroicons/vue/24/outline'

const { t } = useI18n()
const route = useRoute()
const num = ref(route.query.order || ''), order = ref(null), loading = ref(false), err = ref('')

const statusOrder = ['pending','confirmed','processing','out_for_delivery','delivered']

const steps = computed(() => [
  { key: 'pending',          label: t('track.step1'), desc: t('track.step1Desc') },
  { key: 'confirmed',        label: t('track.step2'), desc: t('track.step2Desc') },
  { key: 'processing',       label: t('track.step3'), desc: t('track.step3Desc') },
  { key: 'out_for_delivery', label: t('track.step4'), desc: t('track.step4Desc') },
  { key: 'delivered',        label: t('track.step5'), desc: t('track.step5Desc') },
])

const isDone    = (k) => { if (!order.value || order.value.status === 'cancelled') return false; return statusOrder.indexOf(order.value.status) > statusOrder.indexOf(k) }
const isCurrent = (k) => order.value?.status === k
const statusBg  = (s) => ({ pending:'bg-amber-500', confirmed:'bg-blue-500', processing:'bg-violet-500', out_for_delivery:'bg-orange-500', delivered:'bg-brand-600', cancelled:'bg-red-500' }[s] || 'bg-slate-600')
const fmtDate   = (d) => new Date(d).toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' })

async function doTrack() {
  if (!num.value.trim()) return
  loading.value = true; err.value = ''; order.value = null
  try { const { data } = await orderAPI.track(num.value.trim()); order.value = data }
  catch { err.value = t('track.notFound') }
  loading.value = false
}
onMounted(() => { if (num.value) doTrack() })
</script>
