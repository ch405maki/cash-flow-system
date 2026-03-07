<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

import PettyCashHeader from '@/components/pettyCash/index/PettyCashHeader.vue'
import ThresholdStatus from '@/components/pettyCash/index/ThresholdStatus.vue'
import FundBalance from '@/components/pettyCash/index/FundBalance.vue'
import PettyCashTable from '@/components/pettyCash/index/PettyCashTable.vue'
import EmptyState from '@/components/pettyCash/index/EmptyState.vue'

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

// Active filter state - changed from activeTab
const activeFilter = ref('all')

// Check if there are petty cash records
const hasItem = computed(() => {
  const hasData = !!(props.pettyCash && props.pettyCash.length > 0)
  return hasData
})

// Ensure we always pass an array to the table
const tableData = computed(() => {
  return props.pettyCash || []
})

// Get unique statuses from data for dynamic filter
const uniqueStatuses = computed(() => {
  if (!props.pettyCash || props.pettyCash.length === 0) return []
  
  const statuses = [...new Set(props.pettyCash.map(item => item.status))]
  // Sort statuses in a logical order (customize as needed)
  const statusOrder = ['draft', 'requested', 'submitted', 'approved', 'liquidation', 'approved liquidation', 'released', 'completed', 'returned', 'rejected', 'cancelled']
  
  return statuses.sort((a, b) => {
    const indexA = statusOrder.indexOf(a)
    const indexB = statusOrder.indexOf(b)
    // If status not in order list, put it at the end
    if (indexA === -1) return 1
    if (indexB === -1) return -1
    return indexA - indexB
  })
})

// Count items per status
const statusCounts = computed(() => {
  if (!props.pettyCash) return {}
  
  return props.pettyCash.reduce((acc, item) => {
    const status = item.status
    acc[status] = (acc[status] || 0) + 1
    return acc
  }, {} as Record<string, number>)
})

// Filter data based on active filter - changed from activeTab
const filteredData = computed(() => {
  if (!props.pettyCash) return []
  if (activeFilter.value === 'all') return props.pettyCash
  return props.pettyCash.filter(item => item.status === activeFilter.value)
})

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

// Event handlers
const goToCreate = () => {
  router.get(route('petty-cash.create'))
}
</script>

<template>
  <Head title="Petty Cash" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <!-- Header Section with integrated filter -->
      <PettyCashHeader
        :user="user"
        :thresholds="thresholds"
        :petty-cash-fund="pettyCashFund"
        :fund-status="fundStatus"
        :has-item="hasItem"
        :active-filter="activeFilter" 
        :unique-statuses="uniqueStatuses"
        :status-counts="statusCounts"
        :total-count="pettyCash?.length || 0"
        @create-petty-cash="goToCreate"
        @update:activeFilter="activeFilter = $event" 
      >
        <template #threshold-content>
          <ThresholdStatus :thresholds="thresholds" />
        </template>
        
        <template v-if="props.pettyCashFund && props.pettyCashFund.fund_balance !== undefined" #fund-balance>
          <FundBalance 
            :petty-cash-fund="pettyCashFund"
            :fund-status="fundStatus"
          />
        </template>
      </PettyCashHeader>

      <!-- Content Section - Only the table, no filter here -->
      <div v-if="hasItem">
        <PettyCashTable
          :petty-cash="filteredData"
          :user="user"
        />
      </div>

      <!-- Empty State -->
      <EmptyState
        v-else
        :user="user"
        @create-petty-cash="goToCreate"
      />
    </div>
  </AppLayout>
</template>