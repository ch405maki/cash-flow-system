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
import { formatDate, formatDateTime } from '@/lib/utils';
import { FileText } from 'lucide-vue-next';
import StatusBadge from '@/components/StatusBadge.vue';


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
  <div v-if="requests.length > 0">
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
          <TableCell>{{ formatDateTime(request.request_date) }}</TableCell>
          <TableCell>{{ request.department?.department_name }}</TableCell>
          <TableCell>{{ getFullName(request.user) }}</TableCell>
          <TableCell class="text-right">
            <StatusBadge 
                :status="request.status"
                show-icon
                size="md"
              />
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
