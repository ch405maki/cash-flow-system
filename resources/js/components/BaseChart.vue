<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import {
  Chart as ChartJS,
  Title, Tooltip, Legend,
  CategoryScale, LinearScale,
  BarElement, BarController,
  LineElement, PointElement, LineController
} from 'chart.js';

ChartJS.register(
  Title, Tooltip, Legend,
  CategoryScale, LinearScale,
  BarElement, BarController,
  LineElement, PointElement, LineController
);

interface Props {
  chartData: ChartData<'bar'>;
  chartOptions?: ChartOptions<'bar'>;
}

const props = withDefaults(defineProps<Props>(), {
  chartOptions: () => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top' as const,
      },
    }
  })
});

const chartRef = ref<HTMLCanvasElement | null>(null);
const chartInstance = ref<ChartJS<'bar'> | null>(null);

const renderChart = () => {
  if (!chartRef.value) return;

  if (chartInstance.value) {
    chartInstance.value.destroy();
  }

  const ctx = chartRef.value.getContext('2d');
  if (!ctx) return;

  chartInstance.value = new ChartJS(ctx, {
    type: 'bar',
    data: props.chartData,
    options: props.chartOptions
  });
};

watch(() => props.chartData, renderChart, { deep: true });
onMounted(renderChart);
onBeforeUnmount(() => chartInstance.value?.destroy());
</script>

<template>
  <div class="relative w-full">
    <canvas 
      ref="chartRef" 
      class="w-full h-full"
      aria-label="Chart"
      role="img"
    ></canvas>
  </div>
</template>