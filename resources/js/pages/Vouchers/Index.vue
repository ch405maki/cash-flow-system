<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import VoucherTable from '@/components/vouchers/VoucherTable.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button'

const breadcrumbs: BreadcrumbItem[] = [
    {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Vouchers',
    href: '/vouchers',
  },
];

function goToCreate() {
  router.visit(`/vouchers/create`)
}

const props = defineProps({
  vouchers: {
    type: Array,
    default: () => [],
    required: true,
  },
  authUser: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head title="Create Voucher" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Vouchers</h1>
        <Button variant="default" size="sm" @click="goToCreate()">
          Create New Voucher
        </Button>
      </div>
      <VoucherTable :vouchers="vouchers" />

      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
          Showing page {{ vouchers.current_page }} of {{ vouchers.last_page }}
        </div>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="!vouchers.prev_page_url"
            @click="goToPage(vouchers.prev_page_url)"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            :disabled="!vouchers.next_page_url"
            @click="goToPage(vouchers.next_page_url)"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
