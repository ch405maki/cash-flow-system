<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { 
  Users,
  Clock,
  CheckCircle,
  ShoppingCart,
  XCircle,
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

defineProps<{
  statusCounts: {
    po_approval: number;
    canvas_approval: number;
    approved_request: number;
    approved_po: number;
  };
}>()

const goToApprovedRequest = () => {
  router.get('/approved-request');
};
const goToCanvas = () => {
  router.get('/canvas/approval');
};
</script>

<template>
  <div class="grid gap-4 md:grid-cols-4">
    <!-- Pending Requests Card -->
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50"  
      @click="goToApprovedRequest">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Approved Request
        </CardTitle>
        <Clock class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.approved_request }}</div>
        <p class="text-xs text-muted-foreground">
          For P. O. creation
        </p>
      </CardContent>
    </Card>

    <!-- To Order Card -->
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50"
      @click="goToCanvas">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Canvas For Approval
        </CardTitle>
        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.canvas_approval }}</div>
        <p class="text-xs text-muted-foreground">
          Waiting EOD Approval
        </p>
      </CardContent>
    </Card>

    <!-- Approved Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          P. O. For Approval
        </CardTitle>
        <CheckCircle class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.po_approval }}</div>
        <p class="text-xs text-muted-foreground">
          Waiting EOD Approval
        </p>
      </CardContent>
    </Card>

    <!-- Rejected Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Approved P. O.
        </CardTitle>
        <CheckCircle class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.approved_po }}</div>
        <p class="text-xs text-muted-foreground">
          All Approved Purchase Order
        </p>
      </CardContent>
    </Card>
  </div>
</template>