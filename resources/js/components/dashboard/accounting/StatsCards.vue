<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { 
  Users,
  Clock,
  CheckCircle,
  ShoppingCart,
  XCircle,
} from 'lucide-vue-next'

defineProps<{
  statusCounts: {
    pending: number;
    forApproval: number;
    approved: number;
    rejected: number;
  };
}>()
</script>

<template>
  <div class="grid gap-4 md:grid-cols-4">
    <!-- Pending Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Pending
        </CardTitle>
        <Clock class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.pending }}</div>
        <p class="text-xs text-muted-foreground">
          Waiting for department head approval
        </p>
      </CardContent>
    </Card>

    <!-- To Order Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          For EOD Approval
        </CardTitle>
        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.forApproval }}</div>
        <p class="text-xs text-muted-foreground">
          Pending For EOD Approval
        </p>
      </CardContent>
    </Card>

    <!-- Approved Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Approved
        </CardTitle>
        <CheckCircle class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.approved }}</div>
        <p class="text-xs text-muted-foreground">
          Ready for check processing
        </p>
      </CardContent>
    </Card>

    <!-- Rejected Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Rejected
        </CardTitle>
        <XCircle class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.rejected }}</div>
        <p class="text-xs text-muted-foreground">
          Not approved for review
        </p>
      </CardContent>
    </Card>
  </div>
</template>