<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next';
import { PhilippinePeso, Eye } from 'lucide-vue-next';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/components/ui/tooltip'
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
    { title: 'Dashboard', href: '#', },
    { title: 'Approved Petty Cash', href: '#', },
];

const props = defineProps<{
  pettyCash: Object,
  pettyCashFund:Number
}>();

const hasItem = Object.keys(props.pettyCash).length;
</script>

<template>
<Head title="Bursar's Petty Cash" />

<AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex justify-between items-center">
          <div>
              <h1 class="text-xl font-bold">Petty Cash</h1>
              <p class="text-sm font-medium">List of approved petty cash.</p>
          </div>
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger>
                <div class="rounded-lg border p-4 hover:border-green-500 hover:text-green-600">
                  <h1 class="text-xl font-medium flex items-center"><PhilippinePeso />{{ props.pettyCashFund.fund_amount }}</h1>
                </div>
              </TooltipTrigger>
              <TooltipContent>
                <p>Allocated Fund</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
          
        </div>
        <!-- Table -->
        <div v-if="hasItem" class="border rounded-lg">
          <Table>
            <TableCaption class="py-4">A list of your petty cash.</TableCaption>
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
                  <Button @click="router.get(route('bursar.petty-cash.view', item.id))">
                    <Eye/>Review
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