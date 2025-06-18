<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { computed } from 'vue'; // Add computed import
import { router } from '@inertiajs/vue3';
import { SquarePen, Eye } from 'lucide-vue-next';
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
    [key: string]: any; // Allow for other fields
  };
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

function goToEditVoucher(id: number, e: Event) {
  e.stopPropagation(); // Prevent the row click event from firing
  router.get(`/vouchers/${id}/edit`);
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(amount);
}

function formatDate(dateString: string): string {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
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
    case 'for_eod':
      return 'For EOD Approval';
    case 'for_check':
      return 'For Check Releasing';
    default:
      return status; // Keep as-is (e.g., "draft", "approved")
  }
}
</script>

<template>
  <div class="rounded-lg border">
    <Table class="w-full table-auto text-left text-sm">
      <TableHeader>
        <TableRow>
          <TableHead class="px-4 py-2">Voucher No</TableHead>
          <TableHead class="px-4 py-2">Type</TableHead>
          <TableHead class="px-4 py-2">Check Amount</TableHead>
          <TableHead class="px-4 py-2">Payee</TableHead>
          <TableHead class="px-4 py-2">Check Pay to</TableHead>
          <TableHead class="px-4 py-2">Date</TableHead>
          <TableHead class="px-4 py-2 text-center">Status</TableHead>
          <TableHead class="px-4 py-2 "></TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow @click="viewVoucher(voucher.id)" v-for="voucher in sortedVouchers" :key="voucher.id" class="border-t hover:bg-muted/50 hover:cursor-pointer" title="View Voucher">
          <TableCell class="px-4 py-2 font-medium">{{ voucher.voucher_no }}</TableCell>
          <TableCell class="px-4 py-2 capitalize">{{ voucher.type }}</TableCell>
          <TableCell class="px-4 py-2 font-mono tabular-nums">
            {{ formatCurrency(voucher.check_amount) }}
          </TableCell>
          <TableCell class="px-4 py-2">{{ voucher.payee }}</TableCell>
          <TableCell class="px-4 py-2">{{ voucher.check_payable_to }}</TableCell>
          <TableCell class="px-4 py-2">
            {{ formatDisplayDate(voucher.created_at || voucher.voucher_date) }}
          </TableCell>
          <TableCell class="px-4 py-2 capitalize">
            <span class="inline-block rounded-full px-8 py-0.5 text-xs font-semibold" :class="{
              'bg-yellow-100 text-yellow-800': voucher.status === 'draft',
              'bg-green-100 text-green-800': voucher.status === 'for_check',
              'bg-red-100 text-red-800': voucher.status === 'rejected',
              'bg-blue-100 text-blue-800': voucher.status === 'for_eod',
            }">
              {{ formatStatus(voucher.status) }}
            </span>
          </TableCell>
          <TableCell class="px-4 py-2 space-x-2">
            <Button 
              v-if="authUser.role !== 'executive_director'" 
              size="sm" 
              variant="outline"
              @click.stop="goToEditVoucher(voucher.id, $event)" 
              class="hover:bg-green-50"
            >
              <SquarePen class="h-4 w-4 mr-1" />
              <span>Edit</span>
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>