<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue'
import PurchaseOrderTable from '@/components/purchaseOrder/PurchaseOrderTable.vue';
import { Button } from '@/components/ui/button';
import { PlusCircle } from 'lucide-vue-next'
import PageHeader from '@/components/PageHeader.vue';
import { Skeleton } from '@/components/ui/skeleton'
import { purchaseOrderService } from '@/services/purchaseOrderService';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Purchase Orders', href: '/purchase-order' },
];

const props = defineProps<{
  filters: { status?: string }
}>();

const loading = ref(true)
const purchaseOrders = ref<any>({ data: [] })

onMounted(async () => {
  try {
    const response = await purchaseOrderService.indexData({ status: props.filters.status })
    purchaseOrders.value = response.data.purchaseOrders
  } catch (error) {
    console.error('Failed to load purchase orders:', error)
  } finally {
    loading.value = false
  }
})

function loadPage(url: string | null) {
  if (!url) return
  const params = new URL(url).searchParams
  const page = params.get('page')
  loading.value = true
  purchaseOrderService.indexData({ status: props.filters.status, page: page || undefined })
    .then(response => {
      purchaseOrders.value = response.data.purchaseOrders
    })
    .catch(console.error)
    .finally(() => { loading.value = false })
}
</script>

<template>
  <Head title="Purchase Orders" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div v-if="loading" class="space-y-4">
        <Skeleton class="h-8 w-64" />
        <Skeleton class="h-64 w-full" />
      </div>

      <template v-else>
        <div class="flex justify-between items-center">
          <PageHeader 
            title="Purchase Orders" 
            subtitle="Monitoring submitted purchase orders"
          />
          <Button v-if="filters.status === 'draft'" size="sm" @click="router.visit('/purchase-order/create')"><PlusCircle />Create PO</Button>
        </div>

        <PurchaseOrderTable :purchase-orders="purchaseOrders" />

        <div class="flex items-center justify-end space-x-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="!purchaseOrders.prev_page_url"
            @click="loadPage(purchaseOrders.prev_page_url)"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            :disabled="!purchaseOrders.next_page_url"
            @click="loadPage(purchaseOrders.next_page_url)"
          >
            Next
          </Button>
        </div>
      </template>
    </div>
  </AppLayout>
</template>
