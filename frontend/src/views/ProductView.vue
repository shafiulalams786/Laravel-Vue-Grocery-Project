<template>
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-10">
      <div class="aspect-square skeleton rounded-3xl" />
      <div class="space-y-4 pt-4">
        <div class="h-8 skeleton w-3/4" /><div class="h-4 skeleton w-1/3" /><div class="h-20 skeleton" />
      </div>
    </div>

    <div v-else-if="product">
      <RouterLink :to="`/category/${product.category?.slug}`" class="inline-flex items-center gap-1 text-sm text-ink-muted hover:text-brand-600 transition-colors mb-6">
        <ChevronLeftIcon class="w-4 h-4" /> {{ product.category?.name }}
      </RouterLink>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-16">
        <!-- Image -->
        <div class="aspect-square bg-surface-muted rounded-3xl overflow-hidden shadow-card">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover product-img" />
          <div v-else class="w-full h-full flex items-center justify-center text-8xl">{{ catEmoji }}</div>
        </div>

        <!-- Info -->
        <div class="flex flex-col">
          <div class="flex flex-wrap gap-2 mb-3">
            <span v-if="product.discount_percentage" class="badge bg-red-500 text-white">-{{ product.discount_percentage }}% OFF</span>
            <span v-if="product.is_featured" class="badge shimmer-badge text-white">⭐ Featured</span>
          </div>

          <h1 class="font-extrabold text-3xl text-ink tracking-tight">{{ product.name }}</h1>
          <p class="text-brand-600 text-sm font-semibold mt-1">{{ product.category?.name }} · {{ t('product.per') }} {{ product.unit }}</p>

          <div class="flex items-end gap-3 mt-5 mb-6">
            <span class="text-4xl font-extrabold text-ink">${{ product.current_price }}</span>
            <span v-if="product.sale_price" class="text-xl text-ink-muted line-through mb-0.5">${{ product.price }}</span>
          </div>

          <p class="text-ink-secondary leading-relaxed text-sm mb-6">{{ product.description }}</p>

          <!-- Stock -->
          <div class="flex items-center gap-2 mb-6">
            <div :class="['w-2 h-2 rounded-full', product.stock > 10 ? 'bg-brand-500' : product.stock > 0 ? 'bg-amber-500' : 'bg-red-500']" />
            <span :class="['text-sm font-medium', product.stock > 10 ? 'text-brand-600' : product.stock > 0 ? 'text-amber-600' : 'text-red-600']">
              {{ product.stock > 10 ? t('product.inStock') : product.stock > 0 ? t('product.lowStock', { n: product.stock }) : t('product.outOfStock') }}
            </span>
          </div>

          <!-- Qty + Add -->
          <div class="flex items-center gap-4 mb-6">
            <div class="flex items-center border-2 border-border rounded-2xl overflow-hidden">
              <button @click="qty = Math.max(1, qty - 1)" class="w-11 h-12 flex items-center justify-center hover:bg-surface-muted transition-colors text-ink-secondary">
                <MinusIcon class="w-4 h-4" />
              </button>
              <span class="w-10 text-center font-bold text-ink">{{ qty }}</span>
              <button @click="qty++" class="w-11 h-12 flex items-center justify-center hover:bg-surface-muted transition-colors text-ink-secondary">
                <PlusIcon class="w-4 h-4" />
              </button>
            </div>
            <button @click="addToCart" :disabled="adding || product.stock === 0"
              class="flex-1 flex items-center justify-center gap-2 py-3.5 bg-brand-600 text-white font-bold rounded-2xl hover:bg-brand-700 disabled:opacity-50 transition-all active:scale-95 shadow-sm text-sm">
              <ShoppingBagIcon class="w-5 h-5" />
              {{ adding ? t('product.adding') : product.stock === 0 ? t('product.outOfStock') : t('product.addToCart') }}
            </button>
          </div>

          <div v-if="product.origin" class="flex items-center gap-2 text-sm text-ink-muted p-3 bg-surface-muted rounded-xl">
            🌍 <span>{{ t('product.origin') }}: <span class="font-medium text-ink-secondary">{{ product.origin }}</span></span>
          </div>
        </div>
      </div>

      <!-- Related -->
      <div v-if="related.length">
        <h2 class="section-title mb-6">{{ t('product.relatedTitle') }}</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
          <ProductCard v-for="p in related" :key="p.id" :product="p" />
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { productAPI } from '@/services/api'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from '@/i18n/index.js'
import ProductCard from '@/components/product/ProductCard.vue'
import { ChevronLeftIcon, MinusIcon, PlusIcon, ShoppingBagIcon } from '@heroicons/vue/24/outline'

const { t } = useI18n()
const route = useRoute(), cart = useCartStore(), auth = useAuthStore()
const product = ref(null), related = ref([]), loading = ref(true), qty = ref(1), adding = ref(false)

const catEmojis = { 'fresh-fruits':'🍎','vegetables':'🥦','dairy-eggs':'🥛','meat-fish':'🥩','bakery':'🍞','beverages':'🧃','snacks':'🍪','pantry':'🫙' }
const catEmoji  = computed(() => catEmojis[product.value?.category?.slug] || '🛒')

async function addToCart() {
  adding.value = true
  await cart.addItem(product.value.id, qty.value, auth.isAuthenticated)
  adding.value = false
}

onMounted(async () => {
  try {
    const { data } = await productAPI.show(route.params.slug)
    product.value = data.product; related.value = data.related
  } catch {}
  loading.value = false
})
</script>
