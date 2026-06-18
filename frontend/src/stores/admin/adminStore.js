import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { dashboardAPI } from '@/services/adminApi'

export const useAdminStore = defineStore('admin', () => {
  const stats       = ref(null)
  const loading     = ref(false)
  const sidebarOpen = ref(true)

  const hasLowStock = computed(() => stats.value?.products?.low_stock > 0)
  const pendingOrders = computed(() => stats.value?.orders?.pending ?? 0)

  async function fetchStats() {
    loading.value = true
    try {
      const { data } = await dashboardAPI.stats()
      stats.value = data
    } catch (e) {
      console.error('Failed to fetch admin stats', e)
    } finally {
      loading.value = false
    }
  }

  function toggleSidebar() { sidebarOpen.value = !sidebarOpen.value }

  return { stats, loading, sidebarOpen, hasLowStock, pendingOrders, fetchStats, toggleSidebar }
})
