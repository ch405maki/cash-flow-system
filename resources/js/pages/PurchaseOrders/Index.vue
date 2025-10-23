<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PurchaseOrderTable from '@/components/purchaseOrder/PurchaseOrderTable.vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Purchase Orders',
    href: '/request/create',
  },
];

const props = defineProps<{
  purchaseOrders: any,
  filters: {
    status?: string
  }
}>();
</script>

<template>
  <Head title="Purchase Orders" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <PageHeader 
        title="Purchase Orders" 
        subtitle="Monitoring submitted purchase orders"
      />

      <PurchaseOrderTable :purchase-orders="purchaseOrders" />

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