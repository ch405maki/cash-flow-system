<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import {
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'

const props = defineProps({
  request: {
    type: Object,
    required: true,
  },
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
  { title: `${props.request.request_no}`, href: '' },
]
</script>

<template>
  <Head title="Create Purchase Order" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-4">
      <div class="flex">
        <h1 class="text-2xl font-bold">Create Purchase Order</h1>
        <div class="ml-auto space-x-2">
          <Button variant="default" size="sm" @click="approve()">
            Approve
        </Button>
      </div>
      </div>

      <div>
        <p><strong>Request No:</strong> {{ request.request_no }}</p>
        <p><strong>Status:</strong> {{ request.status }}</p>
        <p><strong>Department:</strong> {{ request.department.department_name || 'N/A' }}</p>
        <p><strong>Requested By:</strong> {{ request.user.first_name }} {{ request.user.last_name }}</p>
        <p><strong>Purpose:</strong> {{ request.purpose }}</p>
      </div>

      <div>
        <h2 class="text-lg font-semibold mt-4">Items</h2>
        <div class="rounded-md border">
          <Table>
            <TableHeader class="bg-muted">
              <TableRow>
                <TableHead class="border p-2 w-10">#</TableHead>
                <TableHead class="border p-2">Quantity</TableHead>
                <TableHead class="border p-2">Unit</TableHead>
                <TableHead class="border p-2">Item Description</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(detail, index) in request.details" :key="detail.id">
                <TableCell class="border p-2">{{ index + 1 }}</TableCell>
                <TableCell class="border p-2">{{ detail.quantity }}</TableCell>
                <TableCell class="border p-2">{{ detail.unit }}</TableCell>
                <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
