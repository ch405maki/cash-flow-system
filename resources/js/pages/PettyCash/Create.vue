<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import PettyCashForm from '@/components/pettyCash/PettyCashForm.vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash Voucher', href: '#' },
]
</script>

<template>
  <Head title="Petty Cash Voucher" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <h1 class="text-xl font-bold">Create Petty Cash Voucher</h1>
      <div class="rounded-xl border p-4">
        <PettyCashForm />
      </div>
    </div>
  </AppLayout>
</template>
