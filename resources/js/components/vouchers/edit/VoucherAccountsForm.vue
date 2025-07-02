<script setup lang="ts">
import { ref, computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Plus, Trash2, Check, ChevronsUpDown, Search } from 'lucide-vue-next'
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

const props = defineProps({
    form: Object,
    accounts: Array,
    voucher: Array,
    isCashVoucher: Boolean
});

const emit = defineEmits(['add-detail', 'remove-detail', 'calculate-amount', 'calculate-total']);

const accountSearchQuery = ref('')

const filteredAccounts = computed(() => {
    if (!accountSearchQuery.value) return props.accounts

    const query = accountSearchQuery.value.toLowerCase()
    return props.accounts.filter(account => account.account_title.toLowerCase().includes(query))
})
</script>

<template>
    <div class="border rounded-lg p-4 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-medium">Update Accounts</h3>
            <Button v-if="voucher.status !== 'forCheck'" type="button" variant="outline" size="sm" @click="emit('add-detail')">
                <Plus class="h-4 w-4 mr-2" />
                Add Account
            </Button>
        </div>

        <div v-for="(detail, index) in form.check" :key="index" 
            class="grid gap-4 mb-4 pb-4 border-b last:border-0"
            :class="isCashVoucher ? 'grid-cols-3 md:grid-cols-3' : 'grid-cols-5 md:grid-cols-5'"
        >
            <!-- Account Selection with Combobox -->
            <div class="grid gap-2">
                <Label :for="`account-${index}`">Account *</Label>
                <Combobox v-model="detail.account_id"  by="id">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <Button variant="outline" class="w-full justify-between">
                                {{accounts.find(a => a.id == detail.account_id)?.account_title ||
                                'Select account' }}
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                            </Button>
                        </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList class="max-h-[180px] overflow-y-auto">
                        <div class="relative w-full items-center">
                            <ComboboxInput
                                class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10"
                                placeholder="Search accounts..." v-model="accountSearchQuery" />
                            <span
                                class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
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
                <Select v-model="detail.charging_tag">
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
            <div class="grid gap-2" v-if="!isCashVoucher">
                <Label :for="`hours-${index}`">Hours</Label>
                <Input :id="`hours-${index}`" type="number" step="0.01" v-model="detail.hours"
                    @blur="emit('calculate-amount', index)" placeholder="Optional"
                    :disabled="isCashVoucher" />
            </div>

            <!-- Rate -->
            <div class="grid gap-2" v-if="!isCashVoucher">
                <Label :for="`rate-${index}`">Rate</Label>
                <Input :id="`rate-${index}`" type="number" step="0.01" v-model="detail.rate"
                    @blur="emit('calculate-amount', index)" placeholder="Optional"
                    :disabled="isCashVoucher" />
            </div>

            <!-- Amount -->
            <div class="grid gap-2">
                <Label :for="`amount-${index}`">Amount *</Label>
                <div class="flex gap-2">
                    <Input :id="`amount-${index}`" type="number" step="0.01" v-model="detail.amount"
                        @change="emit('calculate-total')" required class="flex-1"/>
                    <Button v-if="voucher.status !== 'forCheck'" type="button" variant="destructive" size="icon"
                        @click="emit('remove-detail', index)"
                        :disabled="form.check.length <= 1">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>