<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { CirclePlus , ListChecks } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next'
import {
  Table,
  TableCaption,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request To Order', href: '/request-to-order' },
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
})

function getStatusVariant(status: string) {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'secondary' 
    case 'approved':
      return 'success'
    case 'rejected':
      return 'destructive'
    default:
      return 'default'
  }
}

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
  <Head title="Request To Order List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-xl font-bold">Request To Order List</h1>
          <p class="text-sm">Created Order List</p>
        </div>
        <div class="space-x-2 items-center">
          <Button variant="outline" @click="goToList" class="h-8"><ListChecks /> List to Order</Button>
          <Button @click="goToCreate" class="h-8"><CirclePlus />Create New Order</Button>
        </div>
      </div>

      <!-- Table -->
      <div v-if="requests.length > 0" class="rounded-lg border">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Order No</TableHead>
              <TableHead>Date Request</TableHead>
              <TableHead>Notes</TableHead>
              <TableHead class="w-[100px]">Status</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="request in props.requests" :key="request.id" @click="viewRequest(request.id)" class="cursor-pointer hover:underline">
              <TableCell class="font-medium">{{ request.order_no }}</TableCell>
              <TableCell>{{ formatDate(request.order_date) }}</TableCell>
              <TableCell>{{ request.notes }}</TableCell>
              <TableCell>
                <Badge
                  :variant="getStatusVariant(request.status)"
                  class="capitalize"
                >
                  {{ request.status }}
                </Badge>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-else-if="requests" class="flex h-48 flex-col items-center justify-center rounded-xl border">
        <FileText class="h-8 w-8 text-muted-foreground" />
        <p class="mt-2 text-sm text-muted-foreground">No request to order found</p>
        <p class="text-xs text-muted-foreground">On process requests will appear here</p>
      </div>
    </div>
  </AppLayout>
</template>
