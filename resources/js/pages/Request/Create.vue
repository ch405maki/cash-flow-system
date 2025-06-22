<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import RequestForm from '@/components/requests/RequestForm.vue';
import RequestTable from '@/components/requests/RequestTable.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
  {
    title: 'Request',
    href: '/request',
  },
  {
    title: 'Create',
    href: '/request/create',
  },
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
        <RequestForm :departments="departments" :auth-user="authUser" />
    </div>
  </AppLayout>
</template>
