<script setup lang="ts">
import {
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
} from '@/components/ui/table';
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { formatDate } from '@/lib/utils';


const props = defineProps<{
  requests: Array<any>
}>()

const user = usePage().props.auth.user;

const getFullName = (user: any) =>
  `${user.first_name} ${user.last_name}`

function goToShowRequest(requestId: number) {
  router.get(`/request/show/${requestId}`)
}
</script>

<template>
  <div v-if="requests.length > 0" class="rounded-lg border">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Request No</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Department</TableHead>
          <TableHead>Requested By</TableHead>
          <TableHead class="text-right">Status</TableHead>
        </TableRow>
      </TableHeader>

      <TableBody>
        <TableRow
          v-for="request in props.requests"
          :key="request.id"
          class="hover:bg-muted/50 group cursor-pointer hover:underline"
          title="show request"
          @click="goToShowRequest(request.id)"
        >
          <TableCell>{{ request.request_no }}</TableCell>
          <TableCell>{{ formatDate(request.request_date) }}</TableCell>
          <!-- <TableCell>{{ request.purpose }}</TableCell> -->
          <TableCell>{{ request.department?.department_name }}</TableCell>
          <TableCell>{{ getFullName(request.user) }}</TableCell>
          <TableCell class="text-right">
            <span
              class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
              :class="{
                'bg-indigo-100 text-indigo-800': request.status === 'partially_released',
                'bg-orange-100 text-yellow-600': request.status === 'to_property',
                'bg-orange-100 text-orange-500': request.status === 'to_order',
                'bg-yellow-100 text-yellow-800': request.status === 'pending',
                'bg-green-100 text-green-800': request.status === 'approved',
                'bg-red-100 text-red-800': request.status === 'rejected',
              }"
            >
              {{ request.status }}
            </span>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <div v-else-if="requests" class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No requests found</p>
    <p class="text-xs text-muted-foreground">On process requests from your department will appear here</p>
  </div>
</template>
