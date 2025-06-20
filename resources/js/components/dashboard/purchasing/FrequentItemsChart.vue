<script setup lang="ts">
import BaseChart from '@/components/BaseChart.vue'

interface FrequentItem {
  item_description: string
  total_quantity: number
  request_count: number
  unit?: string
}

const props = defineProps<{
  items: FrequentItem[];
  key?: number;
}>();

/* --- soft violet tones --- */
const violetBar   = 'rgba(139, 92, 246, 0.70)'  
const violetEdge  = 'rgba(124,  58, 237, 1.00)' 
const lavenderLine= 'rgba(167,139,250,0.90)' 

const chartData = {
  labels: props.items.map(i => i.item_description),
  datasets: [
    {
      label: 'Number of Requests',
      type: 'bar' as const,
      data: props.items.map(i => i.request_count),
      backgroundColor: violetBar,
      borderColor: violetEdge,
      borderWidth: 1,
      yAxisID: 'y',
    },
    {
      label: 'Total Quantity',
      type: 'line' as const,
      data: props.items.map(i => i.total_quantity),
      backgroundColor: lavenderLine,
      borderColor: lavenderLine,
      borderWidth: 2,
      tension: 0.3,
      fill: false,
      yAxisID: 'y1',
    },
  ],
}

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: {
      callbacks: {
        label: (ctx) => {
          const item = props.items[ctx.dataIndex]
          return `${ctx.dataset.label}: ${ctx.raw} ${
            ctx.datasetIndex === 0 ? 'requests' : item.unit || 'units'
          }`
        },
      },
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: 'rgba(0,0,0,0.05)' },
      title: { display: true, text: 'Requests' },
    },
    y1: {
      beginAtZero: true,
      position: 'right',
      grid: { drawOnChartArea: false },
      title: { display: true, text: 'Quantity' },
    },
    x: { grid: { display: false } },
  },
}
</script>

<template>
  <BaseChart :chart-data="chartData" :chart-options="chartOptions" />
</template>
