<template>
  <div class="flex h-screen bg-gray-950 overflow-hidden font-body">

    <!-- Sidebar -->
    <aside
      :class="[
        'flex flex-col bg-gray-900 border-r border-gray-800 transition-all duration-300 flex-shrink-0',
        adminStore.sidebarOpen ? 'w-60' : 'w-16'
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center gap-3 px-4 h-16 border-b border-gray-800 flex-shrink-0">
        <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center flex-shrink-0">
          <span class="text-sm">🌿</span>
        </div>
        <span v-if="adminStore.sidebarOpen" class="font-display font-bold text-white text-base whitespace-nowrap">
          Fresh<span class="text-brand-400">Admin</span>
        </span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
        <NavItem v-for="item in navItems" :key="item.to"
          :item="item"
          :collapsed="!adminStore.sidebarOpen"
        />
      </nav>

      <!-- Collapse toggle -->
      <button
        @click="adminStore.toggleSidebar"
        class="flex items-center justify-center h-12 border-t border-gray-800 text-gray-500 hover:text-white hover:bg-gray-800 transition-colors"
      >
        <ChevronLeftIcon v-if="adminStore.sidebarOpen" class="w-4 h-4" />
        <ChevronRightIcon v-else class="w-4 h-4" />
      </button>
    </aside>

    <!-- Main content -->
    <div class="flex-1 flex flex-col overflow-hidden">

      <!-- Top bar -->
      <header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6 flex-shrink-0">
        <div>
          <h1 class="text-white font-semibold text-base">{{ pageTitle }}</h1>
          <p class="text-gray-500 text-xs">{{ pageSubtitle }}</p>
        </div>

        <div class="flex items-center gap-3">
          <!-- Low stock alert -->
          <RouterLink v-if="adminStore.hasLowStock" to="/admin/products?stock=low"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-yellow-400 text-xs font-medium hover:bg-yellow-500/20 transition-colors">
            <ExclamationTriangleIcon class="w-3.5 h-3.5" />
            {{ adminStore.stats?.products?.low_stock }} low stock
          </RouterLink>

          <!-- Pending orders -->
          <RouterLink v-if="adminStore.pendingOrders > 0" to="/admin/orders?status=pending"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-orange-500/10 border border-orange-500/30 rounded-lg text-orange-400 text-xs font-medium hover:bg-orange-500/20 transition-colors">
            <ClockIcon class="w-3.5 h-3.5" />
            {{ adminStore.pendingOrders }} pending
          </RouterLink>

          <!-- Visit store -->
          <a href="/" target="_blank"
            class="flex items-center gap-1.5 px-3 py-1.5 bg-gray-800 rounded-lg text-gray-400 text-xs hover:text-white hover:bg-gray-700 transition-colors">
            <ArrowTopRightOnSquareIcon class="w-3.5 h-3.5" />
            View Store
          </a>

          <!-- Admin avatar -->
          <div class="flex items-center gap-2 pl-3 border-l border-gray-700">
            <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
              <span class="text-white text-xs font-bold">{{ adminInitial }}</span>
            </div>
            <div v-if="adminStore.sidebarOpen" class="hidden lg:block">
              <p class="text-white text-xs font-medium">{{ authStore.user?.name }}</p>
              <p class="text-gray-500 text-xs">Admin</p>
            </div>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto bg-gray-950 p-6">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAdminStore } from '@/stores/admin/adminStore'
import { useAuthStore } from '@/stores/auth'
import NavItem from '@/components/admin/ui/NavItem.vue'
import {
  ChevronLeftIcon, ChevronRightIcon, ExclamationTriangleIcon,
  ClockIcon, ArrowTopRightOnSquareIcon,
  Squares2X2Icon, ShoppingBagIcon, CubeIcon, TagIcon,
  UsersIcon, TicketIcon, ChartBarIcon, CogIcon,
} from '@heroicons/vue/24/outline'

const adminStore = useAdminStore()
const authStore  = useAuthStore()
const route      = useRoute()

const adminInitial = computed(() => authStore.user?.name?.[0]?.toUpperCase() ?? 'A')

const navItems = [
  { to: '/admin',           label: 'Dashboard',  icon: Squares2X2Icon,  exact: true },
  { to: '/admin/orders',    label: 'Orders',     icon: ShoppingBagIcon },
  { to: '/admin/products',  label: 'Products',   icon: CubeIcon },
  { to: '/admin/categories',label: 'Categories', icon: TagIcon },
  { to: '/admin/customers', label: 'Customers',  icon: UsersIcon },
  { to: '/admin/coupons',   label: 'Coupons',    icon: TicketIcon },
  { to: '/admin/reports',   label: 'Reports',    icon: ChartBarIcon },
  { to: '/admin/settings',  label: 'Settings',   icon: CogIcon },
]

const pageMeta = {
  '/admin':            { title: 'Dashboard',  sub: 'Store overview & analytics' },
  '/admin/orders':     { title: 'Orders',     sub: 'Manage and track orders' },
  '/admin/products':   { title: 'Products',   sub: 'Manage product catalog' },
  '/admin/categories': { title: 'Categories', sub: 'Manage product categories' },
  '/admin/customers':  { title: 'Customers',  sub: 'Customer accounts' },
  '/admin/coupons':    { title: 'Coupons',    sub: 'Discounts & promotions' },
  '/admin/reports':    { title: 'Reports',    sub: 'Sales & performance data' },
  '/admin/settings':   { title: 'Settings',   sub: 'Store configuration' },
}

const pageTitle    = computed(() => pageMeta[route.path]?.title ?? 'Admin')
const pageSubtitle = computed(() => pageMeta[route.path]?.sub   ?? '')

onMounted(() => adminStore.fetchStats())
</script>
