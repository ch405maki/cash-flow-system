<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'
import { 
  FileText,
} from 'lucide-vue-next'

defineProps<{
  recentRequestToOrders: Array<any>;
}>();

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  });
}

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`)
}
</script>

<template>
  <div v-if="recentRequestToOrders.length > 0" class="border rounded-xl mt-4 overflow-auto">
    <div class="px-4 py-3 bg-muted/40">
      <h2 class="text-base font-semibold">Recent Request to Orders</h2>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="text-left">Order No</TableHead>
          <TableHead class="text-right">Date</TableHead>
          <TableHead class="text-right">Status</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="order in recentRequestToOrders"
          :key="order.id"
          class="hover:bg-muted/20 transition-colors cursor-pointer"
          @click="viewRequest(order.id)"
        >
          <TableCell class="font-medium text-purple-800 hover:underline hover:text-purple-700">
            {{ order.order_no }}
          </TableCell>
          <TableCell class="text-right text-muted-foreground">
            {{ formatDate(order.order_date) }}
          </TableCell>
          <TableCell class="text-right capitalize">
            <span
              :class="{
                'text-green-600': order.status === 'approved',
                'text-yellow-600': order.status === 'to_order',
                'text-blue-600': order.status === 'to_property',
                'text-red-600': order.status === 'rejected'
              }"
            >
              {{ order.status.replace('_', ' ') }}
            </span>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <div v-else-if="recentRequestToOrders" class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No recent request to order found</p>
    <p class="text-xs text-muted-foreground">Requests for approval from Property Custodian will appear here</p>
  </div>
</template>
