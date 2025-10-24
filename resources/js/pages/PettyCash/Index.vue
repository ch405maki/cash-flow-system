<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue';
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { computed } from 'vue'
import { FileText, PhilippinePeso, Info } from 'lucide-vue-next'
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
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Badge } from '@/components/ui/badge'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash', href: '#' },
]
const user = usePage().props.auth.user;

const props = defineProps<{
  pettyCash: Record<string, any>[] | null
  pettyCashFund?: {
    fund_amount?: number
    fund_balance?: number
  } | null
  thresholds: {
    cash_advance: {
      limit: number
      used: number
      remaining: number
    }
    reimbursement: {
      limit: number
      used: number
      remaining: number
    }
  }
  today: string
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
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <PageHeader 
          title="Petty Cash" 
          subtitle="List of created petty cash."
        />

        <div class="flex items-center gap-3">
          <Popover v-if="user.role != 'bursar'">
            <PopoverTrigger as-child>
              <Button title="Daily Threshold Status" variant="outline" size="icon" class="h-9 w-9 border-orange-200 bg-orange-50 hover:bg-orange-100">
                <Info class="h-4 w-4 text-orange-600" />
              </Button>
            </PopoverTrigger>
            <PopoverContent class="">
              <div class="space-y-4">
                <!-- Header -->
                <div class="flex items-center gap-2">
                  <div class="flex items-center justify-center">
                    <Info class="h-3 w-3 text-orange-600" />
                  </div>
                  <h3 class="font-medium">Daily Threshold Status</h3>
                </div>

                <!-- Threshold Cards -->
                <div class="space-y-3">
                  <!-- Cash Advance Card -->
                  <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-sm font-medium text-gray-700">Cash Advance</span>
                      <Badge 
                        variant="outline" 
                        :class="thresholds.cash_advance.used >= thresholds.cash_advance.limit 
                          ? 'bg-red-50 text-red-700 border-red-200' 
                          : 'bg-green-50 text-green-700 border-green-200'"
                      >
                        {{ thresholds.cash_advance.used >= thresholds.cash_advance.limit ? 'Limit Reached' : 'Available' }}
                      </Badge>
                    </div>
                    
                    <div class="space-y-2">
                      <!-- Progress Bar -->
                      <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                          class="h-2 rounded-full transition-all duration-300"
                          :class="thresholds.cash_advance.used >= thresholds.cash_advance.limit 
                            ? 'bg-red-500' 
                            : 'bg-orange-500'"
                          :style="{ width: `${Math.min((thresholds.cash_advance.used / thresholds.cash_advance.limit) * 100, 100)}%` }"
                        ></div>
                      </div>
                      
                      <!-- Amounts -->
                      <div class="flex justify-between text-xs text-gray-600">
                        <span>Used: ₱{{ thresholds.cash_advance.used.toLocaleString() }}</span>
                        <span>Limit: ₱{{ thresholds.cash_advance.limit.toLocaleString() }}</span>
                      </div>
                      
                      <!-- Remaining -->
                      <div class="text-sm font-medium text-center" 
                          :class="thresholds.cash_advance.remaining <= 0 
                            ? 'text-red-600' 
                            : 'text-green-600'">
                        Remaining: ₱{{ thresholds.cash_advance.remaining.toLocaleString() }}
                      </div>
                    </div>
                  </div>

                  <!-- Reimbursement Card -->
                  <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                      <span class="text-sm font-medium text-gray-700">Reimbursement</span>
                      <Badge 
                        variant="outline" 
                        :class="thresholds.reimbursement.used >= thresholds.reimbursement.limit 
                          ? 'bg-red-50 text-red-700 border-red-200' 
                          : 'bg-green-50 text-green-700 border-green-200'"
                      >
                        {{ thresholds.reimbursement.used >= thresholds.reimbursement.limit ? 'Limit Reached' : 'Available' }}
                      </Badge>
                    </div>
                    
                    <div class="space-y-2">
                      <!-- Progress Bar -->
                      <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                          class="h-2 rounded-full transition-all duration-300"
                          :class="thresholds.reimbursement.used >= thresholds.reimbursement.limit 
                            ? 'bg-red-500' 
                            : 'bg-blue-500'"
                          :style="{ width: `${Math.min((thresholds.reimbursement.used / thresholds.reimbursement.limit) * 100, 100)}%` }"
                        ></div>
                      </div>
                      
                      <!-- Amounts -->
                      <div class="flex justify-between text-xs text-gray-600">
                        <span>Used: ₱{{ thresholds.reimbursement.used.toLocaleString() }}</span>
                        <span>Limit: ₱{{ thresholds.reimbursement.limit.toLocaleString() }}</span>
                      </div>
                      
                      <!-- Remaining -->
                      <div class="text-sm font-medium text-center" 
                          :class="thresholds.reimbursement.remaining <= 0 
                            ? 'text-red-600' 
                            : 'text-green-600'">
                        Remaining: ₱{{ thresholds.reimbursement.remaining.toLocaleString() }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </PopoverContent>
          </Popover>
          <TooltipProvider
            v-if="props.pettyCashFund && props.pettyCashFund.fund_balance !== undefined"
          >
            <Tooltip>
              <TooltipTrigger>
                <div
                  :class="[
                    'rounded-lg border px-5 py-2 flex items-center justify-between transition-colors cursor-pointer w-full',
                    fundStatus.color,
                  ]"
                >
                  <!-- Icon -->
                  <div
                    class="flex items-center justify-center w-10 h-10 rounded-full border-2 flex-shrink-0"
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
                v-if="item.status !== 'draft' && item.status !== 'requested' && item.status !== 'returned'"
                @click="router.get(route('petty-cash.view', item.id))"
              >
                View
              </Button>
              <Button
                v-if="item.status === 'draft' || item.status == 'returned'"
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
