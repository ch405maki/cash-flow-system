<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'

defineProps<{
  recentPurchaseOrders: Array<any>;
}>();

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  });
}

function goToPO(id: number) {
  router.visit(`/purchase-orders/${id}`)
}
</script>

<template>
  <div class="border rounded-xl mt-4 overflow-auto">
    <div class="px-4 py-3 bg-muted/40">
      <h2 class="text-base font-semibold">Recent Purchase Orders</h2>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="text-left">PO No</TableHead>
          <TableHead class="text-left">Date</TableHead>
          <TableHead class="text-left">Payee</TableHead>
          <TableHead class="text-left">Amount</TableHead>
          <TableHead class="text-left">Status</TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow
          v-for="po in recentPurchaseOrders"
          :key="po.id"
          class="hover:bg-muted/20 transition-colors cursor-pointer"
          @click="goToPO(po.id)"
        >
          <TableCell class="font-medium text-purple-800 hover:underline hover:text-purple-700">
            {{ po.po_no }}
          </TableCell>
          <TableCell>{{ formatDate(po.date) }}</TableCell>
          <TableCell>{{ po.payee }}</TableCell>
          <TableCell>₱{{ po.amount.toLocaleString() }}</TableCell>
          <TableCell class="capitalize">
            <span
              :class="{
                'text-green-600': po.status === 'approved',
                'text-yellow-600': po.status === 'pending',
                'text-red-600': po.status === 'rejected'
              }"
            >
              {{ po.status.replace('_', ' ') }}
            </span>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
