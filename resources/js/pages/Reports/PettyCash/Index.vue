<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next';
import { formatDate } from '@/lib/utils'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard', },
    { title: 'Petty Cash', href: '#', },
];

const props = defineProps<{
  pettyCash: object
}>()
const hasItem = Object.keys(props.pettyCash).length;


const totalAmount = (items: any[]) => {
    return items.reduce((sum, item) => sum + parseFloat(item.amount), 0).toFixed(2);
};
</script>

<template>
    <Head title="Petty Cash" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
          <div class="flex justify-between">
            <div>
              <h1 class="text-xl font-bold">Petty Cash</h1>
              <p class="text-sm font-medium">List of created petty cash.</p>
            </div>
          </div>
          <Table v-if="hasItem">
            <TableCaption>A list of your petty cash.</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[200px]">PCV NO</TableHead>
                <TableHead class="w-[100px]">Paid to</TableHead>
                <TableHead class="w-[150px]">Requested By</TableHead>
                <TableHead class="w-[100px]">Date</TableHead>
                <TableHead class="w-[100px]">Status</TableHead>
                <TableHead class="w-[300px]">Remarks</TableHead>
                <TableHead class="w-[100px]">Amount</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="item in pettyCash"  
                :key="item.id" 
                @click="router.get(route('petty-cash.view', item.id))"
                class="cursor-pointer hover:text-primary"
                >
                <TableCell class="font-medium">
                  {{ item.pcv_no }}
                </TableCell>
                <TableCell class="capitalize">
                  {{ item.paid_to }}
                </TableCell>
                <TableCell class="capitalize">
                  {{ item.user?.first_name }} {{ item.user?.last_name }}
                </TableCell>
                <TableCell class="capitalize">
                  {{ formatDate(item.date) }}
                </TableCell>
                <TableCell class="capitalize">
                  {{ item.status }}
                </TableCell>
                <TableCell class="capitalize">
                  "{{ item.remarks }}"
                </TableCell>
                <TableCell class="capitalize">
                  {{ totalAmount(item.items) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
            <FileText class="h-8 w-8 text-muted-foreground" />
            <p class="mt-2 text-sm text-muted-foreground">No petty cash record found</p>
            <p class="text-xs text-muted-foreground mb-4">List of petty cash will appear here</p>
          </div>
        </div>
    </AppLayout>
</template>
