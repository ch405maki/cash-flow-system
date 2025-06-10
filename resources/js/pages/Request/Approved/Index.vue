<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApprovedRequestTable from '@/components/requests/approved/ApprovedRequestTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Filter, PlusCircle } from 'lucide-vue-next'
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


const breadcrumbs: BreadcrumbItem[] = [
    {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Approved Request',
    href: '/request',
  }
];

const props = defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
})

function goToCreate() {
  router.visit(`/purchase-order/create`)
}

function goToCanvas() {
  router.visit(`/canvas`)
}

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`) 
}

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
<Head title="Approved Request" />

<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Approved Request List</h1>
        <div class="space-x-2">
          <Button variant="outline" size="sm" @click="goToCanvas()" class="h-8">
            <PlusCircle class="h-4 w-4" />
            Create Canvas
          </Button>      
          <Button variant="default" size="sm" @click="goToCreate()" class="h-8">
            <PlusCircle class="h-4 w-4" />
            Create P. O.
          </Button>  
        </div>    
        </div>

      <div class="w-full text-sm border border-border rounded-md">
      <Table>
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
            <TableCell class="text-center">
              <Button size="sm" @click="viewRequest(request.id)">
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
