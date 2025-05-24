<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import RequestForm from '@/components/requests/RequestForm.vue';
import RequestTable from '@/components/requests/RequestTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

const breadcrumbs: BreadcrumbItem[] = [
    {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Request To Order',
    href: '/request',
  }
];


function goToCreate() {
  router.visit(`/request-to-order/create`)
}

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
        <h1 class="text-xl font-bold">Request To Order List</h1>
        <Button @click="goToCreate">Create New Order</Button>
      </div>
      <RequestTable :requests="requests"/>
    </div>
  </AppLayout>
</template>
