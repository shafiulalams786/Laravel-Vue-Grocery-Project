<template>
  <main class="max-w-xl mx-auto px-4 py-16 text-center">
    <div v-if="loading" class="flex justify-center py-24">
      <div class="w-10 h-10 border-4 border-brand-200 border-t-brand-600 rounded-full animate-spin" />
    </div>
    <div v-else class="animate-scale-in">
      <div class="relative w-24 h-24 mx-auto mb-6">
        <div class="absolute inset-0 bg-brand-400 rounded-full pulse-ring" />
        <div class="relative w-24 h-24 bg-brand-600 rounded-full flex items-center justify-center shadow-glow">
          <CheckIcon class="w-12 h-12 text-white" />
        </div>
      </div>

      <h1 class="font-extrabold text-4xl text-ink mb-3">{{ t('success.title') }}</h1>
      <p class="text-ink-secondary mb-8 max-w-sm mx-auto">
        {{ method === 'cod' ? t('success.codMsg') : t('success.paidMsg') }}
      </p>

      <div class="card p-5 text-left mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-bold text-ink">{{ t('success.orderDetails') }}</h2>
          <span class="badge badge-green">{{ t('success.confirmed') }}</span>
        </div>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between text-ink-secondary">
            <span>{{ t('success.orderNumber') }}</span>
            <span class="font-mono font-bold text-ink">{{ orderNum }}</span>
          </div>
          <div class="flex justify-between text-ink-secondary">
            <span>{{ t('success.payment') }}</span>
            <span class="font-medium text-ink">{{ payLabel }}</span>
          </div>
          <div class="flex justify-between text-ink-secondary">
            <span>{{ t('success.estimatedDelivery') }}</span>
            <span class="font-medium text-ink">{{ t('success.tomorrow') }} {{ deliveryDay }}</span>
          </div>
        </div>
      </div>

      <div class="card p-5 text-left mb-8">
        <h3 class="font-bold text-ink mb-4">{{ t('success.whatsNext') }}</h3>
        <div class="space-y-3">
          <div v-for="(s, i) in nextSteps" :key="i" class="flex items-start gap-3">
            <div class="w-6 h-6 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">{{ i+1 }}</div>
            <div>
              <p class="font-semibold text-sm text-ink">{{ s.title }}</p>
              <p class="text-xs text-ink-muted">{{ s.desc }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <RouterLink :to="`/track-order?order=${orderNum}`" class="btn-primary py-3 px-6">
          <TruckIcon class="w-4 h-4" /> {{ t('success.trackOrder') }}
        </RouterLink>
        <RouterLink to="/shop" class="btn-secondary py-3 px-6">{{ t('success.continueShopping') }}</RouterLink>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { paymentAPI } from '@/services/api'
import { useI18n } from '@/i18n/index.js'
import { CheckIcon, TruckIcon } from '@heroicons/vue/24/outline'

const { t } = useI18n()
const route  = useRoute()
const loading = ref(false)
const orderNum = ref(route.query.order || '')
const method   = ref(route.query.method || 'stripe')

const payLabel   = computed(() => ({ stripe: t('success.paymentStripe'), paypal: t('success.paymentPaypal'), cod: t('success.paymentCOD') }[method.value] || method.value))
const deliveryDay = computed(() => { const d = new Date(); d.setDate(d.getDate()+1); return d.toLocaleDateString('en-US',{weekday:'long',month:'short',day:'numeric'}) })
const nextSteps   = computed(() => [
  { title: t('success.step1Title'), desc: t('success.step1Desc') },
  { title: t('success.step2Title'), desc: t('success.step2Desc') },
  { title: t('success.step3Title'), desc: t('success.step3Desc') },
  { title: method.value === 'cod' ? t('success.step4TitleCOD') : t('success.step4Title'), desc: method.value === 'cod' ? t('success.step4DescCOD') : t('success.step4Desc') },
])

onMounted(async () => {
  const pendingId  = localStorage.getItem('pending_paypal_id')
  const pendingNum = localStorage.getItem('pending_paypal_order')
  if (route.query.token && pendingId && pendingNum) {
    loading.value = true
    try {
      await paymentAPI.capturePaypalOrder({ paypal_order_id: pendingId, order_number: pendingNum })
      orderNum.value = pendingNum; method.value = 'paypal'
      localStorage.removeItem('pending_paypal_id')
      localStorage.removeItem('pending_paypal_order')
    } catch {}
    loading.value = false
  }
})
</script>
