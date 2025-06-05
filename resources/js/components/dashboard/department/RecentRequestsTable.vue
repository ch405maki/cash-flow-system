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
  <div v-if="isDepartmentUser && recentRequests.length > 0" class="rounded-xl border">
    <div class="relative w-full overflow-auto">
      <table class="w-full caption-bottom text-sm">
        <thead class="[&_tr]:border-b">
          <tr class="border-b transition-colors hover:bg-muted/50">
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Request #</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Requested By</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Purpose</th>
            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
          </tr>
        </thead>
        <tbody class="[&_tr:last-child]:border-0">
          <tr v-for="request in recentRequests" :key="request.id" class="border-b transition-colors hover:bg-muted/50">
            <td class="p-4 align-middle font-medium cursor-pointer hover:underline hover:text-purple-700"  @click="goToShowRequest(request.id)">
              {{ request.request_no }}
            </td>
            <td class="p-4 align-middle">
              {{ formatDate(request.request_date) }}
            </td>
            <td class="p-4 align-middle">
              {{ request.user?.first_name }} {{ request.user?.last_name }}
            </td>
            <td class="p-4 align-middle">
              {{ request.purpose }}
            </td>
            <td class="p-4 align-middle">
              <span :class="{
                'text-yellow-500': request.status === 'pending',
                'text-green-500': request.status === 'approved',
                'text-blue-500': request.status === 'to_order',
                'text-red-500': request.status === 'rejected'
              }">
                {{ request.status }}
              </span>
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