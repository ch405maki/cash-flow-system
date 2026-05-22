<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'
import { CirclePlus , ListChecks } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next'
import PageHeader from '@/components/PageHeader.vue';
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

function goToList() {
  router.visit('/request-to-order/list')
}

function goToCreate() {
  router.visit('/request-to-order/create')
}

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`)
}

const props = defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
  forOrders: {
    type: Array,
    default: () => [],
  },
  authUser: {
    type: Array,
    default: () => [],
  },
  pageType: {
    type: String,
    default: 'index',
  },
})

const pageConfig = computed(() => {
  const configs = {
    index: {
      title: 'Purchase Request List',
      subtitle: 'View Request information and status',
      showActions: true,
      emptyTitle: 'No request to order found',
      emptyDesc: 'On process requests will appear here',
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
  }
  return configs[props.pageType] || configs.index
})

function goToShowRequest(requestId: number) {
  router.get(`/request/show/${requestId}`)
}

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
      <div class="flex justify-between items-center">
        <PageHeader 
          :title="pageConfig.title" 
          :subtitle="pageConfig.subtitle"
        />
        <div v-if="pageConfig.showActions" class="space-x-2 items-center">
          <Button variant="outline" @click="goToList" class="h-8"><ListChecks /> List to Purchase</Button>
          <Button @click="goToCreate" class="h-8"><CirclePlus />Create New Purchase</Button>
        </div>
      </div>

      <!-- Table -->
      <div v-if="requests.length > 0">
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
            <TableRow v-for="request in props.requests" :key="request.id" @click="viewRequest(request.id)" class="cursor-pointer hover:underline">
              <TableCell class="font-medium">{{ request.order_no }}</TableCell>
              <TableCell>{{ formatDate(request.order_date) }}</TableCell>
              <TableCell>{{ request.notes }}</TableCell>
              <TableCell class="text-right">
                <StatusBadge
                  :status="request.status"
                  show-icon
                />
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-else-if="requests" class="flex h-48 flex-col items-center justify-center rounded-xl border">
        <FileText class="h-8 w-8 text-muted-foreground" />
        <p class="mt-2 text-sm text-muted-foreground">{{ pageConfig.emptyTitle }}</p>
        <p class="text-xs text-muted-foreground">{{ pageConfig.emptyDesc }}</p>
      </div>
    </div>
  </AppLayout>
</template>
