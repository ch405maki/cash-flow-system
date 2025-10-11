<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { computed } from 'vue'
import { FileText, PhilippinePeso } from 'lucide-vue-next'
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
  pettyCashFund: { 
    fund_amount?: number 
    fund_balance?: number 
  } | null
}>()

// Check if there are petty cash records
const hasItem = computed(() => props.pettyCash && Object.keys(props.pettyCash).length > 0)

// Handle navigation to create page
const goToCreate = () => {
  router.get(route('petty-cash.create'))
}

const fundStatus = computed(() => {
  const fund = props.pettyCashFund;
  if (!fund || !fund.fund_amount) return {};

  const balance = Number(fund.fund_balance);
  const amount = Number(fund.fund_amount);
  const percentage = (balance / amount) * 100;

  if (percentage <= 20) {
    return {
      color: 'border-red-500 bg-red-100 hover:border-red-600 hover:text-red-700 text-red-600',
      textColor: 'text-red-700',
      label: 'Needs Replenishment',
      iconColor: 'text-red-700',
      iconBackground: 'bg-red-100 border-red-400',
    };
  } else if (percentage <= 50) {
    return {
      color:
        'border-yellow-500 bg-yellow-100 hover:border-yellow-600 hover:text-yellow-700 text-yellow-600',
      textColor: 'text-yellow-700',
      label: 'Low Balance',
      iconColor: 'text-yellow-700',
      iconBackground: 'bg-yellow-100 border-yellow-400',
    };
  } else {
    return {
      color:
        'border-purple-500 bg-purple-100 hover:border-purple-600 hover:text-purple-700 text-purple-600',
      textColor: 'text-purple-700',
      label: '',
      iconColor: 'text-purple-700',
      iconBackground: 'bg-purple-100 border-purple-400',
    };
  }
});
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
          <TooltipProvider
            v-if="props.pettyCashFund && props.pettyCashFund.fund_balance !== undefined"
          >
            <Tooltip>
              <TooltipTrigger>
                <div
                  :class="[
                    'rounded-lg border px-5 py-4 flex items-center justify-between transition-colors cursor-pointer w-full',
                    fundStatus.color,
                  ]"
                >
                  <!-- Icon -->
                  <div
                    class="flex items-center justify-center w-12 h-12 rounded-full border-2 flex-shrink-0"
                    :class="fundStatus.iconBackground"
                  >
                    <PhilippinePeso class="w-6 h-6" :class="fundStatus.iconColor" />
                  </div>

                  <!-- Balance Info -->
                  <div class="text-right ml-4 flex-1">
                    <h1 class="text-2xl font-semibold leading-tight text-left">
                      {{ Number(props.pettyCashFund.fund_balance).toLocaleString() }}
                    </h1>
                    <p
                      v-if="fundStatus.label"
                      class="text-sm font-medium"
                      :class="fundStatus.textColor"
                    >
                      {{ fundStatus.label }}
                    </p>
                  </div>
                </div>
              </TooltipTrigger>

              <TooltipContent>
                <div class="space-y-1">
                  <div class="flex justify-between gap-4">
                    <span class="font-medium">Current Balance:</span>
                    <span class="font-semibold">
                      ₱{{ Number(props.pettyCashFund.fund_balance).toLocaleString() }}
                    </span>    
                  </div>
                  <div class="flex justify-between gap-4">
                    <span class="font-medium">Allocated Fund:</span>
                    <span class="font-semibold">
                      ₱{{ Number(props.pettyCashFund.fund_amount).toLocaleString() }}
                    </span>
                  </div>
                  <div class="flex justify-between gap-4 border-t pt-1">
                    <span class="font-medium">Utilized:</span>
                    <span class="font-semibold">
                      ₱{{ Number(props.pettyCashFund.fund_amount - props.pettyCashFund.fund_balance).toLocaleString() }}
                    </span>
                  </div>
                </div>
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
            <TableHead class="w-[150px]">PCV NO</TableHead>
            <TableHead class="w-[100px]">Paid to</TableHead>
            <TableHead class="w-[150px]">Requested By</TableHead>
            <TableHead class="w-[100px]">Date</TableHead>
            <TableHead class="w-[100px]">Status</TableHead>
            <TableHead class="w-[200px]">Remarks</TableHead>
            <TableHead class="w-[50px] text-right">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in pettyCash" :key="item.id">
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
