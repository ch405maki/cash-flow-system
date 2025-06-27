<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'
import { formatCurrency, formatDate } from '@/lib/utils'

Chart.register(...registerables)

const props = defineProps<{
  stats: {
    monthlyData: Array<{
      year: number
      month: number
      count: number
      amount: number
    }>
  }
}>()

const chartRef = ref<HTMLCanvasElement | null>(null)
let chartInstance: Chart | null = null

onMounted(() => {
  if (chartRef.value) {
    const ctx = chartRef.value.getContext('2d')
    if (ctx) {
      const monthlyLabels = props.stats.monthlyData.map(item => {
        const date = new Date(item.year, item.month - 1)
        return formatDate(date.toISOString(), 'MMM yyyy')
      })

      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: monthlyLabels,
          datasets: [
            {
              label: 'Voucher Count',
              data: props.stats.monthlyData.map(item => item.count),
              backgroundColor: 'rgba(139, 92, 246, 0.7)', // violet-500
              borderColor: 'rgba(139, 92, 246, 1)',       // violet-500
              borderWidth: 1
            },
            {
              label: 'Amount (₱)',
              data: props.stats.monthlyData.map(item => item.amount),
              backgroundColor: 'rgba(167, 139, 250, 0.7)', // violet-300
              borderColor: 'rgba(167, 139, 250, 1)',       // violet-300
              borderWidth: 1,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top'
            },
            tooltip: {
              callbacks: {
                label(context) {
                  const label = context.dataset.label || ''
                  const value = context.raw as number
                  return label.includes('Amount')
                    ? `${label}: ${formatCurrency(value)}`
                    : `${label}: ${value}`
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Voucher Count'
              }
            },
            y1: {
              beginAtZero: true,
              position: 'right',
              title: {
                display: true,
                text: 'Amount (₱)'
              },
              grid: {
                drawOnChartArea: false
              }
            }
          }
        }
      })
    }
  }
})
</script>

<template>
  <div class="w-full bg-white rounded-lg border border-violet-200 p-4 shadow-sm">
    <div class="h-[400px] w-full">
      <canvas ref="chartRef" class="w-full h-full"></canvas>
    </div>
  </div>
</template>
