<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'
import { FileText } from 'lucide-vue-next'


defineProps({
  purchaseOrders: {
    type: Object,
    required: true
  }
})

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

function goToPO(id: number) {
  router.visit(`/purchase-orders/${id}`)
}
</script>

<template>
  <div v-if="purchaseOrders.data.length > 0">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>PO Number</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Payee</TableHead>
          <TableHead>Department</TableHead>
          <TableHead>Amount</TableHead>
          <TableHead>Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow @click="goToPO(po.id)" class="hover:cursor-pointer hover:underline" v-for="po in purchaseOrders.data" :key="po.id" title="View Purchase Order">
          <TableCell class="font-medium">{{ po.po_no }}</TableCell>
          <TableCell>{{ formatDate(po.date) }}</TableCell>
          <TableCell>{{ po.payee }}</TableCell>
          <TableCell>{{ po.department.department_name }}</TableCell>
          <TableCell>{{ po.amount.toLocaleString() }}</TableCell>
          <TableCell>
            <Badge class="capitalize">  
              {{ po?.status || 'N/A' }}
            </Badge>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <div v-else="purchaseOrders.data" class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No purchase order found</p>
    <p class="text-xs text-muted-foreground">Purchase order list will appear here</p>
  </div>
</template>