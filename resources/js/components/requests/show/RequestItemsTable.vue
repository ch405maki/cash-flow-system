<script setup lang="ts">
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'
import { PackageCheck, PackageOpen, PackageX, PackageSearch } from 'lucide-vue-next';

const props = defineProps<{ 
  details: any[],
  inventoryStatus: any 
}>()
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead class="border-r w-10">#</TableHead>
        <TableHead class="border-r">Quantity</TableHead>
        <TableHead class="border-r">Unit</TableHead>
        <TableHead>Item Description</TableHead>
        <TableHead class="border-l">Inventory Status</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="(detail, index) in details" :key="detail.id">
        <TableCell class="border-r">{{ index + 1 }}</TableCell>
        <TableCell class="border-r">
          <span class="text-zinc-600">{{ detail.quantity }} Request</span>
          (Released: {{ detail.released_quantity }})
        </TableCell>
        <TableCell class="border-r">{{ detail.unit }}</TableCell>
        <TableCell>{{ detail.item_description }}</TableCell>
        <TableCell class="border-l">
          <div v-if="inventoryStatus[detail.id]">
            <div v-if="!inventoryStatus[detail.id].has_item_id" class="text-gray-500 text-sm">
              <PackageX class="h-4 w-4 inline" />
              <span class="text-xs ml-1">Not linked</span>
            </div>
            <div v-else-if="!inventoryStatus[detail.id].exists_in_inventory" class="text-red-600 text-sm">
              <PackageSearch class="h-4 w-4 inline" />
              <span class="text-xs ml-1">Not found in inventory</span>
            </div>
            <div v-else-if="inventoryStatus[detail.id].has_sufficient" class="text-green-600 text-sm">
              <PackageCheck class="h-4 w-4 inline" />
              <span class="text-xs ml-1">{{ inventoryStatus[detail.id].available_quantity }} Available</span>
            </div>
            <div v-else class="text-red-600 text-sm">
              <PackageOpen class="h-4 w-4 inline" />
              <span class="text-xs ml-1">Out of stock</span>
            </div>
          </div>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>