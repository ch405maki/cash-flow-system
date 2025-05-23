<script setup lang="ts">
import {
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { defineProps } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps<{
  requests: Array<any>
}>()

const getFullName = (user: any) =>
  `${user.first_name} ${user.middle_name} ${user.last_name}`

function goToShowRequest(requestId: number) {
  router.get(`/request/show/${requestId}`)
}

function goToCreatePO(requestId: number) {
  router.get(`/purchase-orders/create/${requestId}`)
}
</script>

<template>
  <div class="rounded-lg border">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Request No</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Purpose</TableHead>
          <TableHead>Department</TableHead>
          <TableHead>Requested By</TableHead>
          <TableHead>Status</TableHead>
          <TableHead class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow
          v-for="request in props.requests"
          :key="request.id"
          class="hover:bg-muted/50"
        >
          <TableCell>{{ request.request_no }}</TableCell>
          <TableCell>{{ request.request_date }}</TableCell>
          <TableCell>{{ request.purpose }}</TableCell>
          <TableCell>{{ request.department?.department_name }}</TableCell>
          <TableCell>{{ getFullName(request.user) }}</TableCell>
          <TableCell>
            <span
              class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
              :class="{
                'bg-indigo-100 text-indigo-800': request.status === 'partially_released',
                'bg-orange-100 text-orange-800': request.status === 'request to order',
                'bg-yellow-100 text-yellow-800': request.status === 'pending',
                'bg-green-100 text-green-800': request.status === 'approved',
                'bg-red-100 text-red-800': request.status === 'rejected',
              }"
            >
              {{ request.status }}
            </span>
          </TableCell>
          <TableCell class="text-right space-x-2">
            <Button
              size="sm"
              variant="outline"
              @click="goToShowRequest(request.id)"
            >
              Show
            </Button>
            <Button v-if="request.status === 'approved'" size="sm" @click="goToCreatePO(request.id)">
              Create PO
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>
