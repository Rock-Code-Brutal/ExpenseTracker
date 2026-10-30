<script setup>
import { computed, nextTick, onUnmounted, reactive, ref, watch } from 'vue'
import { todayInNewYork } from '../utils/localization.js'

const props = defineProps({ currency: { type: String, default: 'USD' } })
const month = ref(todayInNewYork().slice(0, 7))
const budgets = ref([])
const categories = ref([])
const loading = ref(false)
const busy = ref(false)
const error = ref('')
const notice = ref('')
const errors = ref({})
const editing = ref(null)
const open = ref(false)
const pendingDelete = ref(null)
const categoryInput = ref(null)
const addButton = ref(null)
const form = reactive({ category_id: '', amount: '' })
let controller
let version = 0
const money = value => new Intl.NumberFormat('en-US', { style: 'currency', currency: props.currency }).format(value)
const availableCategories = computed(() => categories.value.filter(category => category.type === 'expense'))

async function request(url, options = {}) {
    const response = await fetch(url, { ...options, headers: { Accept: 'application/json', 'Content-Type': 'application/json' } })
    const data = await response.json().catch(() => { throw new Error('The server returned an unexpected response. Please try again.') })
    if (!response.ok || !data.success) {
        const failure = new Error(data.message || 'The request could not be completed. Please try again.')
        failure.errors = data.errors || {}
        throw failure
    }
    return data.data
}

async function load() {
    controller?.abort()
    controller = new AbortController()
    const current = ++version
    loading.value = true
    error.value = ''
    budgets.value = []
    try {
        const params = new URLSearchParams({ month: month.value, currency: props.currency })
        const [items, allCategories] = await Promise.all([
            request(`/api/budgets?${params}`, { signal: controller.signal }),
            request('/api/categories', { signal: controller.signal }),
        ])
        if (current !== version) return
        budgets.value = items
        categories.value = allCategories
    } catch (failure) {
        if (current === version && failure.name !== 'AbortError') error.value = failure.message
    } finally {
        if (current === version) loading.value = false
    }
}

function close() {
    open.value = false
    editing.value = null
    errors.value = {}
    nextTick(() => addButton.value?.focus())
}

function start(budget = null) {
    pendingDelete.value = null
    editing.value = budget?.id || null
    form.category_id = budget?.category_id || ''
    form.amount = budget?.amount || ''
    errors.value = {}
    notice.value = ''
    open.value = true
    nextTick(() => categoryInput.value?.focus())
}

async function save() {
    if (busy.value) return
    busy.value = true
    errors.value = {}
    error.value = ''
    try {
        await request(editing.value ? `/api/budgets/${editing.value}` : '/api/budgets', {
            method: editing.value ? 'PUT' : 'POST',
            body: JSON.stringify({ ...form, month: month.value, currency: props.currency }),
        })
        close()
        notice.value = 'Budget saved.'
        await load()
    } catch (failure) {
        errors.value = failure.errors || {}
        error.value = failure.message
    } finally {
        busy.value = false
    }
}

async function remove() {
    if (busy.value || !pendingDelete.value) return
    busy.value = true
    error.value = ''
    try {
        await request(`/api/budgets/${pendingDelete.value.id}`, { method: 'DELETE' })
        pendingDelete.value = null
        notice.value = 'Budget removed. Your transactions are unchanged.'
        await load()
    } catch (failure) {
        error.value = failure.message
    } finally {
        busy.value = false
    }
}

watch([month, () => props.currency], () => {
    close()
    pendingDelete.value = null
    notice.value = ''
    if (/^\d{4}-\d{2}$/.test(month.value)) load()
    else { controller?.abort(); version++; budgets.value = []; loading.value = false }
}, { immediate: true })
onUnmounted(() => { controller?.abort(); version++ })
</script>

