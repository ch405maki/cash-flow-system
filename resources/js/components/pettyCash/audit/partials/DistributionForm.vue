<script setup lang="ts">
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'

const toast = useToast()

const props = defineProps<{
  pettyCashId: string | number
  accounts: any[]
}>()

const emit = defineEmits(['distribution-added'])

const distribution = reactive({
  account_id: '',
  amount: '',
  date: new Date().toISOString().slice(0, 10),
})

const submitDistribution = async () => {
  router.post(
    route('audit.petty-cash.distribution', props.pettyCashId),
    distribution,
    {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Distribution added successfully!')
        distribution.account_id = ''
        distribution.amount = ''
        distribution.date = new Date().toISOString().slice(0, 10)
        emit('distribution-added')
      },
      onError: () => {
        toast.error('Failed to save distribution.')
      },
    }
  )
}
</script>

<template>
  <div class="mt-6 border rounded-lg p-4">
    <h3 class="text-lg font-semibold mb-2">Distribution of Expense</h3>

    <form @submit.prevent="submitDistribution" class="space-y-4">
      <div class="grid grid-cols-[1fr_1fr_1fr_auto] gap-4 items-end">
        <!-- Account Name -->
        <div>
          <Label>Account Name</Label>
          <Select v-model="distribution.account_id">
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select Account" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="account in accounts"
                :key="account.id"
                :value="account.id"
              >
                {{ account.account_title }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Amount -->
        <div>
          <Label>Amount</Label>
          <Input v-model="distribution.amount" type="number" step="0.01" class="w-full" />
        </div>

        <!-- Date -->
        <div>
          <Label>Date</Label>
          <Input v-model="distribution.date" type="date" class="w-full" />
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
          <Button
            type="submit"
            class="px-4 whitespace-nowrap"
            :disabled="!distribution.account_id || !distribution.amount || !distribution.date"
          >
            Save
          </Button>
        </div>
      </div>
    </form>
  </div>
</template>