<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('categories') }}</h2>
            <button @click="showAddForm = true"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                {{ t('add_category') }}
            </button>
        </div>

        <!-- Form Kategori -->
        <div v-if="showAddForm" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('add_category') }}</h3>
                <button @click="closeAddForm" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="addCategory" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('category_name') }}</label>
                        <input v-model="form.name" type="text" required
                               class="w-full border dark:border-gray-600 rounded px-3 py-2 focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                               :placeholder="t('category_name_placeholder')">
                    </div>

                    <!-- Tipe -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('transaction_type') }}</label>
                        <select v-model="form.type" required
                                class="w-full border dark:border-gray-600 rounded px-3 py-2 focus:ring-2 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="income">{{ t('income') }}</option>
                            <option value="expense">{{ t('expense') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Warna -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('color') }}</label>
                    <div class="flex items-center space-x-3">
                        <input v-model="form.color" type="color"
                               class="w-12 h-8 border dark:border-gray-600 rounded cursor-pointer bg-white dark:bg-gray-700">
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ form.color }}</span>
                    </div>
                </div>

                <!-- Aksi -->
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeAddForm"
                            class="px-4 py-2 border dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ t('cancel') }}
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
                        {{ submitting ? t('saving') : t('save_category') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- List Kategori -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Kategori Pemasukan -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700">
                <div class="p-6 border-b bg-green-50 dark:bg-green-900/20 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-green-800 dark:text-green-200">{{ t('income_categories') }}</h3>
                    <p class="text-sm text-green-600 dark:text-green-300 mt-1">{{ t('income_categories_desc') }}</p>
                </div>
                <div class="p-6 min-h-[300px]">
                    <div v-if="loading" class="text-center py-8">
                        <div class="text-gray-500 dark:text-gray-400">{{ t('loading_categories') }}</div>
                    </div>
                    <div v-else-if="incomeCategories.length === 0" class="text-center py-8">
                        <div class="text-gray-500 dark:text-gray-400">{{ t('no_categories') }}</div>
                    </div>
                    <div v-else class="space-y-3">
                        <div v-for="category in incomeCategories" :key="category.id"
                             class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 rounded-full" :style="{ backgroundColor:category.color }"></div>
                                <span class="font-medium text-gray-900 dark:text-white">{{ category.name }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ category.type }}</span>
                                <button @click="deleteCategory(category.id, category.name)"
                                        class="text-red-500 hover:text-red-700 p-1 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kategori Pengeluaran -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700">
                <div class="p-6 border-b bg-red-50 dark:bg-red-900/20 dark:border-gray-700">
                    <h3 class="text-xl font-semibold text-red-800 dark:text-red-200">{{ t('expense_categories') }}</h3>
                    <p class="text-sm text-red-600 dark:text-red-300 mt-1">{{ t('expense_categories_desc') }}</p>
                </div>
                <div class="p-6 min-h-[300px]">
                    <div v-if="loading" class="text-center py-8">
                        <div class="text-gray-500 dark:text-gray-400">{{ t('loading_categories') }}</div>
                    </div>

                    <div v-else-if="expenseCategories.length === 0" class="text-center py-8">
                        <div class="text-gray-500 dark:text-gray-400">{{ t('no_categories') }}</div>
                    </div>

                    <div v-else class="space-y-3">
                        <div v-for="category in expenseCategories" :key="category.id"
                             class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                <span class="font-medium text-gray-900 dark:text-white">{{ category.name }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500 dark:text-gray-400">{{ category.type }}</span>
                                <button @click="deleteCategory(category.id, category.name)"
                                        class="text-red-500 hover:text-red-700 p-1 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import { t } from '../utils/i18n.js'

//Data Reaktif
const loading = ref(true)
const categories = ref([])
const showAddForm = ref(false)
const submitting = ref(false)

const form = reactive({
    name: '',
    type: 'income', // default
    color: '#3B82F6' // default blue
})

//Menghitung Kategori
const incomeCategories = computed(() => {
    return categories.value.filter(cat => cat.type === 'income')
})

const expenseCategories = computed(() => {
    return categories.value.filter(cat => cat.type === 'expense')
})

//Metode
const loadCategories = async () => {
    loading.value = true
    try {
        const response = await fetch(`/api/categories`)
        const data = await response.json()

        if (data.success) {
            categories.value = data.data
        }
    } catch (error) {
        console.error('Gagal Memuat Kategori: ', error)
    } finally {
        loading.value = false
    }
}
const addCategory = async () => {
    submitting.value = true
    try {
        const response = await fetch('/api/categories', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(form)
        })
        const data = await response.json()

        if (data.success) {
            closeAddForm()
            await loadCategories() // Refresh list
        } else {
            alert(data.message || 'Failed to add category')
        }
    } catch (error) {
        console.error('Failed to add category:', error)
        alert('Failed to add category')
    } finally {
        submitting.value = false
    }
}

const closeAddForm = () => {
    showAddForm.value = false
    Object.assign(form, {
        name: '',
        type: 'income',
        color: '#3B82F6'
    })
}

// Hapus Kategori
const deleteCategory = async (id, name) => {
    if (!confirm(t('delete_category_confirmation', name))) {
        return
    }
    
    try {
        const response = await fetch(`/api/categories/${id}`, {
            method: 'DELETE'
        })
        const data = await response.json()
        
        if (data.success) {
            await loadCategories() // Refresh list
        } else {
            alert(data.message || 'Gagal menghapus kategori')
        }
    } catch (error) {
        console.error('Failed to delete category:', error)
        alert('Gagal menghapus kategori')
    }
}

//Muat data saat mount
onMounted(() => {
    loadCategories()
})
</script>
