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

const toggleSelectAll = (checked: boolean) => {
  emit('toggleSelectAll', { checked, shouldAutoFill: true })
}
</script>

<template>
  <div class="flex justify-between items-center">
    <div class="flex items-center gap-2">
      <Checkbox 
        id="select-all"
        :checked="selectedItems.length === details.length"
        @update:checked="toggleSelectAll"
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
        class="bg-green-600 hover:bg-green-700"
      >
        <span v-if="isReleasing" class="text-xs">
          <svg class="animate-spin h-4 w-4 inline mr-1" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
          </svg>
          Releasing & Syncing Inventory...
        </span>
        <span v-else class="text-xs">Release & Update Inventory</span>
      </Button>
    </div>
  </div>
</template>