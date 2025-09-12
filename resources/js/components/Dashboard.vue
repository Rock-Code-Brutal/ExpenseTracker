<template>
    <div class="space-y-6">
        <!--Loading-->
        <div v-if="loading" class="text-center py-8">
                        <div class="text-gray-500 dark:text-gray-400">{{ t('loading') }}</div>
        </div>

        <!--Konten Dashboard-->
        <div v-else>
            <!--Kartu Ringkasan-->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!--Total Balance-->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ t('total_balance') }}</h3>
                    <p :class="dashboardData.balance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                       class="text-2xl font-bold">
                        {{ formatCurrency(dashboardData.balance) }}
                    </p>
                </div>

                <!--Pemasukan Bulanan-->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ t('monthly_income') }}</h3>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ formatCurrency(dashboardData.monthly_income) }}
                    </p>
                </div>

                <!--Pengeluaran Bulanan-->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border dark:border-gray-700">
                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ t('monthly_expense') }}</h3>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ formatCurrency(dashboardData.monthly_expense) }}
                    </p>
                </div>
            </div>

            <!-- Monthly Chart -->
            <div class="mb-6">
                <ChartComponent 
                :title="`${t('monthly_income_vs_expense')} (${props.currency})`"
                    type="bar"
                    :data="monthlyChartData"
                />
            </div>

            <!--Transaksi Terakhir-->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700">
                <div class="p-6 border-b dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ t('recent_transactions') }}</h3>
                </div>

                <div class="p-6">
                    <div v-if="dashboardData.recent_transactions ?.length === 0"
                         class="text-center py-8 text-gray-500 dark:text-gray-400">
                        {{ t('no_recent_transactions') }}
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="transaction in dashboardData.recent_transactions"
                             :key="transaction.id"
                             class="flex items-center justify-between p-3 border dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 rounded-full"
                                     :style="{ backgroundColor : transaction.category ?.color || '#6B7280' }">

                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ transaction.category?.name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">{{
                                            transaction.description || t('no_description')
                                        }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(transaction.transaction_date) }}</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <p :class="transaction.type === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                   class="font-semibold">
                                    {{ transaction.type === 'income' ? '+' : '-' }}{{
                                        formatCurrency(transaction.amount)
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import ChartComponent from './ChartComponent.vue'
import { t } from '../utils/i18n.js'
// Prop
const props = defineProps({
    currency: {
        type: String,
        default: 'IDR'
    }
})

//Data Reaktif
const loading = ref(true)
const dashboardData = ref({
    balance: 0,
    total_income: 0,
    total_expense: 0,
    monthly_income: 0,
    monthly_expense: 0,
    recent_transactions: []
})

//Metode
const formatCurrency = (amount) => {
    if (props.currency === 'IDR') {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount)
    } else {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount)
    }
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

// Computed untuk chart data
const monthlyChartData = computed(() => {
    console.log('Dashboard: Computing chart data')
    console.log('Dashboard: monthly_data:', dashboardData.value.monthly_data)
    
    if (!dashboardData.value.monthly_data) {
        console.log('Dashboard: No monthly_data, returning empty chart')
        return {labels: [], datasets: []}
    }

    const months = dashboardData.value.monthly_data.map(item => item.month)
    const income = dashboardData.value.monthly_data.map(item => item.income)
    const expense = dashboardData.value.monthly_data.map(item => item.expense)
    
    console.log('Dashboard: Chart months:', months)
    console.log('Dashboard: Chart income:', income)
    console.log('Dashboard: Chart expense:', expense)

    const chartData = {
        labels: months,
        datasets: [
            {
                label: 'Income',
                data: income,
                backgroundColor: 'rgba(34, 197, 94, 0.8)',
                borderColor: 'rgb(34, 197, 94)',
                borderWidth: 1
            },
            {
                label: 'Expense',
                data: expense,
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
                borderWidth: 1
            }
        ]
    }
    
    console.log('Dashboard: Final chart data:', chartData)
    return chartData
})


const loadDashboard = async () => {
    loading.value = true
    try {
        console.log('Loading dashboard with currency:', props.currency)
        const response = await fetch(`/api/dashboard?currency=${props.currency}`)
        const data = await response.json()
        console.log('Dashboard API response:', data)

        if (data.success) {
            dashboardData.value = data.data
            console.log('Dashboard data updated:', dashboardData.value)
        } else {
            console.error('Dashboard API failed:', data)
        }
    } catch (error) {
        console.error('Gagal untuk memuat Dashboard:', error)
    } finally {
        loading.value = false
    }
}

// Lihat apakah ada perubahan kurensi
watch(() => props.currency, () => {
    loadDashboard()
})

// Muat data saat hidup
onMounted(() => {
    loadDashboard()
})
</script>
