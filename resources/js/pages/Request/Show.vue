<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import PrintableSection from '@/components/printables/RequestPrint.vue'
import RequestDetailsTable from '@/components/requests/show/RequestDetailsTable.vue'
import RequestItemsTable from '@/components/requests/show/RequestItemsTable.vue'
import RequestActions from '@/components/requests/show/RequestActions.vue'
import { ref } from 'vue'
import type { BreadcrumbItem } from '@/types'
import { Button } from '@/components/ui/button'
import { Printer } from 'lucide-vue-next'

const props = defineProps<{ 
    request: any, 
    user: any 
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
  { title: `${props.request.request_no}`, href: '' },
]

const printableComponent = ref<InstanceType<typeof PrintableSection>>()

function printArea() {
  printableComponent.value?.printArea()
}
</script>

<template>
  <Head title="Request Details" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <div class="flex justify-between items-center">
        <h1 class="text-lg font-semibold">Request Details</h1>
        <div class="flex items-center space-x-2">
          <RequestActions
                :request="request"
                :user="user"
                @print-list="printArea"
            />
        </div>
      </div>

      <RequestDetailsTable :request="request" />
      <h2 class="text-lg font-semibold my-4">Items</h2>
      <RequestItemsTable :details="request.details" />

      <PrintableSection ref="printableComponent" :request="request" :user="user" />
    </div>
  </AppLayout>
</template>
