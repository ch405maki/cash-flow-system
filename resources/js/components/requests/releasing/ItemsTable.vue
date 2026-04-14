<script setup lang="ts">
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Trash2, Package, PackageX, PackageCheck } from 'lucide-vue-next'
import { Checkbox } from '@/components/ui/checkbox'

const props = defineProps({
  details: {
    type: Array,
    required: true
  },
  selectedItems: {
    type: Array,
    required: true
  }
})

const emit = defineEmits([
  'update:selectedItems',
  'update:releasedQuantity',
  'removeDetail'
])

// Check if item is fully released
const isFullyReleased = (detail: any) => {
  return detail.released_quantity >= detail.quantity
}

// Get remaining quantity
const getRemainingQuantity = (detail: any) => {
  return detail.quantity - detail.released_quantity
}

const toggleItemSelection = (id: number, checked: boolean, index: number) => {
  if (checked) {
    emit('update:selectedItems', [...props.selectedItems, id])
    
    // Auto-fill release quantity with remaining quantity when selected
    const detail = props.details[index]
    const remainingQuantity = getRemainingQuantity(detail)
    if (remainingQuantity > 0) {
      emit('update:releasedQuantity', { index, value: remainingQuantity })
    }
  } else {
    emit('update:selectedItems', props.selectedItems.filter(itemId => itemId !== id))
  }
}
</script>

<template>
  <div class="overflow-hidden">
    <Table>
      <TableHeader class="bg-gray-100 dark:bg-gray-800">
        <TableRow>
          <TableHead class="w-[40px] border-r text-xs text-center">Release</TableHead>
          <TableHead class="w-[140px] border-r text-xs">Release Qty</TableHead>
          <TableHead class="w-[140px] border-r text-xs">Quantity</TableHead>
          <TableHead class="w-[100px] border-r text-xs">Unit</TableHead>
          <TableHead class="text-xs">Description</TableHead>
          <TableHead class="w-[100px] text-xs text-center">Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="(detail, index) in details"
          :key="index"
          class="border-b"
          :class="{
            'bg-green-50 dark:bg-green-900/20': isFullyReleased(detail)
          }"
        >
        <TableCell class="border-r text-center align-middle">
          <div class="flex justify-center items-center w-full">
            <!-- Hide checkbox for fully released items -->
            <div v-if="isFullyReleased(detail)" class="text-green-600 flex justify-center items-center w-full">
              <PackageCheck class="h-5 w-5" />
            </div>
            <div v-else class="flex justify-center items-center w-full">
              <Checkbox 
                :id="'select-' + index"
                :checked="selectedItems.includes(detail.id)"
                @update:checked="(checked) => toggleItemSelection(detail.id, checked, index)"
                class="h-4 w-4 mr-3"
              />
            </div>
          </div>
        </TableCell>

          <TableCell class="border-r p-2">
            <!-- Show disabled input for fully released items -->
            <div v-if="isFullyReleased(detail)" class="text-center text-green-600 font-medium">
              Completed
            </div>
            <div v-else>
              <Input
                :id="'released-' + index"
                type="number"
                :modelValue="''"
                @update:modelValue="(value) => {
                  // Handle empty string as 0
                  const numValue = value === '' ? 0 : Number(value)
                  const maxValue = getRemainingQuantity(detail)
                  const newValue = Math.min(Math.max(0, numValue), maxValue)
                  emit('update:releasedQuantity', { index, value: newValue })
                }"
                :max="getRemainingQuantity(detail)"
                min="0"
                step="1"
                :disabled="!selectedItems.includes(detail.id)"
                placeholder="0"
                class="border border-gray-300 rounded text-xs h-8 w-full"
                :class="{
                  'border-green-500 focus:ring-green-500': detail.released_quantity === getRemainingQuantity(detail) && detail.released_quantity > 0
                }"
              />
            </div>
          </TableCell>

          <TableCell class="border-r p-2">
            <p class="font-medium">{{ detail.quantity }}</p>
            <p v-if="detail.released_quantity > 0 && !isFullyReleased(detail)" class="text-xs text-blue-600">
              ({{ detail.released_quantity }} released)
            </p>
          </TableCell>

          <TableCell class="border-r p-2">
            <p>{{ detail.unit }}</p>
          </TableCell>

          <TableCell class="p-2">
            <p :class="{ 'line-through text-gray-400': isFullyReleased(detail) }">
              {{ detail.item_description }}
            </p>
          </TableCell>
          
          <TableCell class="p-2 text-center">
            <!-- Show different status badges -->
            <div v-if="isFullyReleased(detail)" class="text-green-600 font-medium" title="Item fully released">
              <PackageCheck class="h-4 w-4 inline" />
              <span class="text-xs ml-1">Completed</span>
            </div>
            <div v-else-if="detail.item_id && !isFullyReleased(detail)" class="text-green-600" title="Linked to inventory - will auto-deduct">
              <Package class="h-4 w-4 inline" />
              <span class="text-xs ml-1">Linked</span>
            </div>
            <div v-else class="text-red-500" title="Not linked to inventory - cannot release">
              <PackageX class="h-4 w-4 inline" />
              <span class="text-xs ml-1">Not Linked</span>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>