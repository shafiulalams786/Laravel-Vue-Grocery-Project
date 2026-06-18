# 🌿 FreshBasket - Full-Stack Grocery Store

A complete, production-ready online grocery store built with **Vue 3** (frontend) and **Laravel 11** (backend), featuring multiple payment methods and guest checkout.

## ✨ Features

### Shopping
- 🛒 Cart for both guests and registered users
- 🔍 Product search and category filtering
- 📦 Product detail pages with related items
- 🎯 Featured products & sale badges
- 🚚 Free delivery on orders over $50

### Payments (3 Methods)
- 💳 **Stripe** - Credit/debit card payments via Stripe Elements (fully embedded)
- 💛 **PayPal** - PayPal checkout with redirect flow
- 💵 **Cash on Delivery** - Pay when your order arrives

### Guest Checkout
- ✅ No account required to purchase
- 🔑 Session-based cart for guests
- 📧 Order confirmation by email
- 🔍 Order tracking by order number (no login needed)
- 🔄 Cart merges automatically when guest logs in

### User Accounts
- 👤 Registration & login with Sanctum tokens
- 📋 Order history & tracking
- 🏠 Profile management

---

## 🏗️ Tech Stack

| Layer       | Technology                        |
|-------------|-----------------------------------|
| Frontend    | Vue 3 + Composition API           |
| State       | Pinia                             |
| Router      | Vue Router 4                      |
| Styling     | Tailwind CSS + Custom Design      |
| HTTP        | Axios                             |
| Build       | Vite                              |
| Backend     | Laravel 11                        |
| Auth        | Laravel Sanctum                   |
| Payments    | Stripe PHP SDK + PayPal REST API  |
| Database    | MySQL                             |
| Fonts       | Playfair Display + DM Sans        |

---

## 🚀 Setup Guide

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+
- Stripe account (test keys)
- PayPal Developer account (sandbox)

---

### Backend Setup (Laravel)

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your .env file (database, stripe, paypal keys)
# See .env.example for all required variables
```

**Configure `.env`:**
```env
DB_DATABASE=grocery_store
DB_USERNAME=root
DB_PASSWORD=your_password

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_secret

FRONTEND_URL=http://localhost:5173
```

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE grocery_store;"

# Run migrations and seed data
php artisan migrate --seed

# Start the backend server
php artisan serve
```

The API will be running at `http://localhost:8000`

---

### Frontend Setup (Vue)

```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Configure .env
# VITE_STRIPE_KEY=pk_test_your_publishable_key
# VITE_API_URL=http://localhost:8000/api/v1

# Start development server
npm run dev
```

The frontend will be running at `http://localhost:5173`

---

## 📡 API Endpoints

### Public (No Auth)
```
GET  /api/v1/categories              - List all categories
GET  /api/v1/products                - List products (with filters)
GET  /api/v1/products/featured       - Featured products
GET  /api/v1/products/search?q=...   - Search products
GET  /api/v1/products/{slug}         - Product detail

POST /api/v1/guest/session           - Create guest session
GET  /api/v1/guest/cart/{sessionId}  - Get guest cart
POST /api/v1/guest/cart/{sessionId}  - Add to guest cart

POST /api/v1/checkout/guest          - Guest checkout
GET  /api/v1/orders/track/{number}   - Track any order

POST /api/v1/payment/stripe/intent   - Create Stripe PaymentIntent
POST /api/v1/payment/paypal/order    - Create PayPal order
POST /api/v1/payment/paypal/capture  - Capture PayPal payment
POST /api/v1/payment/stripe/webhook  - Stripe webhook handler
```

### Authenticated
```
POST /api/v1/auth/register           - Register
POST /api/v1/auth/login              - Login
POST /api/v1/auth/logout             - Logout
GET  /api/v1/auth/user               - Current user

GET  /api/v1/cart                    - Get cart
POST /api/v1/cart                    - Add to cart
PUT  /api/v1/cart/{id}               - Update quantity
DELETE /api/v1/cart/{id}             - Remove item
POST /api/v1/cart/merge              - Merge guest cart

POST /api/v1/checkout                - Authenticated checkout
GET  /api/v1/orders                  - Order history
GET  /api/v1/orders/{number}         - Order details
POST /api/v1/orders/{number}/cancel  - Cancel order
```

---

## 💳 Payment Integration Details

### Stripe
1. Frontend calls `POST /payment/stripe/intent` to get a `client_secret`
2. Stripe Elements (`#stripe-element`) collects card details securely
3. `stripe.confirmCardPayment()` processes the payment in browser
4. Stripe webhook at `/payment/stripe/webhook` marks order as paid

### PayPal
1. Frontend calls `POST /payment/paypal/order` to create a PayPal order
2. User is redirected to `approve_url` (PayPal sandbox/live)
3. On return, frontend calls `POST /payment/paypal/capture` with `paypal_order_id`
4. Order is marked as paid

### Cash on Delivery
1. Order is placed directly, `payment_status = pending`
2. Status updated to `paid` on successful delivery

---

## 🗂️ Project Structure

```
grocery-project/
├── backend/                     # Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── CartController.php      # Handles both auth + guest
│   │   │   ├── CategoryController.php
│   │   │   ├── GuestController.php     # Guest session init
│   │   │   ├── OrderController.php     # Both checkout modes
│   │   │   ├── PaymentController.php   # Stripe + PayPal
│   │   │   └── ProductController.php
│   │   └── Models/
│   ├── database/migrations/
│   ├── database/seeders/           # 30+ seeded products
│   └── routes/api.php
│
└── frontend/                    # Vue 3 App
    └── src/
        ├── views/
        │   ├── HomeView.vue         # Hero + featured products
        │   ├── ShopView.vue         # Product grid + filters
        │   ├── ProductView.vue      # Product detail
        │   ├── CheckoutView.vue     # Checkout + all payment methods
        │   ├── OrderSuccessView.vue # PayPal capture + confirmation
        │   ├── TrackOrderView.vue   # Order tracking (no login)
        │   ├── LoginView.vue
        │   ├── RegisterView.vue
        │   ├── AccountView.vue
        │   └── OrdersView.vue
        ├── components/
        │   ├── cart/CartDrawer.vue  # Slide-in cart
        │   ├── layout/AppNavbar.vue
        │   ├── layout/AppFooter.vue
        │   └── product/ProductCard.vue
        ├── stores/
        │   ├── auth.js              # Auth + login/logout
        │   └── cart.js              # Cart (guest + auth modes)
        └── services/api.js          # All API calls
```

---

## 🧪 Testing Payments

### Stripe Test Cards
| Card Number         | Result          |
|---------------------|-----------------|
| 4242 4242 4242 4242 | ✅ Success       |
| 4000 0000 0000 0002 | ❌ Declined      |
| 4000 0025 0000 3155 | 🔐 3D Secure     |

Use any future expiry date and any 3-digit CVV.

### PayPal Sandbox
Create sandbox accounts at [developer.paypal.com](https://developer.paypal.com)

---

## 🌍 Deployment

### Backend (Laravel)
```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend (Vue)
```bash
npm run build
# Deploy dist/ folder to CDN or web server
```

---

## 📝 License
MIT — Free to use, modify, and deploy.
