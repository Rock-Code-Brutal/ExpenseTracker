<script setup>
import {computed, onMounted, ref, watch} from "vue";
import {reactive} from "vue";
import { t } from '../utils/i18n.js';
// Props dari App.vue (untuk currency)
const props = defineProps({
    currency: {
        type: String,
        default: 'IDR'
    }
})

// UI state
const showForm = ref(false)
const submitting = ref(false)
const loading = ref(true)
const editingTransaction = ref(null)

// Data
const categories = ref([])
const transactions = ref([])
const pagination = ref({
    current_page: 1,
    last_page: 1,
    has_more: false,
    per_page: 50, // Increase default to 50
    total: 0
})
const loadingMore = ref(false)

// CSV Import state
const showImportModal = ref(false)
const importing = ref(false)
const importResult = ref(null)

// Computed property for more errors message
const moreErrorsMessage = computed(() => {
    if (!importResult.value?.errors || importResult.value.errors.length <= 5) return ''
    const count = importResult.value.errors.length - 5
    return t('and_more_errors')(count)
})

// Filter
const filters = reactive({
    search: '',
    type: '',
    category_id: '',
    start_date: '',
    end_date: ''
})
const showFilters = ref(false)

// Form state
const form = reactive({
    type: '',
    category_id: '',
    amount: '',
    transaction_date: new Date().toISOString().split('T')[0],
    description: '',
    currency: props.currency
})

// Computed: filter kategori sesuai type
const filteredCategories = computed(() => {
    if (!form.type) return []
    return categories.value.filter(c => c.type === form.type)
})

// Memuat kategori dari API
const loadCategories = async () => {
    try {
        const res = await fetch('/api/categories')
        const json = await res.json()
        if (json.success) {
            categories.value = json.data
        }
    } catch (e) {
        console.error('Failed to load categories', e)
    }
}

//Format Helper
const formatCurrency = (amount) => {
    return new Intl.NumberFormat(props.currency === 'IDR' ? 'id-ID' : 'en-US', {
        style: 'currency',
        currency: props.currency,
    }).format(amount)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {day: '2-digit', month: '2-digit', year: 'numeric'})
}

//Muat Transaksi
const loadTransactions = async (page = 1, append = false) => {
    if (page === 1) {
        loading.value = true
        pagination.value.current_page = 1
    } else {
        loadingMore.value = true
    }
    
    try {
        //Bangun Parameter Kueri
        const params = new URLSearchParams()
        params.append('currency', props.currency)
        params.append('page', page)
        params.append('per_page', pagination.value.per_page)

        if (filters.type) params.append('type', filters.type)
        if (filters.category_id) params.append('category_id', filters.category_id)
        if (filters.start_date) params.append('start_date', filters.start_date)
        if (filters.end_date) params.append('end_date', filters.end_date)

        const res = await fetch(`/api/transactions?${params.toString()}`)
        const json = await res.json()
        if (json.success) {
            let results = json.data.data
            
            // Update pagination info
            pagination.value = {
                current_page: json.data.current_page,
                last_page: json.data.last_page,
                has_more: json.data.current_page < json.data.last_page,
                per_page: json.data.per_page,
                total: json.data.total
            }

            //Filter pencarian dari sisi klien
            if (filters.search) {
                results = results.filter(tx =>
                    tx.description?.toLowerCase().includes(filters.search.toLowerCase()) ||
                    tx.category?.name?.toLowerCase().includes(filters.search.toLowerCase())
                )
            }
            
            // Append or replace transactions
            if (append && page > 1) {
                transactions.value = [...transactions.value, ...results]
            } else {
                transactions.value = results
            }
        }
    } catch (e) {
        console.error('Failed to load transactions', e)
    } finally {
        loading.value = false
        loadingMore.value = false
    }
}

//Submit Tambah Transaksi
const addTransaction = async () => {
    submitting.value = true

    // Debug: log form data
    console.log('Form data:', form)
    console.log('Props currency:', props.currency)

    try {
        const payload = {...form, currency: props.currency}
        console.log('Sending payload:', payload)

        const res = await fetch('/api/transactions', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        })

        console.log('Response status:', res.status)
        const json = await res.json()
        console.log('Response data:', json)

        if (json.success) {
            closeForm()
            await loadTransactions()
        } else {
            console.error('API Error:', json)
            alert(json.message || 'Failed to save transaction')
        }
    } catch (e) {
        console.error('Network/Parse Error:', e)
        alert('Failed to save transaction: ' + e.message)
    } finally {
        submitting.value = false
    }
}

