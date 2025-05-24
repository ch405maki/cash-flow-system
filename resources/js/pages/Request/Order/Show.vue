<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
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

const { requestOrder } = usePage().props

function goBack() {
  window.history.back()
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
</script>

<template>
  <Head :title="`Order ${requestOrder.order_no}`" />

  <AppLayout :breadcrumbs="[
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Request To Order', href: '/request-to-order' },
    { title: `Order ${requestOrder.order_no}`, href: '#' }
  ]">
    <div class="p-4 space-y-4">

      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Request To Order Details</h1>
        
        <Button @click="goBack" variant="outline">Back</Button>
      </div>

      <div class="space-y-2">
        <p><strong>Order No:</strong> {{ requestOrder.order_no }}</p>
        <p><strong>Order Date:</strong> {{ new Date(requestOrder.order_date).toLocaleDateString() }}</p>
        <p><strong>Notes:</strong> {{ requestOrder.notes }}</p>
        <p>
          <strong>Status:</strong>
          <Badge :variant="getStatusVariant(requestOrder.status)" class="capitalize">
            {{ requestOrder.status }}
          </Badge>
        </p>
      </div>

      <h2 class="text-xl font-semibold mt-6 mb-2">Order Details</h2>
      <Table>
        <TableCaption>Items in this order</TableCaption>
        <TableHeader>
            <TableRow>
            <TableHead>Item Description</TableHead>
            <TableHead>Quantity</TableHead>
            <TableHead>Unit</TableHead>
            <TableHead>Department</TableHead>
            <TableHead>Purpose</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="detail in requestOrder.details" :key="detail.id">
            <TableCell>{{ detail.item_description }}</TableCell>
            <TableCell>{{ detail.quantity }}</TableCell>
            <TableCell>{{ detail.unit }}</TableCell>
            <TableCell>{{ detail.request?.department?.department_name ?? 'N/A' }}</TableCell>
        <TableCell>{{ detail.request?.purpose ?? 'N/A' }}</TableCell>
            </TableRow>
        </TableBody>
        </Table>


    </div>
  </AppLayout>
</template>
