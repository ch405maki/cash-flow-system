<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import PettyCashEditForm from '@/components/pettyCash/PettyCashEditForm.vue'
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
  pettyCash: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash Voucher', href: '#' },
  { title: `Edit ${props.pettyCash.pcv_no}`, href: '#' }
]
</script>

<template>
  <Head :title="`Edit Petty Cash - ${props.pettyCash.pcv_no}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <PageHeader 
        title="Edit Petty Cash Voucher" 
        :subtitle="`Update details for PCV ${props.pettyCash.pcv_no}`"
      />
      <PettyCashEditForm :petty-cash="props.pettyCash" />
    </div>
  </AppLayout>
</template>
