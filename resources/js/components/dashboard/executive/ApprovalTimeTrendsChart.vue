<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import {
  Chart,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  BarElement,
  BarController,
  LineController
} from 'chart.js';
import PageHeader from '@/components/PageHeader.vue';

// Register Chart.js components
Chart.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  BarElement,
  BarController,
  LineController
);

const props = defineProps<{
  approvalTimeTrends: {
    labels: string[];
    datasets: {
      canvas: number[];
      purchase_orders: number[];
      request_to_orders: number[];
      vouchers: number[];
    };
  };
}>();

const chartRef = ref<HTMLCanvasElement | null>(null);
const chartInstance = ref<Chart | null>(null);

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
    },
    title: {
      display: true,
      text: 'Approval Trends (Last 6 Months)'
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      title: {
        display: true,
        text: 'Number of Approvals'
      }
    },
    x: {
      title: {
        display: true,
        text: 'Month'
      }
    }
  }
};

const createChart = () => {
  if (!chartRef.value || !props.approvalTimeTrends) return;

  // Destroy previous chart instance if it exists
  if (chartInstance.value) {
    chartInstance.value.destroy();
  }

  const ctx = chartRef.value.getContext('2d');
  if (!ctx) return;

  chartInstance.value = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.approvalTimeTrends.labels,
      datasets: [
        {
          label: 'Canvas Approvals',
          data: props.approvalTimeTrends.datasets.canvas,
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 2
        },
        {
          label: 'Purchase Order Approvals',
          data: props.approvalTimeTrends.datasets.purchase_orders,
          backgroundColor: 'rgba(255, 99, 132, 0.5)',
          borderColor: 'rgba(255, 99, 132, 1)',
          borderWidth: 2
        },
        {
          label: 'Request to Order Approvals',
          data: props.approvalTimeTrends.datasets.request_to_orders,
          backgroundColor: 'rgba(75, 192, 192, 0.5)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 2
        },
        {
          label: 'Voucher Approvals',
          data: props.approvalTimeTrends.datasets.vouchers,
          backgroundColor: 'rgba(255, 205, 86, 0.5)',
          borderColor: 'rgba(255, 205, 86, 1)',
          borderWidth: 2
        }
      ]
    },
    options: chartOptions
  });
};

onMounted(() => {
  createChart();
});

watch(() => props.approvalTimeTrends, () => {
  createChart();
}, { deep: true });
</script>

<template>
  <PageHeader 
      title="Approval Trends"
      subtitle="Tracking approval and volume trends over the last 6 months"
  />
  <div class="p-2">
    <div class="h-80">
      <canvas ref="chartRef"></canvas>
    </div>
  </div>
</template>