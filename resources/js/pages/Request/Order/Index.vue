<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

import { Button } from '@/components/ui/button'
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
</script>

<template>
  <Head title="Request To Order List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Request To Order List</h1>
        <div class="space-x-2">
          <Button @click="goToList">List to Order</Button>
          <Button @click="goToCreate">Create New Order</Button>
        </div>
      </div>

      <!-- Table -->
      <h1 class="text-lg">Created Order List</h1>
      <div class="rounded-lg border">
        <Table>
          <TableCaption>-------- Created Orders --------</TableCaption>
          <TableHeader>
            <TableRow>
              <TableHead>Order No</TableHead>
              <TableHead>Date Request</TableHead>
              <TableHead>Notes</TableHead>
              <TableHead class="w-[100px]">Status</TableHead>
              <TableHead class="text-center">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="request in props.requests" :key="request.id">
              <TableCell class="font-medium">{{ request.order_no }}</TableCell>
              <TableCell>{{ new Date(request.order_date).toLocaleDateString() }}</TableCell>
              <TableCell>{{ request.notes }}</TableCell>
              <TableCell>
                <Badge
                  :variant="getStatusVariant(request.status)"
                  class="capitalize"
                >
                  {{ request.status }}
                </Badge>
              </TableCell>
              <TableCell class="text-center">
                <Button size="sm" variant="outline" @click="viewRequest(request.id)">
                  View
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>
