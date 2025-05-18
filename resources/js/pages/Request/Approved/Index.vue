<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApprovedRequestTable from '@/components/requests/approved/ApprovedRequestTable.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Approved Request',
    href: '/request',
  }
];

const props = defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
  departments: {
    type: Array,
    default: () => [],
  },
  authUser: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head title="Create Request" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Approved Request List</h1>
      </div>
      <ApprovedRequestTable :requests="requests"/>
    </div>
  </AppLayout>
</template>
