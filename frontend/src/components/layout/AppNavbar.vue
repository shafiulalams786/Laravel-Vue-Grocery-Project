<template>
  <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-xl border-b border-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center gap-3 h-16">

        <!-- Logo -->
        <RouterLink to="/" class="flex items-center gap-2.5 flex-shrink-0 group">
          <div class="w-9 h-9 bg-brand-600 rounded-xl flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
            <span class="text-white text-base">🌿</span>
          </div>
          <span class="font-extrabold text-xl text-ink hidden sm:block">
            Fresh<span class="text-brand-600">Basket</span>
          </span>
        </RouterLink>

        <!-- Search -->
        <div class="flex-1 max-w-sm mx-4 hidden md:block">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-muted pointer-events-none" />
            <input
              v-model="q"
              @keyup.enter="doSearch"
              type="text"
              :placeholder="t('nav.searchPlaceholder')"
              class="w-full pl-9 pr-9 py-2.5 bg-surface-soft border border-border rounded-xl text-sm text-ink placeholder-ink-muted focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent focus:bg-white transition-all"
            />
            <button v-if="q" @click="q=''" class="absolute right-3 top-1/2 -translate-y-1/2 text-ink-muted hover:text-ink-secondary transition-colors">
              <XMarkIcon class="w-3.5 h-3.5" />
            </button>
          </div>
        </div>

        <!-- Right actions -->
        <div class="flex items-center gap-1 ml-auto">

          <!-- Language switcher -->
          <button
            @click="toggleLocale"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-ink-secondary hover:bg-surface-muted transition-colors"
            :title="locale === 'en' ? 'Switch to Bangla' : 'Switch to English'"
          >
            <LanguageIcon class="w-4 h-4" />
            <span class="hidden sm:block">{{ locale === 'en' ? 'বাংলা' : 'EN' }}</span>
          </button>

          <!-- Track order -->
          <RouterLink to="/track-order"
            class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-ink-secondary hover:bg-surface-muted hover:text-ink transition-colors">
            <TruckIcon class="w-4 h-4" />
            {{ t('nav.trackOrder') }}
          </RouterLink>

          <!-- Auth -->
          <template v-if="authStore.isAuthenticated">
            <div class="relative" ref="menuRef">
              <button @click="menu = !menu"
                class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-surface-muted transition-colors">
                <div class="w-7 h-7 bg-brand-600 rounded-lg flex items-center justify-center flex-shrink-0">
                  <span class="text-white text-xs font-bold">{{ authStore.user?.name?.[0]?.toUpperCase() }}</span>
                </div>
                <span class="hidden sm:block text-sm font-medium text-ink max-w-20 truncate">{{ authStore.user?.name?.split(' ')[0] }}</span>
                <ChevronDownIcon class="w-3 h-3 text-ink-muted" />
              </button>
              <Transition name="fade">
                <div v-if="menu" class="absolute right-0 mt-2 w-44 bg-white rounded-2xl shadow-lift border border-border py-1.5 z-50">
                  <RouterLink to="/account" @click="menu=false" class="nav-dd-item">
                    <UserIcon class="w-4 h-4" />{{ t('nav.myAccount') }}
                  </RouterLink>
                  <RouterLink to="/account/orders" @click="menu=false" class="nav-dd-item">
                    <ClipboardDocumentListIcon class="w-4 h-4" />{{ t('nav.myOrders') }}
                  </RouterLink>
                  <div class="border-t border-border my-1" />
                  <button @click="logout" class="nav-dd-item text-red-500 hover:bg-red-50 w-full">
                    <ArrowRightOnRectangleIcon class="w-4 h-4" />{{ t('nav.signOut') }}
                  </button>
                </div>
              </Transition>
            </div>
          </template>
          <template v-else>
            <RouterLink to="/login" class="hidden sm:block px-3 py-1.5 text-sm font-medium text-ink-secondary hover:text-ink transition-colors">
              {{ t('nav.signIn') }}
            </RouterLink>
            <RouterLink to="/register" class="btn-primary text-xs px-4 py-2">
              {{ t('nav.joinFree') }}
            </RouterLink>
          </template>

          <!-- Cart -->
          <button @click="cartStore.toggleCart"
            class="relative ml-1 p-2.5 bg-brand-600 text-white rounded-xl hover:bg-brand-700 transition-all hover:scale-105 active:scale-95 shadow-sm">
            <ShoppingBagIcon class="w-5 h-5" />
            <Transition name="pop">
              <span v-if="cartStore.itemCount > 0"
                class="absolute -top-1.5 -right-1.5 min-w-5 h-5 px-1 bg-amber-500 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-sm">
                {{ cartStore.itemCount > 99 ? '99+' : cartStore.itemCount }}
              </span>
            </Transition>
          </button>
        </div>
      </div>

      <!-- Category strip -->
      <nav class="hidden md:flex items-center gap-0.5 pb-2.5 overflow-x-auto scrollbar-hide">
        <RouterLink to="/shop"
          class="flex-shrink-0 px-3 py-1.5 text-xs font-medium text-ink-secondary rounded-lg hover:bg-surface-muted hover:text-ink transition-colors whitespace-nowrap">
          {{ t('nav.allProducts') }}
        </RouterLink>
        <RouterLink v-for="c in categories" :key="c.id" :to="`/category/${c.slug}`"
          class="flex-shrink-0 px-3 py-1.5 text-xs font-medium text-ink-secondary rounded-lg hover:bg-surface-muted hover:text-ink transition-colors whitespace-nowrap">
          {{ c.name }}
        </RouterLink>
      </nav>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { categoryAPI } from '@/services/api'
import { useI18n } from '@/i18n/index.js'
import {
  MagnifyingGlassIcon, XMarkIcon, ShoppingBagIcon, TruckIcon,
  UserIcon, ChevronDownIcon, ArrowRightOnRectangleIcon,
  ClipboardDocumentListIcon, LanguageIcon,
} from '@heroicons/vue/24/outline'

const { t, locale, setLocale } = useI18n()
const authStore  = useAuthStore()
const cartStore  = useCartStore()
const router     = useRouter()
const categories = ref([])
const q          = ref('')
const menu       = ref(false)
const menuRef    = ref(null)

function toggleLocale() { setLocale(locale.value === 'en' ? 'bn' : 'en') }
function doSearch() { if (q.value.trim()) { router.push({ path: '/shop', query: { search: q.value } }); q.value = '' } }
function handleOutside(e) { if (menuRef.value && !menuRef.value.contains(e.target)) menu.value = false }
async function logout() { await authStore.logout(); menu.value = false; router.push('/') }

onMounted(async () => {
  const { data } = await categoryAPI.list()
  categories.value = data
  document.addEventListener('click', handleOutside)
})
onUnmounted(() => document.removeEventListener('click', handleOutside))
</script>

<style scoped>
.nav-dd-item { @apply flex items-center gap-2.5 w-full px-4 py-2 text-sm text-ink-secondary hover:bg-surface-muted hover:text-ink transition-colors; }
</style>
