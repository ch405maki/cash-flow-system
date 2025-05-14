<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Purchase Orders', href: '/purchase-orders' },
  { title: 'Details', href: '' },
];

defineProps({
  purchaseOrder: {
    type: Object,
    required: true
  }
});

function formatDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  });
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(amount);
}
</script>

<template>
  <Head title="Purchase Order Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Purchase Order: {{ purchaseOrder.po_no }}</h1>
        <div class="space-x-2">
          <Button variant="outline" as-child>
            <Link :href="`/purchase-orders/${purchaseOrder.id}/edit`">Edit</Link>
          </Button>
          <Button as-child>
            <Link href="/purchase-orders">Back to List</Link>
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- PO Summary Card -->
        <Card>
          <CardHeader>
            <CardTitle>PO Information</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <p class="text-sm text-muted-foreground">Status</p>
              <Badge class="mt-1">
                {{ purchaseOrder.status }}
              </Badge>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Date</p>
              <p>{{ formatDate(purchaseOrder.date) }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Total Amount</p>
              <p class="font-semibold">{{ formatCurrency(purchaseOrder.amount) }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Payee Information -->
        <Card>
          <CardHeader>
            <CardTitle>Payee Information</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <p class="text-sm text-muted-foreground">Payee</p>
              <p>{{ purchaseOrder.payee }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Check Payable To</p>
              <p>{{ purchaseOrder.check_payable_to }}</p>
            </div>
          </CardContent>
        </Card>

        <!-- Department & Account -->
        <Card>
          <CardHeader>
            <CardTitle>Department & Account</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div>
              <p class="text-sm text-muted-foreground">Department</p>
              <p>{{ purchaseOrder.department.department_name }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Account</p>
              <p>{{ purchaseOrder.account.account_title }}</p>
            </div>
            <div>
              <p class="text-sm text-muted-foreground">Requested By</p>
              <p>{{ purchaseOrder.user.first_name }} {{ purchaseOrder.user.last_name }}</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Purpose & Remarks -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Purpose</CardTitle>
          </CardHeader>
          <CardContent>
            <p>{{ purchaseOrder.purpose }}</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Remarks</CardTitle>
          </CardHeader>
          <CardContent>
            <p>{{ purchaseOrder.remarks || 'No remarks' }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Items Table -->
      <Card>
        <CardHeader>
          <CardTitle>Items</CardTitle>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                  <TableHead>Quantity</TableHead>
                  <TableHead>Unit/S</TableHead>
                  <TableHead>Description</TableHead>
                <TableHead class="text-right">Unit Price</TableHead>
                <TableHead class="text-right">Amount</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in purchaseOrder.details" :key="item.id">
                  <TableCell>{{ item.quantity }}</TableCell>
                  <TableCell> {{ item.unit }}</TableCell>
                  <TableCell>{{ item.item_description }}</TableCell>
                <TableCell class="text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
                <TableCell class="text-right font-medium">{{ formatCurrency(item.amount) }}</TableCell>
              </TableRow>
              <TableRow>
                <TableCell colspan="4" class="text-right font-medium">Total</TableCell>
                <TableCell class="text-right font-bold">
                  {{ formatCurrency(purchaseOrder.amount) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>