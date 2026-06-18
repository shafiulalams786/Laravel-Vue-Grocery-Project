<template>
  <div id="app-root">
    <template v-if="isAdmin">
      <router-view />
    </template>

    <template v-else>
      <div class="min-h-screen bg-slate-50">
        <AppNavbar />
        <CartDrawer />
        <router-view />
        <AppFooter />
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import AppNavbar  from '@/components/layout/AppNavbar.vue'
import AppFooter  from '@/components/layout/AppFooter.vue'
import CartDrawer from '@/components/cart/CartDrawer.vue'

const route     = useRoute()
const authStore = useAuthStore()
const cartStore = useCartStore()

const isAdmin = computed(() => route.path.startsWith('/admin'))

onMounted(() => {
  // Use setTimeout so async API calls don't run synchronously during
  // Vue's initial mount/hydration cycle, preventing unmount conflicts
  setTimeout(async () => {
    try {
      if (authStore.token) {
        await authStore.fetchUser()
        await cartStore.fetchCart(true)
      } else {
        await cartStore.fetchCart(false)
      }
    } catch {
      // Swallow errors — user just hasn't logged in yet
    }
  }, 0)
})
</script>
