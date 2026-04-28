<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PageHeader from '@/components/PageHeader.vue';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Order Receiving Page',
    href: '#',
  },
];

</script>

<template>
  <Head title="Order Receiving Page" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <PageHeader 
        title="Purchase Orders" 
        subtitle="Monitoring submitted purchase orders"
      />
      
    </div>
  </AppLayout>
</template>