import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authAPI } from '@/services/api'
import { useCartStore } from './cart'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)

  async function fetchUser() {
    if (!token.value) return
    try {
      const { data } = await authAPI.user()
      user.value = data
    } catch {
      token.value = null
      localStorage.removeItem('auth_token')
    }
  }

  async function login(credentials) {
    loading.value = true
    try {
      const { data } = await authAPI.login(credentials)
      token.value = data.token
      user.value = data.user
      localStorage.setItem('auth_token', data.token)

      // Merge guest cart after login
      const cartStore = useCartStore()
      const guestSessionId = cartStore.guestSessionId
      if (guestSessionId) {
        await cartStore.mergeGuestCart()
      }
      await cartStore.fetchCart()

      return { success: true }
    } catch (error) {
      return { success: false, message: error.response?.data?.message || 'Login failed' }
    } finally {
      loading.value = false
    }
  }

  async function register(data) {
    loading.value = true
    try {
      const { data: res } = await authAPI.register(data)
      token.value = res.token
      user.value = res.user
      localStorage.setItem('auth_token', res.token)
      return { success: true }
    } catch (error) {
      const errors = error.response?.data?.errors
      return { success: false, message: error.response?.data?.message, errors }
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await authAPI.logout()
    } catch {}
    token.value = null
    user.value = null
    localStorage.removeItem('auth_token')

    const cartStore = useCartStore()
    cartStore.clearLocal()
  }

  return { user, token, loading, isAuthenticated, fetchUser, login, register, logout }
})
