<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { defineProps } from 'vue';
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

const getFullName = (user: any) =>
  `${user.first_name} ${user.middle_name} ${user.last_name}`;

function viewVoucherReport(id: number) {
  // Open in new tab
  window.open(route('vouchers.report', id), '_blank');
  
  // Or download directly (alternative approach)
  // router.visit(route('vouchers.report', id), {
  //   method: 'get',
  //   preserveScroll: true,
  //   onSuccess: () => {},
  // });
}
</script>

<template>
  <div class="rounded-lg border">
    <Table class="w-full table-auto text-left text-sm">
      <TableHeader>
        <TableRow>
          <TableHead class="px-4 py-2">Voucher No</TableHead>
          <TableHead class="px-4 py-2">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="voucher in vouchers"
          :key="voucher.id"
          class="border-t hover:bg-muted/50"
        >
          <TableCell class="px-4 py-2 font-medium">{{ voucher.voucher_no }}</TableCell>
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
      </TableBody>
    </Table>
  </div>
</template>