<template>
    <section class="space-y-6 text-gray-900 dark:text-white" aria-labelledby="budgets-heading">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 id="budgets-heading" class="text-xl font-semibold">Monthly category budgets</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Plan spending in {{ currency }}. Change currency using the selector above.</p>
            </div>
            <button ref="addButton" class="budget-primary" :disabled="loading || busy || !month" @click="start()">Add budget</button>
        </div>
        <label class="block max-w-xs font-medium">Budget month
            <input v-model="month" type="month" class="budget-input mt-1" :disabled="busy" required>
        </label>
        <div v-if="notice" role="status" class="rounded-lg bg-green-50 p-4 text-green-800 dark:bg-green-900 dark:text-green-100">{{ notice }}</div>
        <div v-if="error" role="alert" class="rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-900 dark:text-red-100">
            {{ error }}
            <button v-if="!open && !pendingDelete" class="ml-2 underline" :disabled="busy" @click="load">Retry</button>
        </div>

        <form v-if="open" class="budget-panel space-y-4" @submit.prevent="save" @keydown.esc="!busy && close()">
            <h3 class="font-semibold">{{ editing ? 'Edit budget' : 'Add budget' }} — {{ month }} / {{ currency }}</h3>
            <fieldset :disabled="busy" class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="budget-category" class="block font-medium">Expense category</label>
                    <select ref="categoryInput" id="budget-category" v-model="form.category_id" class="budget-input" required :aria-invalid="!!errors.category_id" aria-describedby="budget-category-error">
                        <option disabled value="">Select a category</option>
                        <option v-for="category in availableCategories" :key="category.id" :value="category.id">{{ category.name }}</option>
                    </select>
                    <p id="budget-category-error" class="text-sm text-red-600 dark:text-red-300">{{ errors.category_id?.[0] }}</p>
                    <p v-if="!availableCategories.length" class="text-sm">Create an expense category in Categories first.</p>
                </div>
                <div>
                    <label for="budget-amount" class="block font-medium">Budget amount ({{ currency }})</label>
                    <input id="budget-amount" v-model="form.amount" class="budget-input" type="number" inputmode="decimal" min="0.01" max="9999999999999.99" step="0.01" required :aria-invalid="!!errors.amount" aria-describedby="budget-amount-error">
                    <p id="budget-amount-error" class="text-sm text-red-600 dark:text-red-300">{{ errors.amount?.[0] }}</p>
                </div>
            </fieldset>
            <div class="flex gap-3">
                <button class="budget-primary" :disabled="busy || !availableCategories.length">{{ busy ? 'Saving…' : 'Save budget' }}</button>
                <button type="button" class="budget-secondary" :disabled="busy" @click="close">Cancel</button>
            </div>
        </form>

        <div v-if="pendingDelete" class="budget-panel space-y-3" role="region" aria-label="Confirm budget removal">
            <p>Remove the {{ pendingDelete.category?.name }} budget for {{ month }}? Your category and transactions will be kept.</p>
            <button class="budget-primary bg-red-600" :disabled="busy" @click="remove">{{ busy ? 'Removing…' : 'Confirm removal' }}</button>
            <button class="budget-secondary ml-3" :disabled="busy" @click="pendingDelete = null">Cancel</button>
        </div>

        <p v-if="loading" role="status">Loading budgets…</p>
        <p v-else-if="!month">Select a month to view budgets.</p>
        <div v-else-if="!error && !budgets.length" class="budget-panel text-center">No budgets for {{ month }} in {{ currency }}. Add your first budget to start planning.</div>
        <div v-else class="grid gap-4 lg:grid-cols-2" :aria-busy="loading">
            <article v-for="budget in budgets" :key="budget.id" class="budget-panel space-y-4">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <h3 class="font-semibold text-lg">{{ budget.category?.name }}</h3>
                    <span :class="budget.is_over_budget ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300'">{{ budget.is_over_budget ? 'Over budget' : Number(budget.remaining) === 0 ? 'Budget fully spent' : 'Within budget' }}</span>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Budget</dt><dd class="font-semibold">{{ money(budget.amount) }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Actual spending</dt><dd class="font-semibold">{{ money(budget.actual_spending) }}</dd></div>
                    <div><dt class="text-sm text-gray-500 dark:text-gray-400">Remaining</dt><dd class="font-semibold" :class="budget.is_over_budget && 'text-red-700 dark:text-red-300'">{{ money(budget.remaining) }}</dd></div>
                </dl>
                <div class="flex gap-3">
                    <button class="budget-secondary" :disabled="busy" :aria-label="`Edit ${budget.category?.name} budget`" @click="start(budget)">Edit</button>
                    <button class="budget-secondary text-red-700 dark:text-red-300" :disabled="busy" :aria-label="`Remove ${budget.category?.name} budget`" @click="pendingDelete = budget; close()">Remove</button>
                </div>
            </article>
        </div>
    </section>
</template>

<style scoped>
@reference "../../css/app.css";
.budget-input { @apply w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white; }
.budget-panel { @apply rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800; }
.budget-primary { @apply rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700 disabled:opacity-50; }
.budget-secondary { @apply rounded-md border border-gray-300 px-4 py-2 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700 disabled:opacity-50; }
button:focus-visible, input:focus-visible, select:focus-visible { outline: 2px solid #3b82f6; outline-offset: 3px; }
</style>
