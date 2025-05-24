<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApprovedRequestTable from '@/components/requests/approved/ApprovedRequestTable.vue';
import { type BreadcrumbItem } from '@/types';

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
</script>

<template>
  <Head title="Approved Request" />

<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Approved Request List</h1>
      </div>

      <!-- ShadCN UI Table -->
      <Table>
        <TableCaption>Approved Requests</TableCaption>
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
  </AppLayout>
</template>
