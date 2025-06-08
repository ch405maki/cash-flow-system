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

// Sort vouchers by date (newest first)
const sortedVouchers = computed(() => {
  return [...props.vouchers].sort((a, b) => {
    // Use created_at if available, otherwise fall back to voucher_date
    const dateA = a.created_at || a.voucher_date;
    const dateB = b.created_at || b.voucher_date;
    return new Date(dateB).getTime() - new Date(dateA).getTime();
  });
});

const getRole = (user: any) => `${user.role} `;

function viewVoucher(id: number) {
  router.get(`/vouchers/${id}/view`);
}

function goToEditVoucher(id: number) {
  router.get(`/vouchers/${id}/edit`);
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(amount);
}
</script>

<template>
  <div class="rounded-lg border">
    <Table class="w-full table-auto text-left text-sm">
      <TableHeader>
        <TableRow>
          <TableHead class="px-4 py-2">Voucher No</TableHead>
          <TableHead class="px-4 py-2">Type</TableHead>
          <TableHead class="px-4 py-2">Created By</TableHead>
          <TableHead class="px-4 py-2">Purpose</TableHead>
          <TableHead class="px-4 py-2">Check Amount</TableHead>
          <TableHead class="px-4 py-2">Payee</TableHead>
          <TableHead class="px-4 py-2">Check Pay to</TableHead>
          <TableHead class="px-4 py-2">Date</TableHead>
          <TableHead class="px-4 py-2 text-center">Status</TableHead>
          <TableHead class="px-4 py-2 text-center">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-for="voucher in sortedVouchers" :key="voucher.id" class="border-t hover:bg-muted/50">
          <TableCell class="px-4 py-2 font-medium">{{ voucher.voucher_no }}</TableCell>
          <TableCell class="px-4 py-2 capitalize">{{ voucher.type }}</TableCell>
          <TableCell class="px-4 py-2">{{ getRole(voucher.user) }}</TableCell>
          <TableCell class="px-4 py-2">{{ voucher.purpose }}</TableCell>
          <TableCell class="px-4 py-2 font-mono tabular-nums">
            {{ formatCurrency(voucher.check_amount) }}
          </TableCell>
          <TableCell class="px-4 py-2">{{ voucher.payee }}</TableCell>
          <TableCell class="px-4 py-2">{{ voucher.check_payable_to }}</TableCell>
          <TableCell class="px-4 py-2">
            {{ new Date(voucher.created_at || voucher.voucher_date).toLocaleDateString() }}
          </TableCell>
          <TableCell class="px-4 py-2 capitalize">
            <span class="inline-block rounded-full px-8 py-0.5 text-xs font-semibold" :class="{
              'bg-yellow-100 text-yellow-800': voucher.status === 'pending',
              'bg-green-100 text-green-800': voucher.status === 'approved',
              'bg-red-100 text-red-800': voucher.status === 'rejected',
            }">
              {{ voucher.status }}
            </span>
          </TableCell>
          <TableCell class="px-4 py-2 space-x-2">
            <Button size="sm" variant="outline" @click="viewVoucher(voucher.id)" class="hover:bg-blue-50">
              <Eye class="h-4 w-4 mr-1" />
              <span>View</span>
            </Button>

            <Button v-if="authUser.role !== 'executive_director'" size="sm" variant="outline"
              @click="goToEditVoucher(voucher.id)" class="hover:bg-green-50">
              <SquarePen class="h-4 w-4 mr-1" />
              <span>Edit</span>
            </Button>

          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>