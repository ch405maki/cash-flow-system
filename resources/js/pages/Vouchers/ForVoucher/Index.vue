<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ApprovedPurchaseOrderTable from '@/components/vouchers/ApprovedPurchaseOrderTable.vue';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Purchase Orders',
    href: '/for-voucher',
  },
];

defineProps({
  purchaseOrders: Object
});
</script>

<template>
  <Head title="Purchase Orders" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Purchase Orders</h1>
        <div class="flex gap-2">
        </div>
      </div>
      

      <ApprovedPurchaseOrderTable :purchase-orders="purchaseOrders" />

      <!-- Pagination -->
      <div class="flex items-center justify-end space-x-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="!purchaseOrders.prev_page_url"
          @click="$inertia.get(purchaseOrders.prev_page_url)"
        >
          Previous
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="!purchaseOrders.next_page_url"
          @click="$inertia.get(purchaseOrders.next_page_url)"
        >
          Next
        </Button>
      </div>
    </div>
  </AppLayout>
</template>