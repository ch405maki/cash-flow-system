<script setup lang="ts">
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next'

const props = defineProps<{
  distributionExpenses: any[]
  pettyCashId?: string | number
}>()

const totalDistribution = computed(() => {
  if (!props.distributionExpenses) return 0
  return props.distributionExpenses.reduce(
    (sum, record) => sum + Number(record.amount || 0),
    0
  )
})

const navigateToCreateVoucher = () => {
  if (!props.pettyCashId) return
  
  // Navigate to create voucher with just the petty cash ID
  router.get(route('vouchers.create', { petty_cash_id: props.pettyCashId }))
}
</script>

<template>
  <div v-if="distributionExpenses?.length" class="mt-6 border rounded-lg p-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Existing Distribution Records</h3>
      <Button 
        @click="navigateToCreateVoucher"
        class="flex items-center gap-2"
        :disabled="!pettyCashId"
      >
        <FileText class="h-4 w-4" />
        Create Voucher
      </Button>
    </div>
    
    <table class="w-full border-collapse text-sm">
      <thead class="bg-muted">
        <tr>
          <th class="p-2 border-b text-left">Account Name</th>
          <th class="p-2 border-b text-right">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="record in distributionExpenses"
          :key="record.id"
          class="border-b"
        >
          <td class="p-2">{{ record.account_name }}</td>
          <td class="p-2 text-right">₱{{ Number(record.amount).toLocaleString() }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="font-bold border-t">
          <td class="p-2 text-right pl-[80%]">Total:</td>
          <td class="p-2 text-right">₱{{ totalDistribution.toLocaleString() }}</td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>