// Mengedit Data
const editTransaction = (transaction) => {
    console.log('Editing transaction:', transaction) // Debug log
    editingTransaction.value = transaction
    
    // Populate form dengan data yang ada
    form.type = transaction.type
    form.category_id = String(transaction.category_id) // Convert to string untuk select option
    form.amount = String(transaction.amount) // Convert to string untuk number input
    form.transaction_date = transaction.transaction_date
    form.description = transaction.description || ''
    form.currency = transaction.currency
    
    showForm.value = true
    console.log('Form populated:', form) // Debug log
}

// Mengupdate Data
const updateTransaction = async () => {
    submitting.value = true
    
    // Debug logs
    console.log('Updating transaction:', editingTransaction.value.id)
    console.log('Form data before update:', form)
    
    try {
        // Prepare payload dengan type conversion yang benar
        const payload = {
            ...form,
            currency: props.currency,
            amount: Number(form.amount), // Convert back to number
            category_id: Number(form.category_id) // Convert back to number
        }
        
        console.log('Update payload:', payload)
        
        const res = await fetch(`/api/transactions/${editingTransaction.value.id}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        })

        console.log('Update response status:', res.status)
        const json = await res.json()
        console.log('Update response data:', json)
        
        if (json.success) {
            closeForm()
            await loadTransactions()
        } else {
            console.error('Update API Error:', json)
            alert(json.message || 'Failed to update transaction')
        }
    } catch (e) {
        console.error('Update Error:', e)
        alert('Failed to update transaction: ' + e.message)
    } finally {
        submitting.value = false
    }
}
// Menghapus Data
const deleteTransaction = async (id) => {
    if (!confirm('Delete this transaction?')) return
    try {
        const res = await fetch(`/api/transactions/${id}`, {method: 'DELETE'})
        const json = await res.json()
        if (json.success) {
            await loadTransactions()
        } else {
            alert('Failed to delete transaction')
        }
    } catch (e) {
        console.error('Failed to delete transaction', e)
    }
}

const clearFilters = () => {
    Object.assign(filters, {
        search: '',
        type: '',
        category_id: '',
        start_date: '',
        end_date: ''
    })
    loadTransactions()
}

// Export to CSV function
const exportToCSV = () => {
    if (transactions.value.length === 0) {
        alert(t('no_data_to_export'))
        return
    }
    
    // CSV headers
    const headers = ['Date', 'Type', 'Category', 'Amount', 'Currency', 'Description']
    
    // CSV rows
    const rows = transactions.value.map(tx => [
        tx.transaction_date,
        tx.type,
        tx.category?.name || '',
        tx.amount,
        tx.currency,
        tx.description || ''
    ])
    
    // Combine headers and rows
    const csvContent = [headers, ...rows]
        .map(row => row.map(field => `"${field}"`).join(','))
        .join('\n')
    
    // Create and download file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `transactions_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
}

// Load more transactions
const loadMore = async () => {
    if (pagination.value.has_more && !loadingMore.value) {
        await loadTransactions(pagination.value.current_page + 1, true)
    }
}

// CSV Import functionality
const handleFileImport = (event) => {
    console.log('handleFileImport called')
    const file = event.target.files[0]
    if (!file) {
        console.log('No file selected')
        return
    }
    
    console.log('File selected:', file.name, file.size, file.type)
    
    if (!file.name.endsWith('.csv')) {
        alert(t('invalid_file_type'))
        return
    }
    
    const reader = new FileReader()
    reader.onload = async (e) => {
        console.log('File read complete, data length:', e.target.result.length)
        const csvData = e.target.result
        await importCSV(csvData)
    }
    reader.onerror = (e) => {
        console.error('FileReader error:', e)
    }
    reader.readAsText(file)
}

const importCSV = async (csvData) => {
    console.log('importCSV called with data length:', csvData.length)
    console.log('CSV data preview:', csvData.substring(0, 200))
    
    importing.value = true
    importResult.value = null
    
    try {
        console.log('Sending import request...')
        const response = await fetch('/api/transactions/import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                csv_data: csvData,
                currency: props.currency
            })
        })
        
        console.log('Import response status:', response.status)
        const result = await response.json()
        console.log('Import result:', result)
        
        importResult.value = result
        
        if (result.success) {
            console.log('Import successful, reloading transactions')
            await loadTransactions() // Reload transactions
        }
    } catch (error) {
        console.error('Import error:', error)
        importResult.value = {
            success: false,
            message: 'Failed to import CSV: ' + error.message
        }
    } finally {
        importing.value = false
        console.log('Import process finished')
    }
}

