<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { FileText } from 'lucide-vue-next'
import PageHeader from '@/components/PageHeader.vue';
import {
  Table,
  TableCaption,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Order For Approval', href: '/request-to-order' },
]

function goToCreate() {
  router.visit('/request-to-order/create')
}

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`) 
}

const props = defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
  authUser: {
    type: Array,
    default: () => [],
  },
})

function getStatusVariant(status: string) {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'secondary' 
    case 'approved':
      return 'success'
    case 'rejected':
      return 'destructive'
    default:
      return 'default'
  }
}


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
  <Head title="Request To Order List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <PageHeader 
        title="For Approval Request to Purchase" 
        subtitle="Created Purchase(s) On Process"
      />
      <!-- Table -->
      <div v-if="props.requests.length > 0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Order No</TableHead>
                <TableHead>Date Request</TableHead>
                <TableHead>Notes</TableHead>
                <TableHead class="w-[100px]">Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
              v-for="request in props.requests" 
              :key="request.id"
              @click="viewRequest(request.id)"
              class="cursor-pointer hover:underline"
              title="Show details"
              >
                <TableCell class="font-medium">{{ request.order_no }}</TableCell>
                <TableCell>{{ formatDate(request.order_date) }}</TableCell>
                <TableCell>{{ request.notes || 'No Note(s) Attatched'}}</TableCell>
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
        <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
          <FileText class="h-8 w-8 text-muted-foreground" />
          <p class="mt-2 text-sm text-muted-foreground">No pending request to order found</p>
          <p class="text-xs text-muted-foreground">Request to order for approval from Property Custodian will appear here</p>
        </div>
    </div>
  </AppLayout>
</template>