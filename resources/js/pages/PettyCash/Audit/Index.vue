<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next';
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
    { title: 'Petty Cash Review', href: '#', },
];

const props = defineProps<{
  pettyCash: Object,
}>();

const hasItem = Object.keys(props.pettyCash).length;
</script>

<template>
<Head title="Audit Petty Cash" />

<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex justify-between">
        <div>
            <h1 class="text-xl font-bold">Petty Cash</h1>
            <p class="text-sm font-medium">List of created petty cash.</p>
        </div>
        </div>
        <!-- Table -->
        <div v-if="hasItem">
          <Table>
            <TableCaption>A list of your petty cash.</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[300px]">
                  PCV NO
                </TableHead>
                <TableHead class="w-[100px]">Status</TableHead>
                <TableHead class="text-right">
                  Action
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in pettyCash" :key="item.id">
                <TableCell class="font-medium">
                  {{ item.pcv_no }}
                </TableCell>
                <TableCell class="capitalize">{{ item.status }}</TableCell>
                <TableCell class="text-right">
                  <Button @click="router.get(route('petty-cash.view', item.id))">
                    Review
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
          <FileText class="h-8 w-8 text-muted-foreground" />
          <p class="mt-2 text-sm text-muted-foreground">No petty cash to review found</p>
          <p class="text-xs text-muted-foreground">Petty cash for review will appear here</p>
        </div>
    </div>
</AppLayout>
</template>