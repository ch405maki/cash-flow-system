<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { SquarePen, FileText, Eye } from 'lucide-vue-next';
import { formatDate, formatCurrency } from '@/lib/utils';
import {
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from '@/components/ui/table'

const props = defineProps<{
  vouchers: Array<any>;
  authUser: {
    role: string;
    [key: string]: any;
  };
  state?: boolean;  
}>();

// Sort vouchers by date (newest first) and filter based on user role
const sortedVouchers = computed(() => {
  let filteredVouchers = [...props.vouchers];
  
  
  // Sort the remaining vouchers
  return filteredVouchers.sort((a, b) => {
    const dateA = a.created_at || a.voucher_date;
    const dateB = b.created_at || b.voucher_date;
    return new Date(dateB).getTime() - new Date(dateA).getTime();
  });
});

const getRole = (user: any) => `${user.role} `;

function viewVoucher(id: number) {
  router.get(`/vouchers/${id}/view`);
}

// Add this helper function to format dates for display
function formatDisplayDate(dateString: string): string {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function formatStatus(status: string): string {
  switch (status) {
    case 'forEOD':
      return 'For EOD Approval';
    case 'forCheck':
      return 'For Check Releasing';
    default:
      return status; 
  }
}
</script>

<template>
  <div v-if="vouchers.length > 0" class="rounded-lg border">
    <Table class="w-full table-auto text-left text-sm">
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
        <TableRow @click="viewVoucher(voucher.id)" v-for="voucher in sortedVouchers" :key="voucher.id" class="border-t hover:bg-muted/50 hover:cursor-pointer" title="View Voucher">
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
    <p class="mt-2 text-sm text-muted-foreground">No pending vouchers for approval found</p>
    <p class="text-xs text-muted-foreground">Vouchers for approval from Director of Finance will appear here</p>
  </div>
</template>