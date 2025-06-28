<script setup lang="ts">
import { 
  FileText,
  ClipboardList
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { formatDate, formatCurrency } from '@/lib/utils';

interface RequestItem {
  id: number;
  po_no: string;
  date: string;
  payee: string;
  amount: string;
  purpose: string;
  status: string;
  department: string | null;
  user: {
    first_name: string;
    last_name: string;
  } | null;
  details: Array<{
    id: number;
    quantity: string;
    unit: string;
    item_description: string;
    unit_price: string;
    amount: string;
  }>;
}

const props = withDefaults(defineProps<{
  isDepartmentUser?: boolean;
  recentRequests?: RequestItem[];
}>(), {
  isDepartmentUser: false,
  recentRequests: () => []
});

function goToPO(id: number) {
  router.visit(`/purchase-orders/${id}`)
}
</script>

<template>
  <div v-if="isDepartmentUser">
    <div v-if="recentRequests.length > 0" class="rounded-lg border">
      <div class="relative w-full overflow-auto">
        <table class="w-full caption-bottom text-sm">
          <thead class="[&_tr]:border-b">
            <tr class="border-b transition-colors hover:bg-muted/50">
              <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">PO #</th>
              <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
              <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Payee</th>
              <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Amount</th>
              <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Requested By</th>
              <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
            </tr>
          </thead>
          <tbody class="[&_tr:last-child]:border-0">
            <tr 
              v-for="request in recentRequests" 
              :key="request.id" 
              class="border-b transition-colors hover:bg-muted/50 cursor-pointer" 
              @click="goToPO(request.id)"
            >
              <td class="p-4 align-middle font-medium">
                {{ request.po_no }}  
              </td>
              <td class="p-4 align-middle">
                {{ formatDate(request.date) }}
              </td>
              <td class="p-4 align-middle">
                {{ request.payee }}
              </td>
              <td class="p-4 align-middle">
                {{ formatCurrency(request.amount) }}
              </td>
              <td class="p-4 align-middle">
                {{ request.user?.first_name }} {{ request.user?.last_name }}
              </td>
              <td class="p-4 align-middle capitalize">
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
    
    <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
      <FileText class="h-8 w-8 text-muted-foreground" />
      <p class="mt-2 text-sm text-muted-foreground">No recent requests found</p>
      <p class="text-xs text-muted-foreground">Requests from your department will appear here</p>
    </div>
  </div>
  
  <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
    <ClipboardList class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">You don't have permission to view department requests</p>
  </div>
</template>