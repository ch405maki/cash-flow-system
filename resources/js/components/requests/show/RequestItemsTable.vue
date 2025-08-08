<script setup lang="ts">
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'

const props = defineProps<{ details: any[] }>()
</script>

<template>
  <Table>
    <TableHeader class="bg-muted">
      <TableRow>
        <TableHead class="border p-2 w-10">#</TableHead>
        <TableHead class="border p-2">Quantity</TableHead>
        <TableHead class="border p-2">Unit</TableHead>
        <TableHead class="border p-2">Item Description</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="(detail, index) in details" :key="detail.id">
        <TableCell class="border p-2">{{ index + 1 }}</TableCell>
        <TableCell class="border p-2">
          <span class="text-zinc-600">{{ +detail.quantity + +detail.released_quantity }} Request</span>
          (Released: {{ detail.released_quantity }})
        </TableCell>
        <TableCell class="border p-2">{{ detail.unit }}</TableCell>
        <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
