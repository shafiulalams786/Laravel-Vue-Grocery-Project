import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import AdminLayout from '@/layouts/AdminLayout.vue'

const routes = [
  // ── Storefront ─────────────────────────────────────────────
  { path: '/',               name: 'Home',          component: () => import('@/views/HomeView.vue') },
  { path: '/shop',           name: 'Shop',          component: () => import('@/views/ShopView.vue') },
  { path: '/category/:slug', name: 'Category',      component: () => import('@/views/CategoryView.vue') },
  { path: '/product/:slug',  name: 'Product',       component: () => import('@/views/ProductView.vue') },
  { path: '/checkout',       name: 'Checkout',      component: () => import('@/views/CheckoutView.vue') },
  { path: '/checkout/success', name: 'OrderSuccess', component: () => import('@/views/OrderSuccessView.vue') },
  { path: '/checkout/cancel',  name: 'OrderCancel',  component: () => import('@/views/OrderCancelView.vue') },
  { path: '/track-order',    name: 'TrackOrder',    component: () => import('@/views/TrackOrderView.vue') },
  { path: '/login',          name: 'Login',         component: () => import('@/views/LoginView.vue'),    meta: { guestOnly: true } },
  { path: '/register',       name: 'Register',      component: () => import('@/views/RegisterView.vue'), meta: { guestOnly: true } },
  { path: '/account',        name: 'Account',       component: () => import('@/views/AccountView.vue'),  meta: { requiresAuth: true } },
  { path: '/account/orders', name: 'Orders',        component: () => import('@/views/OrdersView.vue'),   meta: { requiresAuth: true } },

  // ── Admin ──────────────────────────────────────────────────
  { path: '/admin/login', name: 'AdminLogin', component: () => import('@/views/admin/AdminLoginView.vue'), meta: { adminGuestOnly: true } },

  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAdmin: true },
    children: [
      { path: '',            name: 'AdminDashboard',  component: () => import('@/views/admin/DashboardView.vue') },
      { path: 'orders',      name: 'AdminOrders',     component: () => import('@/views/admin/OrdersView.vue') },
      { path: 'orders/:id',  name: 'AdminOrderDetail',component: () => import('@/views/admin/OrderDetailView.vue') },
      { path: 'products',    name: 'AdminProducts',   component: () => import('@/views/admin/ProductsView.vue') },
      { path: 'categories',  name: 'AdminCategories', component: () => import('@/views/admin/CategoriesView.vue') },
      { path: 'customers',   name: 'AdminCustomers',  component: () => import('@/views/admin/CustomersView.vue') },
      { path: 'coupons',     name: 'AdminCoupons',    component: () => import('@/views/admin/CouponsView.vue') },
      { path: 'reports',     name: 'AdminReports',    component: () => import('@/views/admin/ReportsView.vue') },
      { path: 'settings',    name: 'AdminSettings',   component: () => import('@/views/admin/SettingsView.vue') },
    ],
  },

  // ── 404 ────────────────────────────────────────────────────
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() { return { top: 0 } },
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Restore user from token if not loaded yet
  if (!auth.user && auth.token) await auth.fetchUser()

  // Admin-guest-only (admin login page)
  if (to.meta.adminGuestOnly && auth.isAuthenticated && auth.user?.is_admin) {
    return { name: 'AdminDashboard' }
  }

  // Requires admin
  if (to.meta.requiresAdmin) {
    if (!auth.isAuthenticated) return { name: 'AdminLogin' }
    if (!auth.user?.is_admin)  return { name: 'Home' }
  }

  // Requires auth (storefront)
  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return { name: 'Login', query: { redirect: to.fullPath } }
  }

  // Guest-only (login / register)
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return { name: 'Home' }
  }
})

export default router
