<script setup lang="ts">
import {
  Table, TableBody, TableCaption, TableCell,
  TableHead, TableHeader, TableRow,
} from '@/components/ui/table'

defineProps<{
  details: Array<{
    id: number
    quantity: number
    unit: string
    item_description: string
    unit_price: number
    amount: number
  }>
  totalAmount: number
  formatCurrency: (v: number) => string
}>()
</script>

<template>
  <Table>
    <TableCaption>
      Nothing Follows
    </TableCaption>
    <TableHeader>
      <TableRow>
        <TableHead>Quantity</TableHead>
        <TableHead>Unit/S</TableHead>
        <TableHead>Description</TableHead>
        <TableHead class="text-right">Unit Price</TableHead>
        <TableHead class="text-right">Amount</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="item in details" :key="item.id">
        <TableCell>{{ item.quantity }}</TableCell>
        <TableCell>{{ item.unit }}</TableCell>
        <TableCell>{{ item.item_description }}</TableCell>
        <TableCell class="text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
        <TableCell class="text-right font-medium">{{ formatCurrency(item.amount) }}</TableCell>
      </TableRow>
      <TableRow>
        <TableCell colspan="4" class="text-right font-medium">Total</TableCell>
        <TableCell class="text-right font-bold">{{ formatCurrency(totalAmount) }}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>