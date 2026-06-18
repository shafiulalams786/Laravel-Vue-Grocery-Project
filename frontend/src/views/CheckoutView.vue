<template>
  <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="font-extrabold text-3xl text-ink mb-8 tracking-tight">{{ t('checkout.title') }}</h1>

    <div v-if="cartStore.isEmpty" class="text-center py-20">
      <div class="w-20 h-20 bg-surface-muted rounded-2xl flex items-center justify-center text-4xl mx-auto mb-4">🛒</div>
      <h2 class="font-bold text-xl text-ink">{{ t('checkout.emptyCart') }}</h2>
      <RouterLink to="/shop" class="btn-primary mt-4 inline-flex">{{ t('home.shopNow') }}</RouterLink>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Left column -->
      <div class="lg:col-span-2 space-y-5">

        <!-- Guest / Login toggle -->
        <div v-if="!authStore.isAuthenticated" class="card p-6">
          <h2 class="font-bold text-lg text-ink mb-4">{{ t('checkout.howCheckout') }}</h2>
          <div class="grid grid-cols-2 gap-3">
            <button @click="mode = 'guest'"
              :class="['p-4 rounded-2xl border-2 text-left transition-all', mode === 'guest' ? 'border-brand-500 bg-brand-50' : 'border-border hover:border-brand-200']">
              <div class="text-2xl mb-1.5">🚀</div>
              <p class="font-bold text-sm text-ink">{{ t('checkout.guestCheckout') }}</p>
              <p class="text-xs text-ink-muted mt-0.5">{{ t('checkout.guestHint') }}</p>
            </button>
            <RouterLink to="/login?redirect=/checkout"
              class="p-4 rounded-2xl border-2 border-border hover:border-brand-200 text-left transition-all block">
              <div class="text-2xl mb-1.5">👤</div>
              <p class="font-bold text-sm text-ink">{{ t('checkout.signIn') }}</p>
              <p class="text-xs text-ink-muted mt-0.5">{{ t('checkout.signInHint') }}</p>
            </RouterLink>
          </div>
        </div>

        <!-- Contact (guest only) -->
        <div v-if="!authStore.isAuthenticated && mode === 'guest'" class="card p-6">
          <h2 class="font-bold text-lg text-ink mb-5">{{ t('checkout.contactInfo') }}</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="label">{{ t('checkout.fullName') }} *</label>
              <input v-model="form.guest_name" class="input" :class="{'input-error': errs.guest_name}" placeholder="John Doe" />
              <p v-if="errs.guest_name" class="text-red-500 text-xs mt-1">{{ errs.guest_name }}</p>
            </div>
            <div>
              <label class="label">{{ t('checkout.phone') }} *</label>
              <input v-model="form.guest_phone" class="input" :class="{'input-error': errs.guest_phone}" placeholder="+1 555 000 0000" />
              <p v-if="errs.guest_phone" class="text-red-500 text-xs mt-1">{{ errs.guest_phone }}</p>
            </div>
            <div class="sm:col-span-2">
              <label class="label">{{ t('checkout.email') }} *</label>
              <input v-model="form.guest_email" type="email" class="input" :class="{'input-error': errs.guest_email}" placeholder="your@email.com" />
              <p v-if="errs.guest_email" class="text-red-500 text-xs mt-1">{{ errs.guest_email }}</p>
            </div>
          </div>
        </div>

        <!-- Delivery Address -->
        <div class="card p-6">
          <h2 class="font-bold text-lg text-ink mb-5">{{ t('checkout.deliveryAddress') }}</h2>
          <div class="space-y-4">
            <div>
              <label class="label">{{ t('checkout.street') }} *</label>
              <input v-model="form.delivery_address.street" class="input" :class="{'input-error': errs['delivery_address.street']}" :placeholder="t('checkout.street')" />
              <p v-if="errs['delivery_address.street']" class="text-red-500 text-xs mt-1">{{ errs['delivery_address.street'] }}</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
              <div class="sm:col-span-1">
                <label class="label">{{ t('checkout.city') }} *</label>
                <input v-model="form.delivery_address.city" class="input" placeholder="New York" />
              </div>
              <div>
                <label class="label">{{ t('checkout.state') }} *</label>
                <input v-model="form.delivery_address.state" class="input" placeholder="NY" />
              </div>
              <div>
                <label class="label">{{ t('checkout.zip') }} *</label>
                <input v-model="form.delivery_address.zip" class="input" placeholder="10001" />
              </div>
            </div>
            <div>
              <label class="label">{{ t('checkout.notes') }}</label>
              <textarea v-model="form.notes" rows="2" class="input resize-none" :placeholder="t('checkout.notesPlaceholder')" />
            </div>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="card p-6">
          <h2 class="font-bold text-lg text-ink mb-5">{{ t('checkout.paymentMethod') }}</h2>
          <div class="space-y-3 mb-5">

            <!-- Stripe -->
            <label :class="['flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all',
              form.payment_method === 'stripe' ? 'border-blue-500 bg-blue-50' : 'border-border hover:border-blue-200']">
              <input v-model="form.payment_method" type="radio" value="stripe" class="sr-only" />
              <div :class="['w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all',
                form.payment_method === 'stripe' ? 'border-blue-500' : 'border-border']">
                <div v-if="form.payment_method === 'stripe'" class="w-2.5 h-2.5 rounded-full bg-blue-500" />
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-semibold text-sm text-ink">{{ t('checkout.creditCard') }}</span>
                  <span class="badge badge-blue text-xs">Stripe</span>
                </div>
                <p class="text-xs text-ink-muted mt-0.5">{{ t('checkout.stripeHint') }}</p>
              </div>
              <div class="hidden sm:flex gap-1">
                <div class="px-2 py-1 bg-blue-600 rounded text-white text-xs font-bold">VISA</div>
                <div class="px-2 py-1 bg-red-500 rounded text-white text-xs font-bold">MC</div>
              </div>
            </label>

            <!-- PayPal -->
            <label :class="['flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all',
              form.payment_method === 'paypal' ? 'border-amber-500 bg-amber-50' : 'border-border hover:border-amber-200']">
              <input v-model="form.payment_method" type="radio" value="paypal" class="sr-only" />
              <div :class="['w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                form.payment_method === 'paypal' ? 'border-amber-500' : 'border-border']">
                <div v-if="form.payment_method === 'paypal'" class="w-2.5 h-2.5 rounded-full bg-amber-500" />
              </div>
              <div class="flex-1">
                <span class="font-semibold text-sm text-ink">PayPal</span>
                <p class="text-xs text-ink-muted mt-0.5">{{ t('checkout.paypalHint') }}</p>
              </div>
              <span class="text-blue-800 font-extrabold text-sm italic">Pay<span class="text-blue-500">Pal</span></span>
            </label>

            <!-- COD -->
            <label :class="['flex items-center gap-4 p-4 rounded-2xl border-2 cursor-pointer transition-all',
              form.payment_method === 'cod' ? 'border-brand-500 bg-brand-50' : 'border-border hover:border-brand-200']">
              <input v-model="form.payment_method" type="radio" value="cod" class="sr-only" />
              <div :class="['w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                form.payment_method === 'cod' ? 'border-brand-500' : 'border-border']">
                <div v-if="form.payment_method === 'cod'" class="w-2.5 h-2.5 rounded-full bg-brand-500" />
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-semibold text-sm text-ink">{{ t('checkout.codLabel') }}</span>
                  <span class="badge badge-green text-xs">{{ t('checkout.codBadge') }}</span>
                </div>
                <p class="text-xs text-ink-muted mt-0.5">{{ t('checkout.codHint') }}</p>
              </div>
              <span class="text-2xl">💵</span>
            </label>
          </div>

          <!-- Stripe element -->
          <div v-if="form.payment_method === 'stripe'" class="mt-3">
            <div ref="stripeElementRef" class="p-4 border-2 border-border rounded-xl bg-surface-soft min-h-12 transition-all focus-within:border-brand-400">
              <div v-if="!stripeReady" class="flex items-center gap-2 text-sm text-ink-muted">
                <div class="w-4 h-4 border-2 border-border border-t-brand-500 rounded-full animate-spin" />
                {{ t('checkout.loadingCard') }}
              </div>
            </div>
            <p class="text-xs text-ink-muted mt-2 flex items-center gap-1.5">
              <LockClosedIcon class="w-3.5 h-3.5" /> {{ t('checkout.securedByStripe') }}
            </p>
          </div>

          <!-- PayPal info -->
          <div v-if="form.payment_method === 'paypal'" class="mt-3 p-3.5 bg-amber-50 border border-amber-100 rounded-xl">
            <p class="text-xs text-amber-700">{{ t('checkout.paypalRedirectHint') }}</p>
          </div>

          <!-- COD info -->
          <div v-if="form.payment_method === 'cod'" class="mt-3 p-3.5 bg-brand-50 border border-brand-100 rounded-xl">
            <p class="text-xs text-brand-700">
              {{ t('checkout.codAmountHint') }}
              <strong class="font-bold"> ${{ cartStore.summary.total?.toFixed(2) }}</strong>
            </p>
          </div>
        </div>
      </div>

      <!-- Order Summary sidebar -->
      <div class="lg:col-span-1">
        <div class="card p-5 sticky top-24">
          <h2 class="font-bold text-lg text-ink mb-4">{{ t('checkout.orderSummary') }}</h2>

          <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-1">
            <div v-for="item in cartStore.items" :key="item.id" class="flex items-center gap-3">
              <div class="w-10 h-10 bg-surface-muted rounded-xl flex items-center justify-center text-lg flex-shrink-0">
                {{ catEmoji(item.product?.category?.slug) }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-ink truncate">{{ item.product?.name }}</p>
                <p class="text-xs text-ink-muted">{{ item.quantity }} × ${{ item.price }}</p>
              </div>
              <span class="text-sm font-bold text-ink">${{ (item.price * item.quantity).toFixed(2) }}</span>
            </div>
          </div>

          <div class="border-t border-border pt-4 space-y-2 text-sm">
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
            <div class="flex justify-between font-extrabold text-base text-ink pt-2 border-t border-border">
              <span>{{ t('cart.total') }}</span><span>${{ cartStore.summary.total?.toFixed(2) }}</span>
            </div>
          </div>

          <button @click="placeOrder" :disabled="processing || !form.payment_method"
            class="mt-5 w-full flex items-center justify-center gap-2 py-4 bg-brand-600 text-white font-extrabold rounded-2xl hover:bg-brand-700 disabled:opacity-50 transition-all active:scale-95 shadow-sm text-sm">
            <span v-if="processing" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin" />
            <span>{{ form.payment_method === 'paypal' ? t('checkout.continuePaypal') : `${t('checkout.placeOrder')} · $${cartStore.summary.total?.toFixed(2)}` }}</span>
          </button>

          <p class="text-xs text-ink-muted text-center mt-3 flex items-center justify-center gap-1.5">
            <LockClosedIcon class="w-3.5 h-3.5" /> {{ t('checkout.secureCheckout') }}
          </p>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { orderAPI, paymentAPI } from '@/services/api'
import { useI18n } from '@/i18n/index.js'
import { useToast } from 'vue-toastification'
import { LockClosedIcon } from '@heroicons/vue/24/outline'
import { loadStripe } from '@stripe/stripe-js'

const { t }          = useI18n()
const router         = useRouter()
const cartStore      = useCartStore()
const authStore      = useAuthStore()
const toast          = useToast()
const mode           = ref('guest')
const processing     = ref(false)
const stripeReady    = ref(false)
const stripeElementRef = ref(null)  // Vue template ref — not getElementById

let isMounted = false
let stripe = null, elements = null, cardEl = null

const errs = ref({})
const form = ref({
  guest_name: '', guest_email: '', guest_phone: '',
  delivery_address: { street: '', city: '', state: '', zip: '' },
  payment_method: 'stripe',
  notes: '',
})

const catEmojis = { 'fresh-fruits':'🍎','vegetables':'🥦','dairy-eggs':'🥛','meat-fish':'🥩','bakery':'🍞','beverages':'🧃','snacks':'🍪','pantry':'🫙' }
const catEmoji  = (s) => catEmojis[s] || '🛒'

async function initStripe() {
  const key = import.meta.env.VITE_STRIPE_KEY
  if (!key || !isMounted) return

  try {
    stripe = await loadStripe(key)

    // Guard: component may have unmounted during async loadStripe()
    if (!isMounted) return

    elements = stripe.elements()
    cardEl   = elements.create('card', {
      style: {
        base: {
          fontFamily: 'Inter, sans-serif',
          fontSize: '15px',
          color: '#0f172a',
          '::placeholder': { color: '#94a3b8' },
        },
      },
    })

    // Wait one tick for Vue to render the ref'd div
    await nextTick()

    // Guard again after await — navigation may have happened
    if (!isMounted) return

    // Use Vue template ref, NOT getElementById
    // getElementById can find stale detached DOM nodes from previous renders
    const el = stripeElementRef.value
    if (el && el.isConnected) {
      cardEl.mount(el)
      stripeReady.value = true
    }
  } catch (err) {
    if (isMounted) console.warn('Stripe init error:', err)
  }
}

function destroyStripe() {
  if (cardEl) {
    try { cardEl.unmount() } catch {}
    try { cardEl.destroy() } catch {}
    cardEl = null
  }
  stripe   = null
  elements = null
  stripeReady.value = false
}

watch(() => form.value.payment_method, async (m, old) => {
  if (old === 'stripe') destroyStripe()
  if (m === 'stripe' && !cardEl) {
    await nextTick()
    initStripe()
  }
})

onMounted(() => {
  isMounted = true
  if (form.value.payment_method === 'stripe') initStripe()
})

onUnmounted(() => {
  isMounted = false
  destroyStripe()
})

function validate() {
  errs.value = {}
  const f = form.value
  if (!authStore.isAuthenticated) {
    if (!f.guest_name)  errs.value.guest_name  = t('checkout.fullName') + ' required'
    if (!f.guest_email) errs.value.guest_email = t('checkout.email') + ' required'
    if (!f.guest_phone) errs.value.guest_phone = t('checkout.phone') + ' required'
  }
  if (!f.delivery_address.street) errs.value['delivery_address.street'] = t('checkout.street') + ' required'
  if (!f.delivery_address.city)   errs.value['delivery_address.city']   = t('checkout.city') + ' required'
  if (!f.delivery_address.state)  errs.value['delivery_address.state']  = t('checkout.state') + ' required'
  if (!f.delivery_address.zip)    errs.value['delivery_address.zip']    = t('checkout.zip') + ' required'
  return !Object.keys(errs.value).length
}

async function placeOrder() {
  if (!validate()) { toast.error(t('checkout.fillRequired')); return }
  processing.value = true
  try {
    const isGuest = !authStore.isAuthenticated
    const payload = {
      delivery_address: form.value.delivery_address,
      payment_method:   form.value.payment_method,
      notes:            form.value.notes,
      ...(isGuest && { session_id: cartStore.guestSessionId, guest_name: form.value.guest_name, guest_email: form.value.guest_email, guest_phone: form.value.guest_phone }),
    }
    if (form.value.payment_method === 'cod')    await handleCOD(payload, isGuest)
    else if (form.value.payment_method === 'stripe')  await handleStripe(payload, isGuest)
    else if (form.value.payment_method === 'paypal')  await handlePayPal(payload, isGuest)
  } catch (e) { toast.error(e.response?.data?.message || t('common.error')) }
  processing.value = false
}

async function handleCOD(payload, isGuest) {
  const { data } = isGuest ? await orderAPI.guestCheckout(payload) : await orderAPI.checkout(payload)
  router.push({ name: 'OrderSuccess', query: { order: data.order_number, method: 'cod' } })
}

async function handleStripe(payload, isGuest) {
  // Double-check Stripe is ready and the card element is still in the DOM
  if (!stripe || !cardEl) { toast.error('Stripe not ready'); return }
  if (!stripeElementRef.value?.isConnected) { toast.error('Card form not ready, please wait'); return }

  const { data: order } = isGuest
    ? await orderAPI.guestCheckout(payload)
    : await orderAPI.checkout(payload)

  const { data: intent } = await paymentAPI.createStripeIntent({
    amount:       cartStore.summary.total,
    order_number: order.order_number,
  })

  // Confirm payment — if component unmounted mid-await, cardEl may be destroyed
  try {
    const { error, paymentIntent } = await stripe.confirmCardPayment(
      intent.client_secret,
      { payment_method: { card: cardEl } }
    )
    if (error) { toast.error(error.message || 'Payment failed'); return }
    if (paymentIntent.status === 'succeeded') {
      router.push({ name: 'OrderSuccess', query: { order: order.order_number, method: 'stripe' } })
    }
  } catch (err) {
    toast.error('Payment error: ' + (err.message || 'Please try again'))
  }
}

async function handlePayPal(payload, isGuest) {
  const { data: order }  = isGuest ? await orderAPI.guestCheckout(payload) : await orderAPI.checkout(payload)
  const { data: pp }     = await paymentAPI.createPaypalOrder({ amount: cartStore.summary.total, order_number: order.order_number })
  localStorage.setItem('pending_paypal_order', order.order_number)
  localStorage.setItem('pending_paypal_id',    pp.paypal_order_id)
  window.location.href = pp.approve_url
}
</script>
