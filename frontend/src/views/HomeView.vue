<template>
  <main>
    <!-- Hero -->
    <section class="hero-bg overflow-hidden">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <div class="animate-fade-up">
            <span class="inline-flex items-center gap-2 bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full text-xs font-semibold mb-6">
              🚜 {{ t('home.heroTag') }}
            </span>
            <h1 class="text-5xl sm:text-6xl font-extrabold text-ink leading-tight mb-6 tracking-tight">
              {{ t('home.heroTitle') }}<br />
              <span class="gradient-text">{{ t('home.heroTitleHighlight') }}</span><br />
              {{ t('home.heroTitleSuffix') }}
            </h1>
            <p class="text-slate-500 text-lg mb-8 max-w-md leading-relaxed">{{ t('home.heroSubtitle') }}</p>

            <div class="flex flex-wrap gap-3 mb-10">
              <RouterLink to="/shop" class="btn-lg bg-brand-600 text-white hover:bg-brand-700 shadow-sm hover:shadow-md">
                {{ t('home.shopNow') }} <ArrowRightIcon class="w-5 h-5" />
              </RouterLink>
              <RouterLink to="/track-order" class="btn-lg bg-white text-ink border border-border shadow-sm hover:border-brand-300 hover:text-brand-700">
                <TruckIcon class="w-5 h-5" /> {{ t('home.trackMyOrder') }}
              </RouterLink>
            </div>

            <div class="flex flex-wrap gap-5">
              <div v-for="trust in trustBadges" :key="trust" class="flex items-center gap-1.5 text-sm text-slate-500">
                <span class="w-4 h-4 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center text-xs font-bold">✓</span>
                {{ trust }}
              </div>
            </div>
          </div>

          <!-- Hero cards -->
          <div class="relative hidden lg:grid grid-cols-2 gap-4 animate-fade-up" style="animation-delay:.1s">
            <div class="space-y-4">
              <div class="card p-5 text-center">
                <div class="text-5xl mb-3">🥑</div>
                <p class="font-semibold text-sm text-ink">Avocados</p>
                <p class="text-brand-600 font-bold text-sm">$1.49 each</p>
              </div>
              <div class="rounded-2xl p-5 text-center bg-brand-600 shadow-card">
                <div class="text-5xl mb-3">🍓</div>
                <p class="font-semibold text-sm text-white">Strawberries</p>
                <p class="text-brand-200 font-bold text-sm">$3.49 / pint</p>
              </div>
            </div>
            <div class="space-y-4 mt-8">
              <div class="rounded-2xl p-5 text-center bg-amber-500 shadow-card">
                <div class="text-5xl mb-3">🥛</div>
                <p class="font-semibold text-sm text-white">Whole Milk</p>
                <p class="text-amber-100 font-bold text-sm">$3.99 / gal</p>
              </div>
              <div class="card p-5 text-center">
                <div class="text-5xl mb-3">🥦</div>
                <p class="font-semibold text-sm text-ink">Broccoli</p>
                <p class="text-brand-600 font-bold text-sm">$2.49 each</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="section bg-white">
      <div class="container">
        <div class="flex items-end justify-between mb-8">
          <div>
            <h2 class="section-title">{{ t('home.categoriesTitle') }}</h2>
            <p class="section-sub">{{ t('home.categoriesSubtitle') }}</p>
          </div>
          <RouterLink to="/shop" class="text-sm font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
            {{ t('home.viewAll') }} <ArrowRightIcon class="w-4 h-4" />
          </RouterLink>
        </div>

        <div v-if="loadingCats" class="grid grid-cols-4 sm:grid-cols-8 gap-3">
          <div v-for="i in 8" :key="i" class="h-24 skeleton" />
        </div>
        <div v-else class="grid grid-cols-4 sm:grid-cols-8 gap-2">
          <RouterLink v-for="cat in categories" :key="cat.id" :to="`/category/${cat.slug}`"
            class="flex flex-col items-center gap-2 p-3 rounded-2xl hover:bg-brand-50 hover:border-brand-100 border border-transparent transition-all group">
            <div class="w-14 h-14 bg-surface-muted rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 group-hover:bg-brand-100 transition-all shadow-sm">
              {{ catEmoji(cat.slug) }}
            </div>
            <span class="text-xs font-medium text-ink-secondary text-center leading-tight">{{ cat.name }}</span>
          </RouterLink>
        </div>
      </div>
    </section>

    <!-- Featured products -->
    <section class="section bg-surface-soft">
      <div class="container">
        <div class="flex items-end justify-between mb-8">
          <div>
            <h2 class="section-title">{{ t('home.featuredTitle') }}</h2>
            <p class="section-sub">{{ t('home.featuredSubtitle') }}</p>
          </div>
          <RouterLink to="/shop" class="text-sm font-semibold text-brand-600 hover:text-brand-700 flex items-center gap-1">
            {{ t('home.seeAll') }} <ArrowRightIcon class="w-4 h-4" />
          </RouterLink>
        </div>

        <div v-if="loadingProducts" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="i in 8" :key="i" class="h-72 skeleton" />
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
          <ProductCard v-for="p in featured" :key="p.id" :product="p" />
        </div>
      </div>
    </section>

    <!-- Pay methods banner -->
    <section class="py-12 bg-slate-900">
      <div class="container">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
          <div>
            <h3 class="text-xl font-bold text-white">{{ t('home.payTitle') }}</h3>
            <p class="text-slate-400 text-sm mt-1">{{ t('home.paySubtitle') }}</p>
          </div>
          <div class="flex items-center gap-3 flex-wrap justify-center">
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur px-4 py-2.5 rounded-xl border border-white/10">
              <span class="text-blue-400 font-bold text-sm">STRIPE</span>
            </div>
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur px-4 py-2.5 rounded-xl border border-white/10">
              <span class="text-yellow-400 font-bold italic text-sm">PayPal</span>
            </div>
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur px-4 py-2.5 rounded-xl border border-white/10">
              <span class="text-brand-400 text-sm">💵</span>
              <span class="text-brand-400 font-semibold text-sm">{{ t('home.cashOnDelivery') }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="section bg-white">
      <div class="container">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="feat in features" :key="feat.emoji" class="text-center p-6 rounded-2xl hover:bg-brand-50 transition-colors">
            <div class="text-4xl mb-3">{{ feat.emoji }}</div>
            <h4 class="font-bold text-ink text-sm">{{ feat.title }}</h4>
            <p class="text-xs text-ink-muted mt-1">{{ feat.desc }}</p>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ArrowRightIcon, TruckIcon } from '@heroicons/vue/24/outline'
