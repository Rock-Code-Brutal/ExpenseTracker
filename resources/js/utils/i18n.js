import { reactive } from 'vue'
import id from '../lang/id.js'
import en from '../lang/en.js'

const languages = {
  'IDR': id,
  'USD': en
}

export const i18n = reactive({
  currentLang: 'IDR',
  messages: languages['IDR']
})

export function setLanguage(currency) {
  const lang = currency === 'IDR' ? 'IDR' : 'USD'
  i18n.currentLang = lang
  i18n.messages = languages[lang]
}

export function t(key, ...args) {
  const message = i18n.messages[key]
  
  if (typeof message === 'function') {
    return message(...args)
  }
  
  return message || key
}
