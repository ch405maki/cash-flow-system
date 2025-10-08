<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { computed } from 'vue'
import { FileText, PhilippinePeso } from 'lucide-vue-next'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash', href: '#' },
]
const user = usePage().props.auth.user;

const props = defineProps<{
  pettyCash: Record<string, any> | null
  pettyCashFund: { fund_amount?: number } | null
}>()

// Check if there are petty cash records
const hasItem = computed(() => props.pettyCash && Object.keys(props.pettyCash).length > 0)

// Handle navigation to create page
const goToCreate = () => {
  router.get(route('petty-cash.create'))
}
</script>

<template>
  <Head title="Petty Cash" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-xl font-bold">Petty Cash</h1>
          <p class="text-sm font-medium">List of created petty cash.</p>
        </div>

        <div class="flex items-center gap-3">
          <TooltipProvider v-if="props.pettyCashFund && props.pettyCashFund.fund_amount">
            <Tooltip>
              <TooltipTrigger>
                <div
                  class="rounded-lg border p-4 text-purple-600 border-purple-500 bg-purple-100 
                        hover:border-purple-600 hover:text-purple-700 flex items-center gap-1"
                >
                  <PhilippinePeso class="w-4 h-4" />
                  <span class="text-xl font-medium">{{ props.pettyCashFund.fund_amount }}</span>
                </div>
              </TooltipTrigger>
              <TooltipContent>
                <p>Allocated Fund</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <Button v-if="hasItem && user.is_petty_cash == 1" @click="goToCreate">Create New Petty Cash</Button>
        </div>
      </div>

      <Table v-if="hasItem">
        <TableCaption>A list of your petty cash.</TableCaption>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[300px]">PCV NO</TableHead>
            <TableHead class="w-[100px]">Status</TableHead>
            <TableHead class="text-right">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in pettyCash" :key="item.id">
            <TableCell class="font-medium">{{ item.pcv_no }}</TableCell>
            <TableCell class="capitalize">{{ item.status }}</TableCell>
            <TableCell class="flex justify-end space-x-2">
              <Button
                v-if="item.status !== 'draft' && item.status !== 'requested'"
                @click="router.get(route('petty-cash.view', item.id))"
              >
                View
              </Button>
              <Button
                v-if="item.status === 'draft'"
                @click="router.get(route('petty-cash.edit', item.id))"
              >
                Review
              </Button>
              <Button
                v-if="item.status === 'requested'"
                @click="router.get(route('petty-cash.view', item.id))"
              >
                View Request
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>

      <div
        v-else
        class="flex h-48 flex-col items-center justify-center rounded-xl border text-center"
      >
        <FileText class="h-8 w-8 text-muted-foreground" />
        <p class="mt-2 text-sm text-muted-foreground">No petty cash found</p>
        <p class="text-xs text-muted-foreground mb-4">
          List of your petty cash will appear here
        </p>
        <Button v-if="user.is_petty_cash == 1" @click="goToCreate">Create New Petty Cash</Button>
      </div>
    </div>
  </AppLayout>
</template>
