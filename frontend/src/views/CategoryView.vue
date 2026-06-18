<template>
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="i in 8" :key="i" class="h-72 skeleton rounded-2xl" />
    </div>
    <div v-else>
      <div class="mb-6">
        <RouterLink to="/shop" class="text-xs text-ink-muted hover:text-brand-600 transition-colors">← {{ t('nav.allProducts') }}</RouterLink>
        <h1 class="font-extrabold text-3xl text-ink mt-2">{{ category?.name }}</h1>
        <p class="text-ink-muted text-sm mt-0.5">{{ products.length }} {{ t('shop.products') }}</p>
      </div>
      <div v-if="!products.length" class="text-center py-20 text-ink-muted">
        <div class="text-5xl mb-3">🌿</div><p>{{ t('shop.noProducts') }}</p>
      </div>
      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>
    </div>
  </main>
</template>
<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { categoryAPI } from '@/services/api'
import ProductCard from '@/components/product/ProductCard.vue'
import { useI18n } from '@/i18n/index.js'

const { t } = useI18n()
const route = useRoute()
const category = ref(null), products = ref([]), loading = ref(true)

async function load() {
  loading.value = true
  const { data } = await categoryAPI.show(route.params.slug)
  category.value = data.category; products.value = data.products?.data ?? data.products
  loading.value = false
}
onMounted(load); watch(() => route.params.slug, load)
</script>
