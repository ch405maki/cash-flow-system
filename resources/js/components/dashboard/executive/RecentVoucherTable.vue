<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { formatDate, formatCurrency } from '@/lib/utils';
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'
import { 
  FileText,
} from 'lucide-vue-next'

defineProps<{
  vouchers: Array<any>;
}>();

function formatDisplayDate(dateString: string): string {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function viewVoucher(id: number) {
  router.get(`/vouchers/${id}/view`);
}
</script>

<template>
  <div v-if="vouchers.length > 0"  class="border rounded-xl mt-4 overflow-auto">
    <div class="px-4 py-3 bg-muted/40">
      <h2 class="text-base font-semibold">Recent Voucher For Approval</h2>
    </div>

    <Table>
        <TableHeader>
            <TableRow>
            <TableHead class="px-4 py-2">Voucher No</TableHead>
            <TableHead class="px-4 py-2">Type</TableHead>
            <TableHead class="px-4 py-2">Check Amount</TableHead>
            <TableHead class="px-4 py-2">Payee</TableHead>
            <TableHead class="px-4 py-2">Check Pay to</TableHead>
            <TableHead class="px-4 py-2">Date</TableHead>
            <TableHead class="px-4 py-2 text-right">Status</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow @click="viewVoucher(voucher.id)" v-for="voucher in vouchers" :key="voucher.id" class="border-t hover:bg-muted/50 hover:cursor-pointer" title="View Voucher">
                <TableCell class="px-4 py-4 font-medium">{{ voucher.voucher_no }}</TableCell>
                <TableCell class="px-4 py-4 capitalize">{{ voucher.type }}</TableCell>
                <TableCell class="px-4 py-4 font-mono tabular-nums">
                    {{ formatCurrency(voucher.check_amount) }}
                </TableCell>
                <TableCell class="px-4 py-4">{{ voucher.payee }}</TableCell>
                <TableCell class="px-4 py-4">{{ voucher.check_payable_to }}</TableCell>
                <TableCell class="px-4 py-4">
                    {{ formatDisplayDate(voucher.created_at || voucher.voucher_date) }}
                </TableCell>
                <TableCell class="px-4 py-4 capitalize text-right">
                    <span class="inline-block rounded-full px-3 py-0.5 text-xs font-semibold" :class="{
                    'bg-yellow-100 text-yellow-800': voucher.status === 'draft',
                    'bg-green-100 text-green-800': voucher.status === 'forCheck',
                    'bg-red-100 text-red-800': voucher.status === 'rejected',
                    'bg-blue-100 text-blue-800': voucher.status === 'forEOD',
                    }">
                    {{ voucher.status }}
                    </span>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
  </div>


  <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No recent voucher found</p>
    <p class="text-xs text-muted-foreground">Requests for voucher approval from accounting will appear here</p>
  </div>
</template>
