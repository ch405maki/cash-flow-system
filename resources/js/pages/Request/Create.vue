<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import RequestForm from '@/components/requests/RequestForm.vue';
import { type BreadcrumbItem } from '@/types';
import { requestService } from '@/services/requestService';
import { Skeleton } from '@/components/ui/skeleton';

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

const departments = ref<any[]>([])
const loading = ref(true)
const authUser = usePage().props.auth.user

const reorderRequest = ref<any>(null)

onMounted(async () => {
  const storedReorder = sessionStorage.getItem('reorderRequest')
  if (storedReorder) {
    reorderRequest.value = JSON.parse(storedReorder)
    sessionStorage.removeItem('reorderRequest')
  }

  try {
    const response = await requestService.createData()
    departments.value = response.data.departments
  } catch (error) {
    console.error('Failed to load create data:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <Head title="Create Request" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <Skeleton v-if="loading" class="h-[600px] w-full" />
      <RequestForm
        v-else
        :departments="departments"
        :auth-user="authUser"
        :reorder-request="reorderRequest"
      />
    </div>
  </AppLayout>
</template>