import ProductCard from '@/components/product/ProductCard.vue'
import { productAPI, categoryAPI } from '@/services/api'
import { useI18n } from '@/i18n/index.js'

const { t } = useI18n()
const featured = ref([]), categories = ref([])
const loadingProducts = ref(true), loadingCats = ref(true)

const catEmojis = { 'fresh-fruits':'🍎','vegetables':'🥦','dairy-eggs':'🥛','meat-fish':'🥩','bakery':'🍞','beverages':'🧃','snacks':'🍪','pantry':'🫙' }
const catEmoji = (slug) => catEmojis[slug] || '🛒'

const trustBadges = computed(() => [t('home.freeDelivery'), t('home.guestCheckout'), t('home.freshGuarantee')])
const features    = computed(() => [
  { emoji: '🌱', title: t('home.feat1Title'), desc: t('home.feat1Desc') },
  { emoji: '🚀', title: t('home.feat2Title'), desc: t('home.feat2Desc') },
  { emoji: '🔒', title: t('home.feat3Title'), desc: t('home.feat3Desc') },
  { emoji: '🛒', title: t('home.feat4Title'), desc: t('home.feat4Desc') },
])

onMounted(async () => {
  const [pRes, cRes] = await Promise.all([
    productAPI.featured().catch(() => ({ data: [] })),
    categoryAPI.list().catch(() => ({ data: [] })),
  ])
  featured.value   = pRes.data
  categories.value = cRes.data
  loadingProducts.value = false
  loadingCats.value     = false
})
</script>
