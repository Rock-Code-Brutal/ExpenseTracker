import { reactive } from 'vue'
import en from '../lang/en.js'

export const i18n = reactive({ currentLang: 'en-US', messages: en })

export function t(key, ...args) {
  const message = i18n.messages[key]
  return typeof message === 'function' ? message(...args) : message || key
}
