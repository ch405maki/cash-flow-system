<script setup lang="ts">
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'

const props = defineProps<{ details: any[] }>()
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead class="border-r w-10">#</TableHead>
        <TableHead class="border-r">Quantity</TableHead>
        <TableHead class="border-r">Unit</TableHead>
        <TableHead>Item Description</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="(detail, index) in details" :key="detail.id">
        <TableCell class="border-r">{{ index + 1 }}</TableCell>
        <TableCell class="border-r">
          <span class="text-zinc-600">{{ +detail.quantity + +detail.released_quantity }} Request</span>
          (Released: {{ detail.released_quantity }})
        </TableCell>
        <TableCell class="border-r">{{ detail.unit }}</TableCell>
        <TableCell>{{ detail.item_description }}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
