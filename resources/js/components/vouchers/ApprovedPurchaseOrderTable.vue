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
import { 
  FileText,
} from 'lucide-vue-next'

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

function createVoucher(purchaseOrder: any) {
  // Navigate to voucher create page with PO data
  router.visit('/vouchers/for-voucher/createForVoucher', {
    method: 'get',
    data: {
      po_id: purchaseOrder.id,
      payee: purchaseOrder.payee,
      check_payable_to: purchaseOrder.check_payable_to,
      purpose: purchaseOrder.purpose,
      amount: purchaseOrder.amount,
      department_id: purchaseOrder.department_id,
      account_id: purchaseOrder.account_id,
      // Include any other fields you want to pre-populate
    }
  })
}
</script>

<template>
  <div class="rounded-md border">
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
          <TableHead>Actions</TableHead>
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
                @click="createVoucher(po)"
              >
                Create Voucher
              </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>