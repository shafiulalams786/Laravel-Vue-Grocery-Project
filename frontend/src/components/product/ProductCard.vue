<template>
  <div class="card group cursor-pointer animate-fade-up" @click="$router.push(`/product/${product.slug}`)">
    <!-- Image -->
    <div class="relative overflow-hidden aspect-square bg-surface-muted">
      <img v-if="product.image_url" :src="product.image_url" :alt="product.name"
        class="w-full h-full object-cover product-img" />
      <div v-else class="w-full h-full flex items-center justify-center text-5xl">
        {{ catEmoji }}
      </div>

      <!-- Badges -->
      <div class="absolute top-2.5 left-2.5 flex flex-col gap-1.5">
        <span v-if="product.discount_percentage"
          class="badge bg-red-500 text-white text-xs font-bold shadow-sm">
          -{{ product.discount_percentage }}%
        </span>
        <span v-else-if="product.is_featured"
          class="badge shimmer-badge text-white text-xs font-bold shadow-sm">
          ⭐ Featured
        </span>
      </div>

      <!-- Quick add overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-3">
        <button @click.stop="addToCart"
          :disabled="adding"
          class="flex items-center gap-1.5 px-4 py-2 bg-white text-ink text-xs font-semibold rounded-xl shadow-lift hover:bg-brand-600 hover:text-white transition-all active:scale-95 disabled:opacity-50">
          <ShoppingBagIcon class="w-3.5 h-3.5" />
          {{ adding ? t('product.adding') : t('product.addToCart') }}
        </button>
      </div>
    </div>

    <!-- Info -->
    <div class="p-3.5">
      <p class="text-xs font-semibold text-brand-600 mb-0.5">{{ product.category?.name }}</p>
      <h3 class="font-semibold text-sm text-ink leading-snug line-clamp-2">{{ product.name }}</h3>
      <p class="text-xs text-ink-muted mt-0.5">{{ t('product.per') }} {{ product.unit }}</p>

      <div class="flex items-center justify-between mt-3">
        <div>
          <span class="font-bold text-ink">${{ product.current_price }}</span>
          <span v-if="product.sale_price" class="text-xs text-ink-muted line-through ml-1.5">${{ product.price }}</span>
        </div>
        <button @click.stop="addToCart"
          :disabled="adding"
          class="w-8 h-8 bg-brand-600 text-white rounded-xl flex items-center justify-center hover:bg-brand-700 transition-all hover:scale-110 active:scale-90 disabled:opacity-50 shadow-sm">
          <PlusIcon class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from '@/i18n/index.js'
import { ShoppingBagIcon, PlusIcon } from '@heroicons/vue/24/outline'

const props  = defineProps({ product: { type: Object, required: true } })
const { t }  = useI18n()
const cart   = useCartStore()
const auth   = useAuthStore()
const adding = ref(false)

const catEmojis = { 'fresh-fruits':'🍎','vegetables':'🥦','dairy-eggs':'🥛','meat-fish':'🥩','bakery':'🍞','beverages':'🧃','snacks':'🍪','pantry':'🫙' }
const catEmoji  = computed(() => catEmojis[props.product.category?.slug] || '🛒')

async function addToCart() {
  adding.value = true
  await cart.addItem(props.product.id, 1, auth.isAuthenticated)
  adding.value = false
}
</script>
