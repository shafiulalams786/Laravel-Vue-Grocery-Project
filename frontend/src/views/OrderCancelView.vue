<template>
  <main class="max-w-md mx-auto px-4 py-20 text-center animate-scale-in">
    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center text-4xl mx-auto mb-6">😔</div>
    <h1 class="font-extrabold text-3xl text-ink mb-3">{{ t('cancel.title') }}</h1>
    <p class="text-ink-secondary mb-8">{{ t('cancel.msg') }}</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <RouterLink to="/checkout" class="btn-primary py-3 px-6">{{ t('cancel.tryAgain') }}</RouterLink>
      <RouterLink to="/shop" class="btn-secondary py-3 px-6">{{ t('cancel.continueShopping') }}</RouterLink>
    </div>
  </main>
</template>
<script setup>
import { useI18n } from '@/i18n/index.js'
const { t } = useI18n()
</script>
