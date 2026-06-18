import { reactive, computed } from 'vue'
import en from './en.js'
import bn from './bn.js'

const messages = { en, bn }

const state = reactive({
  locale: localStorage.getItem('locale') || 'en',
})

function setLocale(lang) {
  state.locale = lang
  localStorage.setItem('locale', lang)
  // Set html lang attribute
  document.documentElement.lang = lang
  // Set font class for Bangla
  if (lang === 'bn') {
    document.documentElement.classList.add('lang-bn')
  } else {
    document.documentElement.classList.remove('lang-bn')
  }
}

/**
 * Resolve a dot-path like 'cart.title' from the messages object
 */
function resolve(path, vars = {}) {
  const parts = path.split('.')
  let val = messages[state.locale]
  for (const p of parts) {
    if (val === undefined) break
    val = val[p]
  }
  // Fallback to English
  if (val === undefined) {
    val = messages['en']
    for (const p of parts) {
      if (val === undefined) break
      val = val[p]
    }
  }
  if (typeof val !== 'string') return path

  // Replace {n} placeholders
  return val.replace(/\{(\w+)\}/g, (_, key) =>
    vars[key] !== undefined ? vars[key] : `{${key}}`
  )
}

export function useI18n() {
  const locale = computed(() => state.locale)
  const t = (path, vars) => resolve(path, vars)
  return { t, locale, setLocale }
}

// Install as Vue plugin
export default {
  install(app) {
    app.config.globalProperties.$t = resolve
    app.config.globalProperties.$locale = computed(() => state.locale)
    app.provide('i18n', { t: resolve, locale: computed(() => state.locale), setLocale })
  },
}
