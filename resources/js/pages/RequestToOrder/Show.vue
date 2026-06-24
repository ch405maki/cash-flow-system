<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { formatDate } from '@/lib/utils'
import PageHeader from '@/components/PageHeader.vue'
import { Skeleton } from '@/components/ui/skeleton'
import { useToast } from 'vue-toastification'
import { requestToOrderService } from '@/services/requestToOrderService'
import ActionBar from '@/components/requestToOrder/show/ActionBar.vue'
import ReleaseHistory from '@/components/requestToOrder/show/ReleaseHistory.vue'
import PrintableSection from '@/components/printables/OrderPrint.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import {
  Table,
  TableCaption,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'

const props = defineProps<{ orderId: number }>()

const toast = useToast()
const loading = ref(true)
const requestOrder = ref<any>(null)
const authUser = ref<any>(null)
const processing = ref(false)
const printableComponent = ref<InstanceType<typeof PrintableSection> | null>(null)
const showApproveModal = ref(false)
const showForEODModal = ref(false)

onMounted(async () => {
  try {
    const response = await requestToOrderService.showData(props.orderId)
    requestOrder.value = response.data.requestOrder
    authUser.value = response.data.authUser
  } catch (error) {
    console.error('Failed to load order:', error)
  } finally {
    loading.value = false
  }
})

async function handleApprove(password: string) {
  processing.value = true
  try {
    await requestToOrderService.approve(props.orderId, password)
    toast.success('Request approved successfully')
    showApproveModal.value = false
    const response = await requestToOrderService.showData(props.orderId)
    requestOrder.value = response.data.requestOrder
  } catch (error: any) {
    toast.error(error.response?.data?.errors?.password || 'Something went wrong.')
  } finally {
    processing.value = false
  }
}

async function handleForEod(password: string) {
  processing.value = true
  try {
    await requestToOrderService.forEod(props.orderId, password)
    toast.success('Request sent for EOD approval successfully')
    showForEODModal.value = false
    const response = await requestToOrderService.showData(props.orderId)
    requestOrder.value = response.data.requestOrder
  } catch (error: any) {
    toast.error(error.response?.data?.errors?.password || 'Something went wrong.')
  } finally {
    processing.value = false
  }
}

function handleReceiveItems() {
  if (requestOrder.value) {
    router.visit(`/request-to-order/${requestOrder.value.id}/release`)
  }
}

function handleCreatePo() {
  router.visit('/purchase-order/create')
}

function handleCanvasSuccess() {
  requestToOrderService.showData(props.orderId).then(response => {
    requestOrder.value = response.data.requestOrder
  })
}

function printArea() {
  printableComponent.value?.printArea()
}

function goBack() {
  window.history.back()
}

const breadcrumbs = computed(() => [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request To Order', href: '/request-to-order' },
  { title: `Order ${requestOrder.value?.order_no || ''}`, href: '#' },
])
</script>

<template>
  <Head :title="`Order ${requestOrder?.order_no || '...'}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="loading" class="p-4 space-y-4">
      <Skeleton class="h-8 w-64" />
      <Skeleton class="h-32 w-full" />
      <Skeleton class="h-64 w-full" />
    </div>

    <template v-else-if="requestOrder">
      <div class="p-4 space-y-4">
        <div class="flex justify-between items-center">
          <PageHeader title="Request To Order Details" subtitle="View order information, status, and item specifications" />
          <ActionBar
            :order="requestOrder"
            :auth-user="authUser"
            :processing="processing"
            :show-approve-modal="showApproveModal"
            :show-for-eod-modal="showForEODModal"
            @update:show-approve-modal="showApproveModal = $event"
            @update:show-for-eod-modal="showForEODModal = $event"
            @approve="handleApprove"
            @for-eod="handleForEod"
            @receive-items="handleReceiveItems"
            @create-po="handleCreatePo"
            @canvas-success="handleCanvasSuccess"
            @print="printArea"
            @back="goBack"
          />
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
          <Table>
            <TableBody>
              <TableRow>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-48 uppercase">Order No:</TableCell>
                <TableCell class="p-2 uppercase border-r w-xl">{{ requestOrder.order_no }}</TableCell>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-40 uppercase">Order Date:</TableCell>
                <TableCell class="p-2 w-40">{{ formatDate(requestOrder.order_date) }}</TableCell>
              </TableRow>
              <TableRow>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-48 uppercase">Notes:</TableCell>
                <TableCell class="p-2 uppercase border-r w-xl">{{ requestOrder.notes }}</TableCell>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-40 uppercase">Status:</TableCell>
                <TableCell class="p-2 w-40">
                  <StatusBadge :status="requestOrder.status" show-icon size="md" />
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <PageHeader title="Order Items Details" />
          <Table>
            <TableCaption>Items to purchase</TableCaption>
            <TableHeader class="bg-muted">
              <TableRow>
                <TableHead class="border-r p-2 w-10">#</TableHead>
                <TableHead class="border-r p-2">Item Description</TableHead>
                <TableHead class="border-r p-2">Quantity</TableHead>
                <TableHead class="border-r">Unit</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(detail, index) in requestOrder.details" :key="detail.id">
                <TableCell class="border-b p-2">{{ index + 1 }}</TableCell>
                <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
                <TableCell class="border p-2">{{ detail.quantity }}</TableCell>
                <TableCell class="border p-2">{{ detail.unit }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <ReleaseHistory v-if="requestOrder.details.some((d: any) => d.releases?.length > 0)" :details="requestOrder.details" />

          <PrintableSection ref="printableComponent" :request-order="requestOrder" />
        </div>
      </div>

    </template>
  </AppLayout>
</template>
