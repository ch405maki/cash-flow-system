<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table';

const props = defineProps<{
  purchaseOrders: Array<{
    id: number;
    po_no: string;
    date: string;
    payee: string;
    amount: number;
    department: { department_name: string };
  }>;
}>();

const totalAmount = computed(() =>
  props.purchaseOrders.reduce((sum, po) => sum + Number(po.amount || 0), 0)
);

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard', },
    { title: 'Reports', href: '/reports', },
    { title: 'Purchase Order Summary', href: '/', },
];
</script>

<template>
    <Head title="Purchase Order Summary" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
                <Table>
                <TableHeader>
                    <TableRow>
                    <TableHead>PO No</TableHead>
                    <TableHead>Date</TableHead>
                    <TableHead>Department</TableHead>
                    <TableHead>Payee</TableHead>
                    <TableHead class="text-right">Amount (₱)</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                <TableRow v-for="po in props.purchaseOrders" :key="po.id">
                    <TableCell>{{ po.po_no }}</TableCell>
                    <TableCell>{{ formatDate(po.date) }}</TableCell>
                    <TableCell>{{ po.department.department_name }}</TableCell>
                    <TableCell>{{ po.payee }}</TableCell>
                    <TableCell class="text-right">₱{{ Number(po.amount || 0).toFixed(2) }}</TableCell>
                </TableRow>

                <!-- Total Row -->
                <TableRow>
                    <TableCell colspan="4" class="text-right font-semibold">Total:</TableCell>
                    <TableCell class="text-right font-bold text-foreground">
                        <div v-if="totalAmount === 0" class="text-warning">Warning: Total is zero</div>
                        ₱{{ totalAmount.toFixed(2) }}
                    </TableCell>
                </TableRow>
                </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
