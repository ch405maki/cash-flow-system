<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { FileText } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { router } from '@inertiajs/vue3'
import {
  Empty,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty'

defineProps({
  purchaseOrders: {
    type: Object,
    required: true
  }
})

const handleRowClick = (event: MouseEvent, poId: number) => {
  const target = event.target as HTMLElement;
  if (target.closest('button')) {
    return; 
  }
  goToPO(poId);
};

function goToPO(id: number) {
  router.visit(`/purchase-orders/${id}`)
}

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

function goToCreate(poId?: number) {
  const url = poId ? `/vouchers/create?po_id=${poId}` : '/vouchers/create'
  router.visit(url)
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
        <TableRow 
          class="cursor-pointer hover:underline" 
          v-for="po in purchaseOrders.data"
          :key="po.id"
          @click="handleRowClick($event, po.id)"
        >
          <TableCell class="font-medium">{{ po.po_no }}</TableCell>
          <TableCell>{{ formatDate(po.date) }}</TableCell>
          <TableCell>{{ po.payee }}</TableCell>
          <TableCell>{{ po.department.department_name }}</TableCell>
          <TableCell>{{ po.amount.toLocaleString() }}</TableCell>
          <TableCell>
            <Badge>
              {{ po?.status || 'N/A' }}
            </Badge>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <div v-else>
    <Empty>
      <EmptyHeader>
        <EmptyMedia variant="icon">
          <FileText />
        </EmptyMedia>
        <EmptyTitle>No purchase order for voucher found</EmptyTitle>
        <EmptyDescription>
          Purchase order for voucher from Purchasing Department will appear here.
        </EmptyDescription>
      </EmptyHeader>
    </Empty>
</div>
</template>