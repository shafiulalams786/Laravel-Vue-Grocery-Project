import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  // ── IMPORTANT: false = use Bearer token auth, not cookies ──
  // Setting withCredentials: true would trigger CSRF checks.
  // We use localStorage tokens + Authorization header instead.
  withCredentials: false,
})

// ── Request interceptor: attach Bearer token ─────────────────
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
}, (error) => Promise.reject(error))

// ── Response interceptor: handle auth errors ──────────────────
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      // Only redirect if not already on a public page
      const publicPaths = ['/', '/shop', '/login', '/register', '/track-order']
      const isPublic = publicPaths.some(p => window.location.pathname.startsWith(p))
      if (!isPublic) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api

// ── Auth ──────────────────────────────────────────────────────
export const authAPI = {
  login:         (data) => api.post('/auth/login', data),
  register:      (data) => api.post('/auth/register', data),
  logout:        ()     => api.post('/auth/logout'),
  user:          ()     => api.get('/auth/user'),
  updateProfile: (data) => api.put('/auth/profile', data),
}

// ── Products ──────────────────────────────────────────────────
export const productAPI = {
  list:     (params) => api.get('/products', { params }),
  featured: ()       => api.get('/products/featured'),
  search:   (q)      => api.get('/products/search', { params: { q } }),
  show:     (slug)   => api.get(`/products/${slug}`),
}

// ── Categories ────────────────────────────────────────────────
export const categoryAPI = {
  list: ()     => api.get('/categories'),
  show: (slug) => api.get(`/categories/${slug}`),
}

// ── Authenticated cart ─────────────────────────────────────────
export const cartAPI = {
  get:    ()              => api.get('/cart'),
  add:    (data)          => api.post('/cart', data),
  update: (itemId, data)  => api.put(`/cart/${itemId}`, data),
  remove: (itemId)        => api.delete(`/cart/${itemId}`),
  clear:  ()              => api.delete('/cart'),
  merge:  (sessionId)     => api.post('/cart/merge', { session_id: sessionId }),
}

// ── Guest cart ─────────────────────────────────────────────────
export const guestCartAPI = {
  initSession: ()                       => api.post('/guest/session'),
  get:         (sid)                    => api.get(`/guest/cart/${sid}`),
  add:         (sid, data)              => api.post(`/guest/cart/${sid}`, data),
  update:      (sid, itemId, data)      => api.put(`/guest/cart/${sid}/${itemId}`, data),
  remove:      (sid, itemId)            => api.delete(`/guest/cart/${sid}/${itemId}`),
  clear:       (sid)                    => api.delete(`/guest/cart/${sid}`),
}

// ── Orders ─────────────────────────────────────────────────────
export const orderAPI = {
  list:          ()            => api.get('/orders'),
  show:          (num)         => api.get(`/orders/${num}`),
  checkout:      (data)        => api.post('/checkout', data),
  guestCheckout: (data)        => api.post('/checkout/guest', data),
  track:         (num)         => api.get(`/orders/track/${num}`),
  cancel:        (num)         => api.post(`/orders/${num}/cancel`),
}

// ── Payments ───────────────────────────────────────────────────
export const paymentAPI = {
  createStripeIntent: (data)  => api.post('/payment/stripe/intent', data),
  createPaypalOrder:  (data)  => api.post('/payment/paypal/order', data),
  capturePaypalOrder: (data)  => api.post('/payment/paypal/capture', data),
}
