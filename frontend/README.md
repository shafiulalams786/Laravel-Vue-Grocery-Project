# VueMart — Vue.js 3 Frontend

Vue 3 + Vite + Pinia SPA for the FreshBasket  e-commerce platform.

## Requirements
- Node.js 18+
- npm or pnpm
- FreshBasket  Laravel backend running

## Installation

```bash
# 1. Install dependencies
npm install

# 2. Copy environment file
cp .env.example .env

# 3. Configure .env
VITE_API_URL=http://localhost:8000
VITE_STRIPE_KEY=pk_test_your_stripe_publishable_key

# 4. Start development server
npm run dev
# App available at: http://localhost:5173

# Build for production
npm run build
```

## Project Structure

```
vue-frontend/
├── src/
│   ├── main.js                    # App entry + Axios setup
│   ├── App.vue
│   ├── router/
│   │   └── index.js               # Vue Router with auth guards
│   ├── stores/                    # Pinia state management
│   │   ├── auth.js                # Auth state (login/register/logout)
│   │   ├── cart.js                # Cart state
│   │   ├── products.js            # Products + filters
│   │   └── wishlist.js            # Wishlist state
│   ├── components/
│   │   ├── layout/
│   │   │   ├── MainLayout.vue     # Main app shell
│   │   │   ├── AppHeader.vue      # Sticky nav + search + cart icon
│   │   │   └── AppFooter.vue
│   │   └── product/
│   │       └── ProductCard.vue    # Reusable product card
│   └── pages/
│       ├── HomePage.vue           # Hero + categories + featured
│       ├── ProductsPage.vue       # Grid + filters + pagination
│       ├── ProductDetailPage.vue  # Detail + gallery + reviews
│       ├── SearchPage.vue         # Search results
│       ├── CategoryPage.vue       # Category product listing
│       ├── CartPage.vue           # Cart with totals
│       ├── CheckoutPage.vue       # Stripe payment form
│       ├── OrderSuccessPage.vue   # Post-checkout confirmation
│       ├── NotFoundPage.vue       # 404
│       ├── auth/
│       │   ├── LoginPage.vue
│       │   ├── RegisterPage.vue
│       │   └── ForgotPasswordPage.vue
│       └── account/
│           ├── AccountLayout.vue  # Sidebar layout
│           ├── ProfilePage.vue
│           ├── OrdersPage.vue
│           ├── OrderDetailPage.vue
│           ├── WishlistPage.vue
│           ├── AddressesPage.vue
│           └── SecurityPage.vue
```

## Pages & Routes

| Route                    | Page                   | Auth Required |
|--------------------------|------------------------|---------------|
| /                        | HomePage               | No            |
| /products                | ProductsPage           | No            |
| /products/:slug          | ProductDetailPage      | No            |
| /categories/:slug        | CategoryPage           | No            |
| /search?q=               | SearchPage             | No            |
| /cart                    | CartPage               | No            |
| /checkout                | CheckoutPage           | Yes           |
| /checkout/success        | OrderSuccessPage       | Yes           |
| /account                 | ProfilePage            | Yes           |
| /account/orders          | OrdersPage             | Yes           |
| /account/orders/:id      | OrderDetailPage        | Yes           |
| /account/wishlist        | WishlistPage           | Yes           |
| /account/addresses       | AddressesPage          | Yes           |
| /account/security        | SecurityPage           | Yes           |
| /login                   | LoginPage              | No (guest)    |
| /register                | RegisterPage           | No (guest)    |
| /forgot-password         | ForgotPasswordPage     | No            |

## Pinia Stores

### `useAuthStore`
- `user`, `token`, `isAuthenticated`, `isAdmin`
- `login(credentials)`, `register(payload)`, `logout()`
- `updateProfile(payload)`, `initAuth()`

### `useCartStore`
- `items`, `subtotal`, `tax`, `shipping`, `total`, `itemCount`
- `fetchCart()`, `addItem(id, qty)`, `updateItem(id, qty)`, `removeItem(id)`, `clearCart()`

### `useProductStore`
- `products`, `product`, `featured`, `categories`, `pagination`, `filters`
- `fetchProducts(params)`, `fetchFeatured()`, `fetchProduct(slug)`, `fetchCategories()`, `search(q)`

### `useWishlistStore`
- `items`, `isWishlisted(id)`
- `fetchWishlist()`, `toggle(productId)`

## Stripe Integration

Checkout uses `@stripe/stripe-js` to mount a card element and confirm payment.

Flow:
1. POST `/api/checkout/intent` → get `client_secret` + `order_id`
2. `stripe.confirmCardPayment(clientSecret, { payment_method: { card: cardElement } })`
3. On success → POST `/api/checkout/complete`
4. Redirect to `/checkout/success`
