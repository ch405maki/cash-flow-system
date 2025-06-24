<script setup lang="ts">
defineProps({
  status: {
    type: String,
    required: true
  }
})
</script>

<template>
  <span 
    class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
    :class="{
      'bg-indigo-100 text-indigo-800': status === 'partially_released',
      'bg-orange-100 text-orange-800': status === 'request to order',
      'bg-green-100 text-green-700': status === 'released',
      'bg-yellow-100 text-yellow-800': status === 'pending',
      'bg-green-100 text-green-800': status === 'approved',
      'bg-red-100 text-red-800': status === 'rejected',
    }"
  >
    {{ status }}
  </span>
</template>