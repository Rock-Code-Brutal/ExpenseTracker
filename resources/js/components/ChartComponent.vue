<script setup>
import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PieController,
    PointElement,
    Title,
    Tooltip
} from 'chart.js'
import {computed, nextTick, onMounted, onUnmounted, ref, watch} from "vue";

// Register Chart.js components
Chart.register(
    CategoryScale,
    LinearScale,
    BarController,
    BarElement,
    LineController,
    LineElement,
    PointElement,
    PieController,
    Title,
    Tooltip,
    Legend,
    ArcElement
)

// Props
const props = defineProps({
    title: {
        type: String,
        default: 'Chart'
    },
    type: {
        type: String,
        default: 'bar'
    },
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    }
})

// Refs
const chartCanvas = ref(null)
let chartInstance = null

// Dark mode detection
const isDarkMode = computed(() => {
    return document.documentElement.classList.contains('dark')
})

// Chart colors based on theme
const getChartColors = () => {
    const isDark = isDarkMode.value
    return {
        textColor: isDark ? '#F9FAFB' : '#374151',
        gridColor: isDark ? '#4B5563' : '#E5E7EB',
        backgroundColor: isDark ? '#374151' : '#FFFFFF'
    }
}

// Methods
const createChart = async () => {
    console.log('ChartComponent: Creating chart with data:', props.data)
    console.log('ChartComponent: Chart type:', props.type)
    
    if (chartInstance) {
        chartInstance.destroy()
    }

    await nextTick()

    if (chartCanvas.value) {
        const ctx = chartCanvas.value.getContext('2d')
        console.log('ChartComponent: Canvas context ready')
        
        const colors = getChartColors()
        console.log('ChartComponent: Using colors:', colors)

        chartInstance = new Chart(ctx, {
            type: props.type,
            data: props.data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: colors.textColor
                        }
                    }
                },
                scales: props.type !== 'pie' ? {
                    x: {
                        ticks: {
                            color: colors.textColor
                        },
                        grid: {
                            color: colors.gridColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: colors.textColor
                        },
                        grid: {
                            color: colors.gridColor
                        }
                    }
                } : {},
                ...props.options
            }
        })
        console.log('ChartComponent: Chart created successfully')
    } else {
        console.error('ChartComponent: Canvas element not found')
    }
}

// Watchers
watch(() => [props.data, props.type], () => {
    createChart()
}, {deep: true})

// Watch for dark mode changes
watch(isDarkMode, () => {
    console.log('ChartComponent: Dark mode changed, recreating chart')
    createChart()
})

// Lifecycle
onMounted(() => {
    createChart()
})

onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy()
    }
})
</script>

<template>
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border dark:border-gray-700">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">📈 {{ title }}</h3>
        <div class="relative" style="height: 300px;">
            <canvas ref="chartCanvas"></canvas>
        </div>
    </div>
</template>

<style scoped>

</style>
