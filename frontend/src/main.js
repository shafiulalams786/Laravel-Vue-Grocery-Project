import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Toast from 'vue-toastification'
import 'vue-toastification/dist/index.css'
import App from './App.vue'
import router from './router'
import i18nPlugin from './i18n/index.js'
import './assets/css/main.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18nPlugin)
app.use(Toast, {
  position: 'bottom-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  hideProgressBar: false,
  closeButton: 'button',
  icon: true,
})

// Set initial lang attribute
const savedLocale = localStorage.getItem('locale') || 'en'
document.documentElement.lang = savedLocale
if (savedLocale === 'bn') document.documentElement.classList.add('lang-bn')

app.mount('#app')
