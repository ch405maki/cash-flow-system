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

const getFullName = (user: any) =>
  `${user.first_name} ${user.middle_name} ${user.last_name}`;

function viewVoucherReport(id: number) {
  router.visit(route('vouchers.preview', id));
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(amount);
}

// Optional: Format date for display
function formatDate(dateString: string): string {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}
</script>

<template>
  <div class="rounded-lg border">
    <Table class="w-full table-auto text-left text-sm">
      <TableHeader>
        <TableRow>
          <TableHead class="px-4 py-2">Date</TableHead> <!-- Added date column -->
          <TableHead class="px-4 py-2">Voucher No</TableHead>
          <TableHead class="px-4 py-2">Type</TableHead>
          <TableHead class="px-4 py-2">Purpose</TableHead>
          <TableHead class="px-4 py-2">Check Amount</TableHead>
          <TableHead class="px-4 py-2">Status</TableHead>
          <TableHead class="px-4 py-2">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="voucher in sortedVouchers"
          :key="voucher.id"
          class="border-t hover:bg-muted/50"
        >
          <TableCell class="px-4 py-2">
            {{ formatDate(voucher.created_at || voucher.voucher_date) }}
          </TableCell>
          <TableCell class="px-4 py-2 font-medium">{{ voucher.voucher_no }}</TableCell>
          <TableCell class="px-4 py-2 capitalize">{{ voucher.type }}</TableCell>
          <TableCell class="px-4 py-2">{{ voucher.purpose }}</TableCell>
          <TableCell class="px-4 py-2 font-mono tabular-nums">
            {{ formatCurrency(voucher.check_amount) }}
          </TableCell>
          <TableCell class="px-4 py-2 capitalize">
            <span
              class="inline-block items-center rounded-full px-8 py-0.5 text-xs font-semibold"
              :class="{
                'bg-yellow-100 text-yellow-800': voucher.status === 'pending',
                'bg-green-100 text-green-800': voucher.status === 'paid',
                'bg-red-100 text-red-800': voucher.status === 'rejected',
              }"
            >
              {{ voucher.status }}
            </span>
          </TableCell>
          <TableCell class="px-4 py-2 space-x-2">
            <Button
              size="sm"
              variant="outline"
              @click="viewVoucherReport(voucher.id)"
              class="hover:bg-blue-50"
            >
              <Eye class="h-4 w-4 mr-1"/>
              <span>View Report</span>
            </Button>
          </TableCell>
        </TableRow>
        <TableRow v-if="sortedVouchers.length === 0">
          <TableCell colspan="7" class="px-4 py-6 text-center text-muted-foreground">
            No voucher reports found
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>