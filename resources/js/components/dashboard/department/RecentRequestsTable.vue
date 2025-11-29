<script setup lang="ts">
import { 
  FileText,
  ClipboardList
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import PageHeader from '@/components/PageHeader.vue';
import { Badge } from '@/components/ui/badge';
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

// Status badge configuration
const getStatusConfig = (status: string) => {
  const statusMap: Record<string, { color: string; label: string }> = {
    'pending': {
      color: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-100',
      label: 'Pending'
    },
    'approved': {
      color: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-100',
      label: 'Approved'
    },
    'to_order': {
      color: 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-100',
      label: 'To Order'
    },
    'rejected': {
      color: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-100',
      label: 'Rejected'
    },
    'draft': {
      color: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-100',
      label: 'Draft'
    },
    'in_review': {
      color: 'bg-purple-100 text-purple-800 border-purple-200 hover:bg-purple-100',
      label: 'In Review'
    },
    'completed': {
      color: 'bg-emerald-100 text-emerald-800 border-emerald-200 hover:bg-emerald-100',
      label: 'Completed'
    },
    'cancelled': {
      color: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-100',
      label: 'Cancelled'
    }
  }

  return statusMap[status.toLowerCase()] || {
    color: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-100',
    label: status.charAt(0).toUpperCase() + status.slice(1)
  }
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
            <TableHead class="w-[200px]">Request No.</TableHead>
            <TableHead class="w-[120px]">Date</TableHead>
            <TableHead class="w-[320px]">Purpose</TableHead>
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
              <Badge 
                variant="outline" 
                :class="getStatusConfig(request.status).color"
                class="font-medium capitalize"
              >
                {{ getStatusConfig(request.status).label }}
              </Badge>
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