<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ReportsTable from '@/components/reports/ReportsTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const breadcrumbs: BreadcrumbItem[] = [
    {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Reports',
    href: '/reports',
  },
];

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
  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Reports</h1>
      </div>
      <ReportsTable :vouchers="vouchers" />

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
