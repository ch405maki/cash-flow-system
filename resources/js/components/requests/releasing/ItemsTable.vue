<script setup lang="ts">
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Package, PackageX, PackageCheck, AlertCircle } from 'lucide-vue-next'
import { Checkbox } from '@/components/ui/checkbox'

const props = defineProps({
  details: {
    type: Array,
    required: true
  },
  formDetails: {
    type: Array,
    required: true
  },
  selectedItems: {
    type: Array,
    required: true
  },
  inventoryStatus: {
    type: Object,
    required: true
  },
  validationErrors: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'update:selectedItems',
  'update:releasedQuantity',
  'removeDetail'
])

// Get the form detail by id
const getFormDetail = (detailId: number) => {
  return props.formDetails.find((fd: any) => fd.id === detailId)
}

// Check if release quantity exceeds available quantity - use validationErrors from parent
const isQuantityExceeded = (detail: any) => {
  return props.validationErrors[detail.id] || false
}

// Check if item is fully released
const isFullyReleased = (detail: any) => {
  const releasedQty = Number(detail.released_quantity) || 0
  const totalQty = Number(detail.quantity) || 0
  const fullyReleased = releasedQty >= totalQty
  console.log('isFullyReleased:', detail.id, 'released_qty:', releasedQty, 'qty:', totalQty, 'result:', fullyReleased)
  return fullyReleased
}

// Get remaining quantity (what's left to release)
const getRemainingQuantity = (detail: any) => {
  return detail.quantity - detail.released_quantity
}

// Get the remaining quantity for display (what user can still release)
const getDisplayRemaining = (detail: any) => {
  return detail.quantity - detail.released_quantity
}

// Get inventory status for an item
const getInventoryStatusInfo = (detail: any) => {
  const status = props.inventoryStatus[detail.id]
  if (!status) return null
  return status
}

const toggleItemSelection = (id: number, checked: boolean, index: number) => {
  if (checked) {
    emit('update:selectedItems', [...props.selectedItems, id])
    
    // Auto-fill release quantity with remaining quantity when selected
    const detail = props.details[index]
    const remainingQuantity = getRemainingQuantity(detail)
    if (remainingQuantity > 0) {
      emit('update:releasedQuantity', { id, value: remainingQuantity })
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
          <TableHead class="w-[100px] text-xs text-center">Inventory</TableHead>
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
              <PackageCheck class="h-4 w-4 inline" />
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
            <div v-if="isFullyReleased(detail)" class="text-green-600">
              Completed
            </div>
            <div v-else>
              <div class="relative">
                <Input
                  :id="'released-' + index"
                  type="number"
                  :modelValue="getFormDetail(detail.id)?.release_now ?? ''"
                  @update:modelValue="(value) => {
                    // Allow any value - validation will show alert if invalid
                    const numValue = value === '' ? 0 : Number(value)
                    emit('update:releasedQuantity', { id: detail.id, value: numValue })
                  }"
                  min="0"
                  step="1"
                  :disabled="!selectedItems.includes(detail.id)"
                  placeholder="0"
                  class="border border-gray-300 rounded text-xs h-8 w-full"
                  :class="{
                    'border-green-500 focus:ring-green-500': (getFormDetail(detail.id)?.release_now > 0) && (getFormDetail(detail.id)?.release_now === getRemainingQuantity(detail)),
                    'border-red-500 focus:ring-red-500 bg-red-50': isQuantityExceeded(detail)
                  }"
                />
              </div>
              <div v-if="isQuantityExceeded(detail) && selectedItems.includes(detail.id)" class="text-xs text-red-500 mt-1">
                <p class="flex gap-1 items-center"><AlertCircle  class="h-3 w-3 text-red-500" />Exceeds quantity</p>
              </div>
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
            <!-- Inventory Status Display -->
            <div v-if="getInventoryStatusInfo(detail)">
              <div v-if="!getInventoryStatusInfo(detail).has_item_id" class="text-gray-500 text-xs" title="No inventory link">
                <PackageX class="h-4 w-4 inline" />
                <span class="ml-1">Not Linked</span>
              </div>
              <div v-else-if="!getInventoryStatusInfo(detail).exists" class="text-red-600 text-xs" title="Not found in inventory">
                <AlertCircle class="h-4 w-4 inline" />
                <span class="ml-1">Not Found</span>
              </div>
              <div v-else-if="getInventoryStatusInfo(detail).sufficient_for_request" class="text-green-600 text-xs" title="Sufficient stock available">
                <Package class="h-4 w-4 inline" />
                <span class="ml-1">{{ getInventoryStatusInfo(detail).available_quantity }} available</span>
              </div>
              <div v-else class="text-red-600 text-xs" title="Insufficient stock">
                <AlertCircle class="h-4 w-4 inline" />
                <span class="ml-1">Low Stock ({{ getInventoryStatusInfo(detail).available_quantity }})</span>
              </div>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>