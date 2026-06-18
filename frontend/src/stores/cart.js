import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { cartAPI, guestCartAPI } from '@/services/api'
import { useToast } from 'vue-toastification'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  const summary = ref({ subtotal: 0, delivery_fee: 0, tax: 0, total: 0, free_delivery_threshold: 50, remaining_for_free_delivery: 50 })
  const loading = ref(false)
  const isOpen = ref(false)

  // Guest session management
  const guestSessionId = ref(localStorage.getItem('guest_session_id'))

  const itemCount = computed(() => items.value.reduce((acc, item) => acc + item.quantity, 0))
  const isEmpty = computed(() => items.value.length === 0)

  async function ensureGuestSession() {
    if (!guestSessionId.value) {
      const { data } = await guestCartAPI.initSession()
      guestSessionId.value = data.session_id
      localStorage.setItem('guest_session_id', data.session_id)
    }
    return guestSessionId.value
  }

  async function fetchCart(isAuth = false) {
    loading.value = true
    try {
      let data
      if (isAuth) {
        const res = await cartAPI.get()
        data = res.data
      } else {
        const sessionId = await ensureGuestSession()
        const res = await guestCartAPI.get(sessionId)
        data = res.data
      }
      items.value = data.items
      summary.value = data.summary
    } catch (error) {
      console.error('Failed to fetch cart:', error)
    } finally {
      loading.value = false
    }
  }

  async function addItem(productId, quantity = 1, isAuth = false) {
    const toast = useToast()
    try {
      if (isAuth) {
        await cartAPI.add({ product_id: productId, quantity })
      } else {
        const sessionId = await ensureGuestSession()
        await guestCartAPI.add(sessionId, { product_id: productId, quantity })
      }
      await fetchCart(isAuth)
      toast.success('Added to cart!')
      return true
    } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to add item')
      return false
    }
  }

  async function updateItem(itemId, quantity, isAuth = false) {
    try {
      if (isAuth) {
        await cartAPI.update(itemId, { quantity })
      } else {
        const sessionId = guestSessionId.value
        await guestCartAPI.update(sessionId, itemId, { quantity })
      }
      await fetchCart(isAuth)
    } catch (error) {
      console.error('Failed to update cart item:', error)
    }
  }

  async function removeItem(itemId, isAuth = false) {
    const toast = useToast()
    try {
      if (isAuth) {
        await cartAPI.remove(itemId)
      } else {
        const sessionId = guestSessionId.value
        await guestCartAPI.remove(sessionId, itemId)
      }
      await fetchCart(isAuth)
      toast.info('Item removed')
    } catch (error) {
      console.error('Failed to remove cart item:', error)
    }
  }

  async function clearCart(isAuth = false) {
    try {
      if (isAuth) {
        await cartAPI.clear()
      } else {
        const sessionId = guestSessionId.value
        await guestCartAPI.clear(sessionId)
      }
      items.value = []
      summary.value = { subtotal: 0, delivery_fee: 0, tax: 0, total: 0, free_delivery_threshold: 50, remaining_for_free_delivery: 50 }
    } catch (error) {
      console.error('Failed to clear cart:', error)
    }
  }

  async function mergeGuestCart() {
    if (!guestSessionId.value) return
    try {
      await cartAPI.merge(guestSessionId.value)
      localStorage.removeItem('guest_session_id')
      guestSessionId.value = null
    } catch (error) {
      console.error('Failed to merge cart:', error)
    }
  }

  function clearLocal() {
    items.value = []
    summary.value = { subtotal: 0, delivery_fee: 0, tax: 0, total: 0, free_delivery_threshold: 50, remaining_for_free_delivery: 50 }
  }

  function openCart() { isOpen.value = true }
  function closeCart() { isOpen.value = false }
  function toggleCart() { isOpen.value = !isOpen.value }

  return {
    items, summary, loading, isOpen, guestSessionId,
    itemCount, isEmpty,
    fetchCart, addItem, updateItem, removeItem, clearCart, mergeGuestCart,
    clearLocal, openCart, closeCart, toggleCart, ensureGuestSession
  }
})
