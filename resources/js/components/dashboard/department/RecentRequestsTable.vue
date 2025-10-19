<script setup lang="ts">
import { 
  FileText,
  ClipboardList
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import PageHeader from '@/components/PageHeader.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

defineProps<{
  isDepartmentUser: boolean;
  recentRequests: Array<{
    id: number;
    request_no: string;
    request_date: string;
    purpose: string;
    status: string;
    user: {
        first_name: string;
        last_name: string;
    } | null;
    details: Array<{
        id: number;
        quantity: number;
        unit: string;
        item_description: string;
    }>;
  }>;
}>()

function goToShowRequest(requestId: number) {
  router.get(`/request/show/${requestId}`)
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
  <PageHeader 
    title="Recent Requests" 
    subtitle="Latest purchase and service requests awaiting action"
  />
  <div v-if="isDepartmentUser && recentRequests.length > 0">
    <div class="relative w-full overflow-auto">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[200px]">Request #</TableHead>
            <TableHead class="w-[120px]">Date</TableHead>
            <TableHead class="w-[320px]">Purpose</TableHead>
            <TableHead class="text-right">Requested By</TableHead>
            <TableHead class="text-right">Status</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow 
            v-for="request in recentRequests" 
            :key="request.id" 
            class="cursor-pointer hover:bg-muted/50"
            @click="goToShowRequest(request.id)"
          >
            <TableCell class="font-medium">
              {{ request.request_no }}
            </TableCell>
            <TableCell>
              {{ formatDate(request.request_date) }}
            </TableCell>
            <TableCell>
              {{ request.purpose }}
            </TableCell>
            <TableCell class="text-right">
              {{ request.user?.first_name }} {{ request.user?.last_name }}
            </TableCell>
            <TableCell class="text-right">
              <span 
                class="capitalize font-medium"
                :class="{
                  'text-yellow-500': request.status === 'pending',
                  'text-green-500': request.status === 'approved',
                  'text-blue-500': request.status === 'to_order',
                  'text-red-500': request.status === 'rejected'
                }"
              >
                {{ request.status }}
              </span>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </div>
  
  <div v-else-if="isDepartmentUser" class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No recent requests found</p>
    <p class="text-xs text-muted-foreground">Requests from your department will appear here</p>
  </div>
  
  <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <ClipboardList class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">You don't have permission to view department requests</p>
  </div>
</template>