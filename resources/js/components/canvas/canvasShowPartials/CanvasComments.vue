<script setup lang="ts">
import { Textarea } from '@/components/ui/textarea';

defineProps<{
  modelValue: string
  placeholder?: string
  label?: string
}>();

defineEmits<{
  (e: 'update:modelValue', value: string): void
}>();
</script>

<template>
  <div>
    <h3 v-if="label" class="text-sm font-medium text-muted-foreground">{{ label }}</h3>
    <Textarea
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
      :placeholder="placeholder"
      class="mt-2"
    />
  </div>
</template>