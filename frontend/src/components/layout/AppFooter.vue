<template>
  <footer class="bg-slate-900 text-white mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-8 mb-12">

        <div class="col-span-2 sm:col-span-1">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center text-sm">🌿</div>
            <span class="font-bold text-lg">Fresh<span class="text-brand-400">Basket</span></span>
          </div>
          <p class="text-slate-400 text-sm leading-relaxed">{{ t('footer.tagline') }}</p>
          <div class="flex gap-3 mt-5">
            <a href="#" v-for="icon in ['📘','📸','🐦']" :key="icon"
              class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center hover:bg-brand-600 transition-colors text-sm">
              {{ icon }}
            </a>
          </div>
        </div>

        <div>
          <p class="font-semibold text-white mb-4 text-sm">{{ t('footer.shop') }}</p>
          <ul class="space-y-2.5">
            <li v-for="link in shopLinks" :key="link.to">
              <RouterLink :to="link.to" class="text-slate-400 text-sm hover:text-brand-400 transition-colors">{{ link.label }}</RouterLink>
            </li>
          </ul>
        </div>

        <div>
          <p class="font-semibold text-white mb-4 text-sm">{{ t('footer.help') }}</p>
          <ul class="space-y-2.5">
            <li v-for="link in helpLinks" :key="link.to || link.label">
              <RouterLink v-if="link.to" :to="link.to" class="text-slate-400 text-sm hover:text-brand-400 transition-colors">{{ link.label }}</RouterLink>
              <a v-else href="#" class="text-slate-400 text-sm hover:text-brand-400 transition-colors">{{ link.label }}</a>
            </li>
          </ul>
        </div>

        <div>
          <p class="font-semibold text-white mb-4 text-sm">{{ t('footer.securePayment') }}</p>
          <div class="space-y-2.5">
            <div class="flex items-center gap-2 text-sm text-slate-400">
              <span class="text-blue-400 font-bold text-xs">STRIPE</span> {{ t('footer.creditCards') }}
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-400">
              <span class="text-yellow-400 font-bold italic text-xs">PayPal</span> {{ t('footer.paypalCheckout') }}
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-400">
              <span class="text-brand-400">💵</span> {{ t('footer.cashDelivery') }}
            </div>
          </div>
          <p class="text-xs text-slate-500 mt-4 flex items-center gap-1">🔒 {{ t('footer.ssl') }}</p>
        </div>
      </div>

      <div class="border-t border-white/10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
        <p>{{ t('footer.rights') }}</p>
        <p>{{ t('footer.madeWith') }}</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from '@/i18n/index.js'
const { t } = useI18n()

const shopLinks = computed(() => [
  { to: '/shop',                   label: t('footer.allProducts') },
  { to: '/category/fresh-fruits',  label: t('footer.fruits') },
  { to: '/category/vegetables',    label: t('footer.vegetables') },
  { to: '/category/dairy-eggs',    label: t('footer.dairy') },
  { to: '/category/meat-fish',     label: t('footer.meat') },
])
const helpLinks = computed(() => [
  { to: '/track-order', label: t('footer.trackOrder') },
  { label: t('footer.delivery') },
  { label: t('footer.returns') },
  { label: t('footer.contact') },
])
</script>
