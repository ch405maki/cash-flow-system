<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Plus } from 'lucide-vue-next'
import AccountDetailRow from './AccountDetailRow.vue'

defineProps<{
  form: any
  accounts: any[]
  isCashVoucher: boolean
  addDetailItem: () => void
  removeDetailItem: (index: number) => void
  calculateAmountFromRate: (index: number) => void
  calculateTotalAmount: () => void
}>()
</script>

<template>
  <div class="border rounded-lg p-4 mb-6">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-medium">Update Accounts</h3>
      <Button type="button" variant="outline" size="sm" @click="$emit('add-detail')">
        <Plus class="h-4 w-4 mr-2" />
        Add Account
      </Button>
    </div>

    <AccountDetailRow
      v-for="(detail, index) in form.check"
      :key="index"
      :detail="detail"
      :index="index"
      :accounts="accounts"
      :is-cash-voucher="isCashVoucher"
      @update:account="(val) => form.check[index].account_id = val"
      @update:tag="(val) => form.check[index].charging_tag = val"
      @update:hours="(val) => { form.check[index].hours = val; calculateAmountFromRate(index) }"
      @update:rate="(val) => { form.check[index].rate = val; calculateAmountFromRate(index) }"
      @update:amount="(val) => { form.check[index].amount = val; calculateTotalAmount() }"
      @remove="removeDetailItem(index)"
    />
  </div>
</template>