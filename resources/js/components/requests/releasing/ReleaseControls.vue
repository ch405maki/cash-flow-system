<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'

defineProps({
  selectedItems: {
    type: Array,
    required: true
  },
  details: {
    type: Array,
    required: true
  },
  isReleasing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'toggleSelectAll',
  'releaseItems'
])
</script>

<template>
  <div class="flex justify-between items-center">
    <div class="flex items-center gap-2">
      <Checkbox 
        id="select-all"
        :checked="selectedItems.length === details.length"
        @update:checked="(checked) => $emit('toggleSelectAll', checked)"
        class="h-4 w-4"
      />
      <Label>Select All</Label>
    </div>
    <div class="flex gap-2">
      <slot name="cancel-button"></slot>
      <Button 
        type="button" 
        @click="$emit('releaseItems')"
        size="sm"
        :disabled="selectedItems.length === 0 || isReleasing"
      >
        <span v-if="isReleasing" class="text-xs">Releasing...</span>
        <span v-else class="text-xs">Release Selected Items</span>
      </Button>
    </div>
  </div>
</template>