<script setup lang="ts">
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'

// Components
import PettyCashHeader from './partials/PettyCashHeader.vue'
import PettyCashRemarks from './partials/PettyCashRemarks.vue'
import PettyCashItemsTable from './partials/PettyCashItemsTable.vue'
import DistributionForm from './partials/DistributionForm.vue'
import DistributionRecords from './partials/DistributionRecords.vue'
import ApprovalSection from './partials/ApprovalSection.vue'

const user = usePage().props.auth.user
const toast = useToast()

const props = defineProps<{
  pettyCash: any
  accounts: any[]
}>()

const existingItems = ref(props.pettyCash.items ?? [])

// Computed properties
const hasLiquidation = computed(() => {
  return existingItems.value.some(item => item.type === 'Liquidation')
})

const totalDistribution = computed(() => {
  if (!props.pettyCash.distribution_expenses) return 0
  return props.pettyCash.distribution_expenses.reduce(
    (sum, record) => sum + Number(record.amount || 0),
    0
  )
})

// Event handlers
const handleDistributionAdded = () => {
  // You could refetch data here or use events to update parent
  toast.success('Distribution added successfully!')
}

const handleApprovalSubmitted = () => {
  // Handle post-approval logic
  toast.success('Approval processed successfully!')
}
</script>

<template>
  <div class="space-y-4">
    <!-- Header -->
    <PettyCashHeader :petty-cash="props.pettyCash" />
    
    <!-- Remarks -->
    <PettyCashRemarks :petty-cash="props.pettyCash" />
    
    <!-- Items Table -->
    <PettyCashItemsTable :items="existingItems" />
    
    <!-- Distribution Form -->
    <DistributionForm
      :petty-cash-id="props.pettyCash.id"
      :accounts="props.accounts"
      @distribution-added="handleDistributionAdded"
    />
    
    <!-- Distribution Records -->
    <DistributionRecords
      :distribution-expenses="props.pettyCash.distribution_expenses"
      :petty-cash-id="props.pettyCash.id"
    />
    
    <!-- Approval Section -->
    <ApprovalSection
      :petty-cash-id="props.pettyCash.id"
      :user-role="user.role"
      :has-liquidation="hasLiquidation"
      @approval-submitted="handleApprovalSubmitted"
    />
  </div>
</template>