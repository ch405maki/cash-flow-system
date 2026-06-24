<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { CirclePlus , ListChecks, Search, PlusCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { FileText } from 'lucide-vue-next'
import PageHeader from '@/components/PageHeader.vue';
import { Skeleton } from '@/components/ui/skeleton'
import { requestToOrderService } from '@/services/requestToOrderService';
import {
  Table,
  TableCaption,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'
import StatusBadge from '@/components/StatusBadge.vue';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Purchase Request', href: '/request-to-order' },
]

const props = defineProps<{
  pageType?: string
}>()

const requests = ref<any[]>([])
const forOrders = ref<any[]>([])
const authUser = ref<any>(null)
const loading = ref(true)
const searchQuery = ref('')

const filteredRequests = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  if (!q) return requests.value
  return requests.value.filter(r =>
    r.order_no?.toLowerCase().includes(q) ||
    r.notes?.toLowerCase().includes(q)
  )
})

onMounted(async () => {
  try {
    const response = await requestToOrderService.index(props.pageType || 'index')
    requests.value = response.data.requests || []
    forOrders.value = response.data.forOrders || []
    authUser.value = response.data.authUser
  } catch (error) {
    console.error('Failed to load request to order data:', error)
  } finally {
    loading.value = false
  }
})

function goToList() {
  router.visit('/request-to-order/list-to-order')
}

function goToCreate() {
  router.visit('/request-to-order/create')
}

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`)
}

const pageConfig = computed(() => {
  const configs: Record<string, { title: string; subtitle: string; showActions: boolean; emptyTitle: string; emptyDesc: string }> = {
    index: {
      title: 'Purchase Request List',
      subtitle: 'View Request information and status',
      showActions: true,
      emptyTitle: 'No request to order found',
      emptyDesc: 'On process requests will appear here',
    },
    pending: {
      title: 'Pending Orders',
      subtitle: 'Purchase requests waiting to be processed',
      showActions: false,
      emptyTitle: 'No pending orders',
      emptyDesc: 'New orders will appear here',
    },
    'for-approval': {
      title: 'For Approval',
      subtitle: 'Purchase requests pending review',
      showActions: false,
      emptyTitle: 'No pending request to order found',
      emptyDesc: 'Request to order for approval from Property Custodian will appear here',
    },
    'on-process': {
      title: 'On Process',
      subtitle: 'Purchase requests currently being processed',
      showActions: false,
      emptyTitle: 'No request to order found',
      emptyDesc: 'On process requests will appear here',
    },
    approved: {
      title: 'Approved Request',
      subtitle: 'Approved purchase requests ready for purchasing',
      showActions: false,
      emptyTitle: 'No approved request found',
      emptyDesc: 'Approved requests for purchasing will appear here',
    },
  }
  return configs[props.pageType || 'index'] || configs.index
})

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}
</script>

<template>
  <Head :title="pageConfig.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div v-if="loading" class="space-y-4">
        <Skeleton class="h-8 w-64" />
        <Skeleton class="h-64 w-full" />
      </div>

      <template v-else>
        <div class="flex justify-between items-center gap-4">
          <PageHeader 
            :title="pageConfig.title" 
            :subtitle="pageConfig.subtitle"
          />
          <div class="flex items-center gap-2">
            <div class="relative">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input v-model="searchQuery" placeholder="Search..." class="pl-8 h-8 w-64" />
            </div>
            <Button v-if="props.pageType === 'approved' && authUser?.role === 'purchasing'" size="sm" @click="router.visit('/purchase-order/create')"><PlusCircle />Create PO</Button>
            <div v-if="pageConfig.showActions" class="flex items-center gap-2">
              <Button variant="outline" @click="goToList" class="h-8"><ListChecks /> List to Purchase</Button>
              <Button @click="goToCreate" class="h-8"><CirclePlus />Create New Purchase</Button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div v-if="filteredRequests.length > 0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[240px]">Order No</TableHead>
                <TableHead class="w-[140px]">Date Request</TableHead>
                <TableHead class="w-[540px]">Notes</TableHead>
                <TableHead class="w-[140px] text-right">Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="request in filteredRequests" :key="request.id" class="cursor-pointer hover:underline">
                <TableCell class="font-medium" @click="viewRequest(request.id)">{{ request.order_no }}</TableCell>
                <TableCell @click="viewRequest(request.id)">{{ formatDate(request.order_date) }}</TableCell>
                <TableCell @click="viewRequest(request.id)">{{ request.notes }}</TableCell>
                <TableCell class="text-right" @click="viewRequest(request.id)">
                  <StatusBadge :status="request.status" show-icon />
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div v-else-if="!loading" class="flex h-48 flex-col items-center justify-center rounded-xl border">
          <FileText class="h-8 w-8 text-muted-foreground" />
          <p class="mt-2 text-sm text-muted-foreground">{{ pageConfig.emptyTitle }}</p>
          <p class="text-xs text-muted-foreground">{{ pageConfig.emptyDesc }}</p>
        </div>
      </template>
    </div>
  </AppLayout>
</template>
