<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Trash2 } from 'lucide-vue-next'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxItemIndicator,
  ComboboxList,
  ComboboxTrigger
} from '@/components/ui/combobox'
import { Check, ChevronsUpDown, Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps<{
  detail: any
  index: number
  accounts: any[]
  isCashVoucher: boolean
}>()

const emit = defineEmits([
  'update:account',
  'update:tag',
  'update:hours',
  'update:rate',
  'update:amount',
  'remove'
])

const accountSearchQuery = ref('')

const filteredAccounts = computed(() => {
  if (!accountSearchQuery.value) return props.accounts
  const query = accountSearchQuery.value.toLowerCase()
  return props.accounts.filter(account =>
    account.account_title.toLowerCase().includes(query)
  )
})
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 pb-4 border-b last:border-0">
    <!-- Account Selection with Combobox -->
    <div class="grid gap-2">
      <Label :for="`account-${index}`">Account *</Label>
      <Combobox :model-value="detail.account_id" @update:model-value="val => emit('update:account', val)" by="id">
        <ComboboxAnchor as-child>
          <ComboboxTrigger as-child>
            <Button variant="outline" class="w-full justify-between" :disabled="isCashVoucher">
              {{ accounts.find(a => a.id == detail.account_id)?.account_title || 'Select account' }}
              <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
          </ComboboxTrigger>
        </ComboboxAnchor>

        <ComboboxList class="max-h-[180px] overflow-y-auto">
          <div class="relative w-full items-center">
            <ComboboxInput
              class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10"
              placeholder="Search accounts..." v-model="accountSearchQuery" />
            <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
              <Search class="size-4 text-muted-foreground" />
            </span>
          </div>

          <ComboboxEmpty v-if="filteredAccounts.length === 0">
            No accounts found.
          </ComboboxEmpty>

          <ComboboxGroup>
            <div class="max-h-[150px] overflow-y-auto">
              <ComboboxItem v-for="account in filteredAccounts.slice(0, 5)"
                :key="account.id" :value="account.id.toString()">
                {{ account.account_title }}
                <ComboboxItemIndicator>
                  <Check class="ml-auto h-4 w-4" />
                </ComboboxItemIndicator>
              </ComboboxItem>
            </div>
          </ComboboxGroup>
        </ComboboxList>
      </Combobox>
    </div>

    <!-- Charging Tag -->
    <div class="grid gap-2">
      <Label :for="`tag-${index}`">Charging Tag *</Label>
      <Select v-model="detail.charging_tag" @update:model-value="val => emit('update:tag', val)" :disabled="isCashVoucher">
        <SelectTrigger>
          <SelectValue placeholder="Choose Charging Tag" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="C">C</SelectItem>
          <SelectItem value="D">D</SelectItem>
        </SelectContent>
      </Select>
    </div>

    <!-- Hours -->
    <div class="grid gap-2">
      <Label :for="`hours-${index}`">Hours</Label>
      <Input :id="`hours-${index}`" type="number" step="0.01" :model-value="detail.hours"
        @update:model-value="val => emit('update:hours', val)" placeholder="Optional"
        :disabled="isCashVoucher" />
    </div>

    <!-- Rate -->
    <div class="grid gap-2">
      <Label :for="`rate-${index}`">Rate</Label>
      <Input :id="`rate-${index}`" type="number" step="0.01" :model-value="detail.rate"
        @update:model-value="val => emit('update:rate', val)" placeholder="Optional"
        :disabled="isCashVoucher" />
    </div>

    <!-- Amount -->
    <div class="grid gap-2">
      <Label :for="`amount-${index}`">Amount *</Label>
      <div class="flex gap-2">
        <Input :id="`amount-${index}`" type="number" step="0.01" :model-value="detail.amount"
          @update:model-value="val => emit('update:amount', val)" required class="flex-1"
          :disabled="isCashVoucher" />
        <Button type="button" variant="destructive" size="icon"
          @click="emit('remove', index)"
          :disabled="isCashVoucher">
          <Trash2 class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </div>
</template>