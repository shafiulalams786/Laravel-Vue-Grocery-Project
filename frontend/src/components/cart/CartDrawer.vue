<template>
  <Transition name="fade">
    <div v-if="cartStore.isOpen" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50" @click="cartStore.closeCart" />
  </Transition>

  <Transition name="slide">
    <aside v-if="cartStore.isOpen" class="fixed right-0 top-0 h-full w-full sm:w-96 bg-white z-50 flex flex-col shadow-2xl">

      <!-- Header -->
      <div class="flex items-center justify-between px-5 py-4 border-b border-border">
        <div>
          <h2 class="font-bold text-lg text-ink">{{ t('cart.title') }}</h2>
          <p class="text-xs text-ink-muted mt-0.5">
            {{ cartStore.itemCount === 1 ? t('cart.item', { n: cartStore.itemCount }) : t('cart.items', { n: cartStore.itemCount }) }}
          </p>
        </div>
        <button @click="cartStore.closeCart" class="p-2 rounded-xl hover:bg-surface-muted transition-colors text-ink-muted hover:text-ink">
          <XMarkIcon class="w-5 h-5" />
        </button>
      </div>

      <!-- Free delivery progress -->
      <div v-if="cartStore.summary.remaining_for_free_delivery > 0"
        class="px-5 py-3 bg-brand-50 border-b border-brand-100">
        <div class="flex items-center justify-between mb-1.5 text-xs">
          <span class="text-brand-700" v-html="t('cart.freeDeliveryMsg', { n: cartStore.summary.remaining_for_free_delivery?.toFixed(2) })" />
          <TruckIcon class="w-4 h-4 text-brand-500 flex-shrink-0" />
        </div>
        <div class="h-1.5 bg-brand-100 rounded-full overflow-hidden">
          <div class="h-full bg-brand-500 rounded-full transition-all duration-700"
            :style="{ width: `${Math.min(100, (cartStore.summary.subtotal / 50) * 100)}%` }" />
        </div>
      </div>
      <div v-else class="px-5 py-2.5 bg-brand-600 text-white text-xs font-medium flex items-center gap-2">
        <TruckIcon class="w-4 h-4" /> {{ t('cart.freeDeliveryUnlocked') }}
      </div>

      <!-- Items -->
      <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
        <div v-if="cartStore.isEmpty" class="flex flex-col items-center justify-center h-full text-center py-12">
          <div class="w-20 h-20 bg-surface-muted rounded-2xl flex items-center justify-center text-4xl mb-4">🛒</div>
          <p class="font-semibold text-ink">{{ t('cart.empty') }}</p>
          <p class="text-sm text-ink-muted mt-1">{{ t('cart.emptyHint') }}</p>
          <button @click="cartStore.closeCart(); $router.push('/shop')" class="btn-primary mt-4 text-sm">
            {{ t('cart.startShopping') }}
          </button>
        </div>

        <TransitionGroup name="fade-up" tag="div" class="space-y-3">
          <div v-for="item in cartStore.items" :key="item.id"
            class="flex gap-3 p-3 rounded-2xl border border-border hover:border-brand-200 hover:bg-brand-50/30 transition-all group">
            <div class="w-14 h-14 bg-surface-muted rounded-xl overflow-hidden flex-shrink-0">
              <img v-if="item.product?.image_url" :src="item.product.image_url" :alt="item.product.name" class="w-full h-full object-cover" />
              <div v-else class="w-full h-full flex items-center justify-center text-2xl">
                {{ catEmoji(item.product?.category?.slug) }}
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-ink truncate">{{ item.product?.name }}</p>
              <p class="text-xs text-ink-muted">{{ item.product?.unit }}</p>
              <div class="flex items-center justify-between mt-2">
                <div class="flex items-center border border-border rounded-lg overflow-hidden">
                  <button @click="changeQty(item, item.quantity - 1)" class="w-7 h-7 flex items-center justify-center hover:bg-surface-muted transition-colors text-ink-secondary">
                    <MinusIcon class="w-3 h-3" />
                  </button>
                  <span class="w-7 text-center text-sm font-semibold text-ink">{{ item.quantity }}</span>
                  <button @click="changeQty(item, item.quantity + 1)" class="w-7 h-7 flex items-center justify-center hover:bg-surface-muted transition-colors text-ink-secondary">
                    <PlusIcon class="w-3 h-3" />
                  </button>
                </div>
                <span class="text-sm font-bold text-ink">${{ (item.price * item.quantity).toFixed(2) }}</span>
              </div>
            </div>
            <button @click="removeItem(item)" class="opacity-0 group-hover:opacity-100 self-start p-1 text-ink-muted hover:text-red-500 transition-all rounded-lg hover:bg-red-50">
              <TrashIcon class="w-3.5 h-3.5" />
            </button>
          </div>
        </TransitionGroup>
      </div>

      <!-- Footer -->
      <div v-if="!cartStore.isEmpty" class="px-5 py-4 border-t border-border space-y-3 bg-surface-soft">
        <div class="space-y-1.5 text-sm">
          <div class="flex justify-between text-ink-secondary">
            <span>{{ t('cart.subtotal') }}</span><span>${{ cartStore.summary.subtotal?.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-ink-secondary">
            <span>{{ t('cart.delivery') }}</span>
            <span :class="cartStore.summary.delivery_fee === 0 ? 'text-brand-600 font-semibold' : ''">
              {{ cartStore.summary.delivery_fee === 0 ? t('cart.free') : `$${cartStore.summary.delivery_fee?.toFixed(2)}` }}
            </span>
          </div>
          <div class="flex justify-between text-ink-secondary">
            <span>{{ t('cart.tax') }}</span><span>${{ cartStore.summary.tax?.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between font-bold text-base text-ink pt-2 border-t border-border">
            <span>{{ t('cart.total') }}</span><span>${{ cartStore.summary.total?.toFixed(2) }}</span>
          </div>
        </div>

        <RouterLink to="/checkout" @click="cartStore.closeCart()"
          class="flex items-center justify-center gap-2 w-full py-3.5 bg-brand-600 text-white font-bold rounded-xl hover:bg-brand-700 transition-all hover:shadow-lg active:scale-95 text-sm">
          {{ t('cart.checkout') }} · ${{ cartStore.summary.total?.toFixed(2) }}
          <ArrowRightIcon class="w-4 h-4" />
        </RouterLink>

        <button @click="cartStore.closeCart(); $router.push('/shop')"
          class="w-full text-xs text-ink-muted hover:text-brand-600 transition-colors py-1 text-center">
          {{ t('cart.continueShopping') }}
        </button>
      </div>
    </aside>
  </Transition>
</template>

<script setup>
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useI18n } from '@/i18n/index.js'
import { XMarkIcon, TruckIcon, MinusIcon, PlusIcon, TrashIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'

const { t }      = useI18n()
const cartStore  = useCartStore()
const authStore  = useAuthStore()

const catEmojis = { 'fresh-fruits':'🍎','vegetables':'🥦','dairy-eggs':'🥛','meat-fish':'🥩','bakery':'🍞','beverages':'🧃','snacks':'🍪','pantry':'🫙' }
const catEmoji  = (slug) => catEmojis[slug] || '🛒'

async function changeQty(item, qty) {
  if (qty < 1) await cartStore.removeItem(item.id, authStore.isAuthenticated)
  else          await cartStore.updateItem(item.id, qty, authStore.isAuthenticated)
}
async function removeItem(item) { await cartStore.removeItem(item.id, authStore.isAuthenticated) }
</script>
