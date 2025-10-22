<script setup lang="ts">
import { 
  FileText,
  ClipboardList
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

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

const getStatusVariant = (status: string) => {
  const variantMap: { [key: string]: 'default' | 'secondary' | 'destructive' | 'outline' } = {
    propertyCustodian: 'secondary',
    pending: 'outline',
    approved: 'default',
    to_order: 'secondary',
    rejected: 'destructive'
  };
  
  return variantMap[status] || 'outline';
};
</script>

<template>
  <div v-if="isDepartmentUser && recentRequests.length > 0">
    <div class="relative w-full overflow-auto">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>
              Request #
            </TableHead>
            <TableHead>
              Date
            </TableHead>
            <TableHead>
              Requested By
            </TableHead>
            <TableHead>
              Purpose
            </TableHead>
            <TableHead>
              Status
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow 
            v-for="request in recentRequests" 
            :key="request.id" 
            class="border-b transition-colors hover:bg-muted/50 cursor-pointer" 
            @click="goToShowRequest(request.id)"
          >
            <TableCell>
              {{ request.request_no }}
            </TableCell>
            <TableCell>
              {{ formatDate(request.request_date) }}
            </TableCell>
            <TableCell>
              {{ request.user?.first_name }} {{ request.user?.last_name }}
            </TableCell>
            <TableCell>
              {{ request.purpose }}
            </TableCell>
            <TableCell>
                <Badge 
                  :variant="getStatusVariant(request.status)"
                  class="capitalize"
                >
                  {{ request.status }}
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