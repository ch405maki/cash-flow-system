<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'

// Components
import PettyCashHeader from '@/components/pettycash/index/PettyCashHeader.vue'
import ThresholdStatus from '@/components/pettycash/index/ThresholdStatus.vue'
import FundBalance from '@/components/pettycash/index/FundBalance.vue'
import PettyCashTable from '@/components/pettycash/index/PettyCashTable.vue'
import EmptyState from '@/components/pettycash/index/EmptyState.vue'

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

// Debug: Log all props when component mounts
onMounted(() => {
  console.log('🔍 PettyCashIndex Props:', {
    pettyCash: props.pettyCash,
    pettyCashLength: props.pettyCash?.length,
    pettyCashFund: props.pettyCashFund,
    thresholds: props.thresholds,
    user: user
  })
})

// Check if there are petty cash records - FIXED VERSION
const hasItem = computed(() => {
  const hasData = !!(props.pettyCash && props.pettyCash.length > 0)
  console.log('📊 hasItem computed:', hasData, 'pettyCash:', props.pettyCash)
  return hasData
})

// Ensure we always pass an array to the table
const tableData = computed(() => {
  return props.pettyCash || []
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
      <!-- Header Section -->
      <PettyCashHeader
        :user="user"
        :thresholds="thresholds"
        :petty-cash-fund="pettyCashFund"
        :fund-status="fundStatus"
        :has-item="hasItem"
        @create-petty-cash="goToCreate"
      >
        <template #threshold-content>
          <ThresholdStatus :thresholds="thresholds" />
        </template>
        
        <template v-if="props.pettyCashFund && props.pettyCashFund.fund_balance !== undefined"  #fund-balance>
          <FundBalance 
            :petty-cash-fund="pettyCashFund"
            :fund-status="fundStatus"
          />
        </template>
      </PettyCashHeader>

      <!-- Content Section -->
      <PettyCashTable
        v-if="hasItem"
        :petty-cash="tableData"
        :user="user"
      />

      <EmptyState
        v-else
        :user="user"
        @create-petty-cash="goToCreate"
      />
    </div>
  </AppLayout>
</template>