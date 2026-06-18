import axios from 'axios'

const adminApi = axios.create({
  baseURL: import.meta.env.VITE_API_URL?.replace('/v1', '') || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: false, // Bearer token auth — no cookies, no CSRF
})

adminApi.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

adminApi.interceptors.response.use(
  (r) => r,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/admin/login'
    }
    if (error.response?.status === 403) {
      window.location.href = '/'
    }
    return Promise.reject(error)
  }
)

// ── Dashboard ──────────────────────────────────────────────
export const dashboardAPI = {
  stats:              ()       => adminApi.get('/admin/dashboard/stats'),
  revenueChart:       (period) => adminApi.get('/admin/dashboard/revenue-chart', { params: { period } }),
  ordersByStatus:     ()       => adminApi.get('/admin/dashboard/orders-by-status'),
  ordersByPayment:    ()       => adminApi.get('/admin/dashboard/orders-by-payment'),
  topProducts:        (limit)  => adminApi.get('/admin/dashboard/top-products', { params: { limit } }),
  lowStock:           ()       => adminApi.get('/admin/dashboard/low-stock'),
  recentOrders:       ()       => adminApi.get('/admin/dashboard/recent-orders'),
  categoryRevenue:    ()       => adminApi.get('/admin/dashboard/category-revenue'),
}

// ── Orders ─────────────────────────────────────────────────
export const adminOrderAPI = {
  list:          (params)          => adminApi.get('/admin/orders', { params }),
  show:          (id)              => adminApi.get(`/admin/orders/${id}`),
  updateStatus:  (id, status)      => adminApi.patch(`/admin/orders/${id}/status`, { status }),
  updatePayment: (id, status)      => adminApi.patch(`/admin/orders/${id}/payment-status`, { payment_status: status }),
  bulkStatus:    (ids, status)     => adminApi.post('/admin/orders/bulk-status', { order_ids: ids, status }),
  destroy:       (id)              => adminApi.delete(`/admin/orders/${id}`),
  exportUrl:     (params)          => {
    const q = new URLSearchParams(params).toString()
    return `${adminApi.defaults.baseURL}/admin/orders/export?${q}&token=${localStorage.getItem('auth_token')}`
  },
}

// ── Products ───────────────────────────────────────────────
export const adminProductAPI = {
  list:        (params) => adminApi.get('/admin/products', { params }),
  show:        (id)     => adminApi.get(`/admin/products/${id}`),
  store:       (data)   => adminApi.post('/admin/products', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  update:      (id, data) => adminApi.post(`/admin/products/${id}?_method=PUT`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  destroy:     (id)     => adminApi.delete(`/admin/products/${id}`),
  bulk:        (ids, action) => adminApi.post('/admin/products/bulk', { ids, action }),
  adjustStock: (id, stock)   => adminApi.patch(`/admin/products/${id}/stock`, { stock }),
}

// ── Categories ─────────────────────────────────────────────
export const adminCategoryAPI = {
  list:    ()           => adminApi.get('/admin/categories'),
  store:   (data)       => adminApi.post('/admin/categories', data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  update:  (id, data)   => adminApi.post(`/admin/categories/${id}?_method=PUT`, data, { headers: { 'Content-Type': 'multipart/form-data' } }),
  destroy: (id)         => adminApi.delete(`/admin/categories/${id}`),
  reorder: (order)      => adminApi.post('/admin/categories/reorder', { order }),
}

// ── Customers ──────────────────────────────────────────────
export const adminCustomerAPI = {
  list:      (params) => adminApi.get('/admin/customers', { params }),
  show:      (id)     => adminApi.get(`/admin/customers/${id}`),
  toggleBan: (id)     => adminApi.patch(`/admin/customers/${id}/ban`),
  exportUrl: ()       => `${adminApi.defaults.baseURL}/admin/customers/export?token=${localStorage.getItem('auth_token')}`,
}

// ── Coupons ────────────────────────────────────────────────
export const adminCouponAPI = {
  list:         ()       => adminApi.get('/admin/coupons'),
  store:        (data)   => adminApi.post('/admin/coupons', data),
  update:       (id, d)  => adminApi.put(`/admin/coupons/${id}`, d),
  destroy:      (id)     => adminApi.delete(`/admin/coupons/${id}`),
  generateCode: ()       => adminApi.get('/admin/coupons/generate-code'),
}

// ── Reports ────────────────────────────────────────────────
export const adminReportAPI = {
  sales:     (params) => adminApi.get('/admin/reports/sales', { params }),
  products:  (params) => adminApi.get('/admin/reports/products', { params }),
  customers: (params) => adminApi.get('/admin/reports/customers', { params }),
  exportUrl: (params) => {
    const q = new URLSearchParams(params).toString()
    return `${adminApi.defaults.baseURL}/admin/reports/sales/export?${q}&token=${localStorage.getItem('auth_token')}`
  },
}

// ── Settings ───────────────────────────────────────────────
export const adminSettingsAPI = {
  get:    ()     => adminApi.get('/admin/settings'),
  update: (data) => adminApi.put('/admin/settings', data),
}

export default adminApi
