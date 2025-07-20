<script setup lang="ts">
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Trash2 } from 'lucide-vue-next'
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

const toggleItemSelection = (id: number, checked: boolean) => {
  if (checked) {
    emit('update:selectedItems', [...props.selectedItems, id])
  } else {
    emit('update:selectedItems', props.selectedItems.filter(itemId => itemId !== id))
  }
}
</script>

<template>
  <div class="border overflow-hidden">
    <Table>
      <TableHeader class="bg-gray-100 dark:bg-gray-800">
        <TableRow>
          <TableHead class="w-[40px] border-r text-xs text-center">Release</TableHead>
          <TableHead class="w-[100px] border-r text-xs">Release Qty</TableHead>
          <TableHead class="w-[100px] border-r text-xs">Quantity</TableHead>
          <TableHead class="w-[100px] border-r text-xs">Unit</TableHead>
          <TableHead class="border-r text-xs">Description</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="(detail, index) in details"
          :key="index"
          class="border-b"
        >
          <TableCell class="border-r">
            <div class="flex justify-center items-center">
              <Checkbox 
                :id="'select-' + index"
                :checked="selectedItems.includes(detail.id)"
                @update:checked="(checked) => toggleItemSelection(detail.id, checked)"
                class="h-4 mr-4 w-4 border-zinc-600"
              />
            </div>
          </TableCell>

          <TableCell class="border-r p-2">
            <Input
              :id="'released-' + index"
              type="number"
              :modelValue="detail.released_quantity"
              @update:modelValue="(value) => $emit('update:releasedQuantity', { index, value })"
              :max="detail.quantity - detail.released_quantity"
              min="0"
              :disabled="!selectedItems.includes(detail.id)"
              class="border border-gray-300 rounded text-xs h-8 w-full"
            />
          </TableCell>

          <TableCell class="border-r p-2">
            <p>{{ detail.quantity }}</p>
          </TableCell>

          <TableCell class="border-r p-2">
            <p>{{ detail.unit }}</p>
          </TableCell>

          <TableCell class="border-r p-2">
            <p>{{ detail.item_description }}</p>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>