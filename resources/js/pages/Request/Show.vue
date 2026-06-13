<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import PrintableSection from '@/components/printables/RequestPrint.vue'
import DetailsTable from '@/components/request/detail/DetailsTable.vue'
import ItemsTable from '@/components/request/detail/ItemsTable.vue'
import Actions from '@/components/request/detail/Actions.vue'
import ReleasedItemsPrint from '@/components/printables/ReleasedItemsPrint.vue'
import { ref, onMounted } from 'vue'
import type { BreadcrumbItem } from '@/types'
import { requestService } from '@/services/requestService'
import { Skeleton } from '@/components/ui/skeleton'

const props = defineProps<{
    requestId: number
}>()

const request = ref<any>(null)
const inventoryStatus = ref<any>({})
const loading = ref(true)
const user = usePage().props.auth.user

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
  { title: '', href: '' },
]

const printableComponent = ref<InstanceType<typeof PrintableSection>>()
const releasedItemsPrintable = ref<InstanceType<typeof ReleasedItemsPrint>>()

function printArea() {
  printableComponent.value?.printArea()
}

function printReleasedItems() {
  releasedItemsPrintable.value?.printArea()
}

function handleReorder(request: any) {
  sessionStorage.setItem('reorderRequest', JSON.stringify(request))
  router.get('/request/create')
}

function onStatusUpdated(updatedRequest: any) {
  request.value = updatedRequest
}

onMounted(async () => {
  try {
    const response = await requestService.showData(props.requestId)
    request.value = response.data.request
    inventoryStatus.value = response.data.inventoryStatus
    breadcrumbs[2].title = request.value.request_no
  } catch (error) {
    console.error('Failed to load request:', error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <Head title="Request Details" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <div v-if="loading">
        <Skeleton class="h-8 w-64 mb-4" />
        <Skeleton class="h-32 w-full mb-4" />
        <Skeleton class="h-64 w-full" />
      </div>

      <template v-else-if="request">
        <div class="flex justify-between items-center">
          <h1 class="text-lg font-semibold">Request Details</h1>
          <div class="flex items-center space-x-2">
            <Actions
              :request="request"
              :user="user"
              @print-list="printArea"
              @print-released-items="printReleasedItems"
              @reorder="handleReorder"
              @status-updated="onStatusUpdated"
            />
          </div>
        </div>

        <DetailsTable :request="request" />
        <h2 class="text-lg font-semibold my-4">Items</h2>
        <ItemsTable :user="user" :details="request.details" :inventory-status="inventoryStatus" />

        <PrintableSection ref="printableComponent" :request="request" :user="user" />
        <ReleasedItemsPrint ref="releasedItemsPrintable" :request="request" :user="user" />
      </template>
    </div>
  </AppLayout>
</template>
