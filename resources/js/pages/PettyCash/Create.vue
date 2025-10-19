<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import PettyCashForm from '@/components/pettyCash/PettyCashForm.vue'
import { type BreadcrumbItem } from '@/types'
import PageHeader from '@/components/PageHeader.vue';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash Voucher', href: '#' },
]
</script>

<template>
  <Head title="Petty Cash Voucher" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <PageHeader 
        title="Create Petty Cash Voucher" 
        subtitle="Fill out petty cash voucher details and items"
      />
      <div class="rounded-xl border p-4">
        <PettyCashForm />
      </div>
    </div>
  </AppLayout>
</template>
