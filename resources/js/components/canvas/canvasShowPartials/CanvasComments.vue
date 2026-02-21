<script setup lang="ts">
import { Textarea } from '@/components/ui/textarea';

const props = defineProps<{
  modelValue: string
  placeholder?: string
  label?: string
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>();
</script>

<template>
  <div>
    <h3 v-if="label" class="text-sm font-medium text-muted-foreground">
      {{ label }}
    </h3>

    <Textarea
      :model-value="modelValue"
      @update:model-value="emit('update:modelValue', $event)"
      :placeholder="placeholder"
      class="mt-2"
    />
  </div>
</template>