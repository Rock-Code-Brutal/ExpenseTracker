<template>
    <div id="app" class="min-h-screen w-full bg-gray-50 dark:bg-gray-900 transition-colors">
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b dark:border-gray-700 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('app_title') }}</h1>
                    
                    <div class="flex items-center space-x-3">
                        <!-- Dark Mode Toggle -->
                        <button @click="toggleDarkMode" 
                                class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                :title="isDarkMode ? t('switch_to_light') : t('switch_to_dark')">
                            <!-- Moon icon for dark mode -->
                            <svg v-if="isDarkMode" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <!-- Sun icon for light mode -->
                            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        
                        <select v-model="currentCurrency" @change="changeCurrency"
                                class="border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
                            <option value="IDR">IDR (Rp)</option>
                            <option value="USD">USD ($)</option>
                        </select>
                    </div>
                </div>
            </div>
        </header>

        <!--Konten Utama-->
        <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <!--Tab Navigasi-->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'dashboard'"
                            :class="activeTab === 'dashboard' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ t('dashboard') }}
                    </button>
                    <button @click="activeTab = 'transactions'"
                            :class="activeTab === 'transactions' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ t('transactions') }}
                    </button>
                    <button @click="activeTab = 'categories'"
                            :class="activeTab === 'categories' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors">
                        {{ t('categories') }}
                    </button>
                </nav>
            </div>

            <!--Konten Tab-->
            <div v-if="activeTab === 'dashboard'">
                <Dashboard :currency="currentCurrency"/>
            </div>

            <div v-if="activeTab === 'transactions'">
                <Transactions :currency="currentCurrency"/>
            </div>

            <div v-if="activeTab === 'categories'">
                <Categories/>
            </div>
        </main>
    </div>
</template>


<script setup>
import {onMounted, ref} from "vue";
import Dashboard from "./components/Dashboard.vue";
import Transactions from "./components/Transaction.vue";
import Categories from "./components/Categories.vue";
import { t, setLanguage } from './utils/i18n.js';

const activeTab = ref('dashboard')
const currentCurrency = ref('IDR')
const isDarkMode = ref(false)

const changeCurrency = async () => {
    try {
        await fetch("/api/settings", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                default_currency: currentCurrency.value,
            })
        })
        // Update language based on currency
        setLanguage(currentCurrency.value)
    } catch (error) {
        console.error("Gagal untuk memperbarui Kurensi", error);
    }
}

const toggleDarkMode = () => {
    console.log('Toggle dark mode clicked, current:', isDarkMode.value)
    isDarkMode.value = !isDarkMode.value
    localStorage.setItem('darkMode', isDarkMode.value.toString())
    console.log('Dark mode toggled to:', isDarkMode.value)
    
    // Apply dark class to HTML element for proper Tailwind dark mode
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
    
    // Debug: Check if class is applied to DOM
    console.log('HTML element classes after toggle:', document.documentElement.className)
}

const loadSettings = async () => {
    try {
        const response = await fetch('/api/settings')
        const data = await response.json()
        if (data.success) {
            currentCurrency.value = data.data.default_currency
            // Set language based on loaded currency
            setLanguage(currentCurrency.value)
        }
    } catch (error) {
        console.error('Gagal untuk memperbarui Setting:', error)
    }
}

const loadTheme = () => {
    const savedTheme = localStorage.getItem('darkMode')
    console.log('Loading theme, saved theme:', savedTheme)
    
    if (savedTheme === 'true') {
        isDarkMode.value = true
    } else if (savedTheme === 'false') {
        isDarkMode.value = false
    } else {
        // Auto detect system preference for first time
        isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches
        localStorage.setItem('darkMode', isDarkMode.value.toString())
    }
    
    // Apply dark class to HTML element for proper Tailwind dark mode
    if (isDarkMode.value) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
    
    console.log('Theme loaded, isDarkMode:', isDarkMode.value)
    console.log('HTML element classes on load:', document.documentElement.className)
}
onMounted(() => {
    loadTheme()
    loadSettings()
})
</script>
