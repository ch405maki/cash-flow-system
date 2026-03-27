<script setup lang="ts">
defineProps<{
  modelValue: boolean
}>()

defineEmits<{
  (e: 'update:modelValue', value: boolean): void
}>()
</script>

<template>
  <button
    type="button"
    role="switch"
    :aria-checked="modelValue"
    :class="[
      'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
      modelValue ? 'bg-primary' : 'bg-gray-200 dark:bg-gray-700'
    ]"
    @click="$emit('update:modelValue', !modelValue)"
  >
    <span
      :class="[
        'inline-block h-5 w-5 transform rounded-full bg-white shadow-lg transition-transform',
        modelValue ? 'translate-x-6' : 'translate-x-0.5'
      ]"
    />
  </button>
</template>