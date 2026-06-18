<template>
  <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">

      <!-- Sidebar -->
      <aside class="lg:w-52 flex-shrink-0">
        <div class="sticky top-24 space-y-6">
          <div>
            <p class="label mb-3">{{ t('shop.categories') }}</p>
            <div class="space-y-1">
              <button @click="cat = ''" :class="['w-full text-left px-3 py-2 rounded-xl text-sm font-medium transition-colors', !cat ? 'bg-brand-600 text-white shadow-sm' : 'text-ink-secondary hover:bg-surface-muted']">
                {{ t('nav.allProducts') }}
              </button>
              <button v-for="c in categories" :key="c.id" @click="cat = c.slug"
                :class="['w-full text-left px-3 py-2 rounded-xl text-sm font-medium transition-colors flex items-center justify-between', cat === c.slug ? 'bg-brand-600 text-white shadow-sm' : 'text-ink-secondary hover:bg-surface-muted']">
                <span>{{ c.name }}</span>
                <span :class="['text-xs', cat === c.slug ? 'text-brand-200' : 'text-ink-muted']">{{ c.products_count }}</span>
              </button>
            </div>
          </div>
          <div>
            <p class="label mb-3">{{ t('shop.sortBy') }}</p>
            <div class="space-y-1">
              <button v-for="s in sortOpts" :key="s.val" @click="sort = s.val"
                :class="['w-full text-left px-3 py-2 rounded-xl text-sm font-medium transition-colors', sort === s.val ? 'bg-brand-600 text-white shadow-sm' : 'text-ink-secondary hover:bg-surface-muted']">
                {{ s.label }}
              </button>
            </div>
          </div>
        </div>
      </aside>

      <!-- Grid -->
      <div class="flex-1">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="font-extrabold text-2xl text-ink">
              {{ cat ? categories.find(c => c.slug === cat)?.name : t('shop.title') }}
            </h1>
            <p class="text-xs text-ink-muted mt-0.5">{{ total }} {{ t('shop.products') }}</p>
          </div>
          <select v-model="sort" class="lg:hidden input py-2 w-auto text-sm">
            <option v-for="s in sortOpts" :key="s.val" :value="s.val">{{ s.label }}</option>
          </select>
        </div>

        <!-- Search indicator -->
        <div v-if="$route.query.search" class="mb-4 flex items-center gap-2.5 px-4 py-3 bg-brand-50 border border-brand-100 rounded-xl">
          <MagnifyingGlassIcon class="w-4 h-4 text-brand-600 flex-shrink-0" />
          <span class="text-sm text-brand-700">{{ t('shop.searchResults') }} "<strong>{{ $route.query.search }}</strong>"</span>
          <button @click="$router.push('/shop')" class="ml-auto text-ink-muted hover:text-ink-secondary"><XMarkIcon class="w-4 h-4" /></button>
        </div>

        <div v-if="loading" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
          <div v-for="i in 12" :key="i" class="h-72 skeleton" />
        </div>
        <div v-else-if="!products.length" class="flex flex-col items-center justify-center py-24 text-center">
          <div class="text-6xl mb-4">🔍</div>
          <h3 class="font-bold text-xl text-ink">{{ t('shop.noProducts') }}</h3>
          <p class="text-ink-muted mt-1 text-sm">{{ t('shop.noProductsHint') }}</p>
        </div>
        <div v-else class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
          <ProductCard v-for="p in products" :key="p.id" :product="p" />
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="flex items-center justify-center gap-3 mt-10">
          <button @click="page--" :disabled="page === 1" class="btn-secondary text-sm py-2 px-4 disabled:opacity-40">{{ t('shop.prev') }}</button>
          <span class="text-sm text-ink-secondary">{{ t('shop.page') }} {{ page }} {{ t('shop.of') }} {{ lastPage }}</span>
          <button @click="page++" :disabled="page === lastPage" class="btn-secondary text-sm py-2 px-4 disabled:opacity-40">{{ t('shop.next') }}</button>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { productAPI, categoryAPI } from '@/services/api'
import ProductCard from '@/components/product/ProductCard.vue'
import { useI18n } from '@/i18n/index.js'
import { MagnifyingGlassIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const { t } = useI18n()
const route = useRoute(); const router = useRouter()
const products = ref([]), categories = ref([])
const loading = ref(true), page = ref(1), lastPage = ref(1), total = ref(0)
const cat = ref(''), sort = ref('')

const sortOpts = computed(() => [
  { val: '',           label: t('shop.nameAZ') },
  { val: 'price_asc',  label: t('shop.priceLow') },
  { val: 'price_desc', label: t('shop.priceHigh') },
  { val: 'newest',     label: t('shop.newest') },
])

async function fetch() {
  loading.value = true
  try {
    const params = { page: page.value, sort: sort.value || undefined, category: cat.value || undefined }
    const endpoint = route.query.search ? productAPI.search(route.query.search) : productAPI.list(params)
    const { data } = await endpoint
    products.value = Array.isArray(data) ? data : data.data
    lastPage.value = data.last_page || 1
    total.value    = data.total || products.value.length
  } catch { products.value = [] }
  loading.value = false
}

watch([cat, sort, page], fetch)
watch(() => route.query.search, () => { page.value = 1; fetch() })
onMounted(async () => {
  const { data } = await categoryAPI.list()
  categories.value = data
  fetch()
})
</script>