const closeImportModal = () => {
    showImportModal.value = false
    importResult.value = null
    // Reset file input
    const fileInput = document.getElementById('csv-file-input')
    if (fileInput) fileInput.value = ''
}

// Watch filters untuk auto-reload
watch(filters, () => {
    loadTransactions(1, false) // Reset to page 1 when filters change
}, { deep: true })

// Menutup atau Mereset form
const closeForm = () => {
    showForm.value = false
    editingTransaction.value = null
    Object.assign(form, {
        type: '',
        category_id: '',
        amount: '',
        transaction_date: new Date().toISOString().split('T')[0],
        description: '',
        currency: props.currency
    })
}

watch(() => props.currency, () => {
    loadTransactions()
    form.currency = props.currency
})

// Inisialisasi
onMounted(() => {
    loadCategories()
    loadTransactions()
})
</script>

<template>
    <div class="space-y-6">
        <!--Header-->
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ t('transactions') }}</h2>
            <div class="flex space-x-2">
                <button @click="showImportModal = true"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    {{ t('import_csv') }}
                </button>
                <button @click="exportToCSV"
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                    {{ t('export_csv') }}
                </button>
                <button @click="showForm = true"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    {{ t('add_transaction') }}
                </button>
            </div>
        </div>

        <!--Placeholder untuk form dan list-->
        <div v-if="showForm" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                {{ editingTransaction ? t('edit_transaction') : t('add_transaction') }}
            </h3>

            <form @submit.prevent="editingTransaction ? updateTransaction() : addTransaction()" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Tipe -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('transaction_type') }}</label>
                        <select v-model="form.type" required
                                class="w-full border dark:border-gray-600 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">{{ t('select_type') }}</option>
                            <option value="income">{{ t('income') }}</option>
                            <option value="expense">{{ t('expense') }}</option>
                        </select>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('category') }}</label>
                        <select v-model="form.category_id" required
                                class="w-full border dark:border-gray-600 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">{{ t('select_category') }}</option>
                            <option v-for="category in filteredCategories"
                                    :key="category.id"
                                    :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Jumlah -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('amount') }}</label>
                        <input v-model="form.amount" type="number" step="0.01" min="0.01" required
                               class="w-full border dark:border-gray-600 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                               placeholder="0">
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('date') }}</label>
                        <input v-model="form.transaction_date" type="date" required
                               class="w-full border dark:border-gray-600 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('description') }}</label>
                    <input v-model="form.description" type="text"
                           class="w-full border dark:border-gray-600 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                           :placeholder="t('description_placeholder')">
                </div>

                <!-- Aksi Form -->
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="closeForm"
                            class="px-4 py-2 border dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ t('cancel') }}
                    </button>
                    <button type="submit" :disabled="submitting"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                        {{
                            submitting ? t('saving') : (editingTransaction ? t('update_transaction') : t('save_transaction'))
                        }}
                    </button>
                </div>
            </form>
        </div>

        <!-- CSV Import Modal -->
        <div v-if="showImportModal" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                📁 {{ t('import_csv') }}
            </h3>
            
            <div v-if="!importResult" class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="mt-2">
                        <label for="csv-file-input" class="cursor-pointer">
                            <span class="mt-2 block text-sm font-medium text-gray-900 dark:text-white">
                                {{ t('select_csv_file') }}
                            </span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ t('csv_format_info') }}
                            </p>
                        </label>
                        <input 
                            id="csv-file-input" 
                            type="file" 
                            accept=".csv" 
                            @change="handleFileImport" 
                            class="hidden"
                        >
                    </div>
                </div>
                
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">{{ t('csv_format_requirements') }}</h4>
                    <ul class="text-xs text-blue-800 dark:text-blue-300 space-y-1">
                        <li>• {{ t('csv_headers') }}: Date, Type, Category, Amount, Currency, Description</li>
                        <li>• {{ t('csv_date_format') }}: YYYY-MM-DD</li>
                        <li>• {{ t('csv_type_format') }}: income / expense</li>
                        <li>• {{ t('csv_category_requirement') }}</li>
                    </ul>
                </div>
            </div>
            
            <!-- Import Progress -->
            <div v-if="importing" class="text-center py-6">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto"></div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('importing_csv') }}...</p>
            </div>
            
            <!-- Import Result -->
            <div v-if="importResult" class="space-y-4">
                <div :class="importResult.success ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'" class="p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg v-if="importResult.success" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p :class="importResult.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'" class="text-sm font-medium">
                                {{ importResult.message }}
                            </p>
                            <div v-if="importResult.errors && importResult.errors.length > 0" class="mt-2">
                                <p class="text-xs text-red-700 dark:text-red-300 font-medium">{{ t('import_errors') }}:</p>
                                <ul class="mt-1 text-xs text-red-700 dark:text-red-300 list-disc list-inside">
                                    <li v-for="error in importResult.errors.slice(0, 5)" :key="error">{{ error }}</li>
                                    <li v-if="importResult.errors.length > 5" class="font-medium">{{ moreErrorsMessage }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Actions -->
            <div class="flex justify-end space-x-3 mt-6">
                <button @click="closeImportModal" class="px-4 py-2 border dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ importResult ? t('close') : t('cancel') }}
                </button>
            </div>
        </div>
        
        <!-- Bagian Filter -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow border dark:border-gray-700 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-medium text-gray-900 dark:text-white">{{ t('filter_search') }}</h3>
                <button @click="showFilters = !showFilters"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                    {{ showFilters ? t('hide_filters') : t('show_filters') }}
                </button>
            </div>

            <div v-show="showFilters" class="space-y-4">
                <!-- Pencarian -->
                <div>
                    <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('search') }}</label>
                    <input v-model="filters.search" type="text"
                           class="w-full border dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                           :placeholder="t('search_placeholder')">
                </div>

                <!-- Filter Baris -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Filter Ketikan -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('transaction_type') }}</label>
                        <select v-model="filters.type" class="w-full border dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">{{ t('all_types') }}</option>
                            <option value="income">{{ t('income') }}</option>
                            <option value="expense">{{ t('expense') }}</option>
                        </select>
                    </div>

                    <!-- Filter Kategori -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('category') }}</label>
                        <select v-model="filters.category_id" class="w-full border dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">{{ t('all_categories') }}</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }} ({{ category.type }})
                            </option>
                        </select>
                    </div>

                    <!-- Tanggal Awal -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('from_date') }}</label>
                        <input v-model="filters.start_date" type="date"
                               class="w-full border dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <!-- Tanggal Akhir -->
                    <div>
                        <label class="block text-sm font-medium mb-1 text-gray-900 dark:text-white">{{ t('to_date') }}</label>
                        <input v-model="filters.end_date" type="date"
                               class="w-full border dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>
                </div>

                <!-- Tombol Menghapus Filter -->
                <div class="flex justify-end">
                    <button @click="clearFilters()"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ t('clear_filters') }}
                    </button>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border dark:border-gray-700 p-6">
            <div v-if="loading" class="text-center py-4 text-gray-900 dark:text-white">{{ t('loading') }}</div>

            <div v-else-if="transactions.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                {{ t('no_transactions') }}
            </div>

            <div v-else class="space-y-3">
                <div v-for="tx in transactions" :key="tx.id"
                     class="flex flex-col sm:flex-row sm:justify-between sm:items-center p-4 border dark:border-gray-600 rounded-lg hover:shadow-md transition-shadow bg-white dark:bg-gray-700">
                    <div class="flex items-center space-x-3 mb-2 sm:mb-0">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" 
                             :style="{ backgroundColor: tx.category?.color || '#6B7280' }">
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 dark:text-white truncate">
                                {{ tx.category?.name || '-' }}
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ tx.type }})</span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ tx.description || t('no_description') }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(tx.transaction_date) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end sm:text-right">
                        <p :class="tx.type === 'income' ? 'text-green-600' : 'text-red-600'" 
                           class="font-semibold text-lg sm:mb-1">
                            {{ tx.type === 'income' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                        </p>
                        <div class="flex space-x-2 ml-4 sm:ml-0">
                            <button @click="editTransaction(tx)" 
                                    class="text-blue-500 dark:text-blue-400 text-xs hover:bg-blue-50 dark:hover:bg-blue-900 px-2 py-1 rounded">
                                {{ t('edit') }}
                            </button>
                            <button @click="deleteTransaction(tx.id)" 
                                    class="text-red-500 dark:text-red-400 text-xs hover:bg-red-50 dark:hover:bg-red-900 px-2 py-1 rounded">
                                {{ t('delete') }}
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Load More Button -->
                <div v-if="pagination.has_more" class="text-center mt-6">
                    <button @click="loadMore" :disabled="loadingMore"
                            class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="loadingMore">{{ t('loading') }}...</span>
                        <span v-else>📄 {{ t('load_more') }}</span>
                    </button>
                </div>
                
                <!-- Pagination Info -->
                <div v-if="pagination.total > 0" class="text-center mt-4 text-sm text-gray-500 dark:text-gray-400">
                    {{ t('showing') }} {{ transactions.length }} {{ t('of') }} {{ pagination.total }} {{ t('transactions') }}
                </div>
            </div>
        </div>
    </div>
</template>
