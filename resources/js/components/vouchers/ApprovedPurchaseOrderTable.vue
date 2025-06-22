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

function goToCreate(poId?: number) {
  const url = poId ? `/vouchers/create?po_id=${poId}` : '/vouchers/create'
  router.visit(url)
}
</script>

<template>
  <div v-if="purchaseOrders.data.length > 0" class="rounded-md border">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>PO Number</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Payee</TableHead>
          <TableHead>Department</TableHead>
          <TableHead>Amount</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Account</TableHead>
          <TableHead class="text-right">
            <Button 
              variant="outline" 
              size="sm"
              @click="goToCreate()"
              class="float-right"
            >
              Create Voucher (No PO)
            </Button>
          </TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="po in purchaseOrders.data" :key="po.id">
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
          <TableCell>{{ po.account.account_title }}</TableCell>
          <TableCell>
            <Button 
              variant="outline" 
              size="sm"
              @click="goToCreate(po.id)"
            >
              Create Voucher
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No purchase order for voucher found</p>
    <p class="text-xs text-muted-foreground">Purchase order for voucher from Purchasing Department will appear here.</p>
</div>
</template>