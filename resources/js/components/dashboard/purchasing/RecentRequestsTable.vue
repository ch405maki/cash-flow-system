<script setup lang="ts">
import { 
  FileText,
  ClipboardList
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

defineProps<{
  isDepartmentUser: boolean;
  recentRequests: Array<{
    id: number;
    order_no: string;
    request_date: string;
    note: string;
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

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`) 
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
  <div v-if="isDepartmentUser && recentRequests.length > 0" class="rounded-lg border">
    <div class="relative w-full overflow-auto">
      <table class="w-full caption-bottom text-sm">
        <thead class="[&_tr]:border-b">
          <tr class="border-b transition-colors hover:bg-muted/50">
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Order #</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Requested By</th>
          </tr>
        </thead>
        <tbody class="[&_tr:last-child]:border-0">
          <tr @click="viewRequest(request.id)" v-for="request in recentRequests" :key="request.id" class="border-b transition-colors hover:bg-muted/50 hover:cursor-pointer">
            <td class="p-4 align-middle font-medium cursor-pointer text-purple-800 hover:underline hover:text-purple-700">
              {{ request.order_no }}
            </td>
            <td class="p-4 align-middle">
              {{ formatDate(request.request_date) }}
            </td>
            <td class="p-4 align-middle">
              {{ request.user?.first_name }} {{ request.user?.last_name }}
            </td>
          </tr>
        </tbody>
      </table>
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