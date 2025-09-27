<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import PettyCashEditForm from '@/components/pettyCash/PettyCashEditForm.vue'

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
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <h1 class="text-xl font-bold">Edit Petty Cash Voucher</h1>
      <div class="rounded-xl border p-4">
        <PettyCashEditForm :petty-cash="props.pettyCash" />
      </div>
    </div>
  </AppLayout>
</template>
