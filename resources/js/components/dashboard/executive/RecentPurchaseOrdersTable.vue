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
import StatusBadge from '@/components/StatusBadge.vue';


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
  <div class="overflow-auto">
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
          <TableCell>
            <StatusBadge 
                :status="po.status"
                show-icon
                size="md" 
              />
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
