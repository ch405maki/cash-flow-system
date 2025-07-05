<!-- components/BaseChart.vue -->
<script setup lang="ts">
import { ref, onMounted, watch, defineProps } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
  type: {
    type: String,
    required: true
  },
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const chartRef = ref<HTMLCanvasElement>();
const chartInstance = ref<Chart>();

onMounted(() => {
  if (chartRef.value) {
    chartInstance.value = new Chart(chartRef.value, {
      type: props.type,
      data: props.data,
      options: props.options
    });
  }
});

watch(() => props.data, (newData) => {
  if (chartInstance.value) {
    chartInstance.value.data = newData;
    chartInstance.value.update();
  }
}, { deep: true });
</script>

<template>
  <canvas ref="chartRef"></canvas>
</template>