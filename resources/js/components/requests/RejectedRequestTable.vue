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
import { FilePenLine, Eye  } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { FileText } from 'lucide-vue-next'

const props = defineProps<{
  requests: Array<any>
}>()

const user = usePage().props.auth.user;

const getFullName = (user: any) =>
  `${user.first_name} ${user.last_name}`

function goToShowRequest(requestId: number) {
  router.get(`/request/show/${requestId}`)
}

function goToCreatePO(requestId: number) {
  router.get(`/purchase-orders/create/${requestId}`)
}

function goToEditRequest(requestId: number) {
  router.get(`/requests/${requestId}/edit`)
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
  <div v-if="requests.length > 0" class="rounded-lg border">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Request No</TableHead>
          <TableHead>Date</TableHead>
          <!-- <TableHead>Purpose</TableHead> -->
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
          <TableCell>{{ formatDate(request.request_date) }}</TableCell>
          <!-- <TableCell>{{ request.purpose }}</TableCell> -->
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
              @click="goToEditRequest(request.id)"
              v-if="user.role === 'staff' && request.status === 'pending'"
            >
              <FilePenLine class="h-4" />
              Edit
            </Button>

            <Button
              size="sm"
              variant="default"
              @click="goToShowRequest(request.id)"
            >
            <Eye class="h-4"/>
              Show
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <div v-else-if="requests" class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No rejected requests found</p>
    <p class="text-xs text-muted-foreground">Rejected requests from your department will appear here</p>
</div>
</template>
