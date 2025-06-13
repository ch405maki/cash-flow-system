<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import RequestForm from '@/components/requests/RequestForm.vue';
import RejectedRequestTable from '@/components/requests/RejectedRequestTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ref, watch } from 'vue'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Filter, PlusCircle } from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Request',
    href: '/request',
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
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Rejected Requests</h1>
      </div>

      <RejectedRequestTable :requests="requests" />
    </div>
  </AppLayout>
</template>