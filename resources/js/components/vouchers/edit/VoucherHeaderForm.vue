<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { CreditCard, Hash, CalendarCheck } from 'lucide-vue-next'
import PageHeader from '@/components/PageHeader.vue';

defineProps<{
  form: any,
  isCashVoucher: boolean,
  voucherNo: string
}>()
</script>

<template>
    <div class="py-4">
        <PageHeader 
            :title="`Voucher # ${ voucherNo }`" 
            subtitle="Update voucher details"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Column 1 -->
        <div class="space-y-4">
            <div class="grid gap-2">
                <Label for="voucher_no">Voucher Type</Label>
                <Input class="capitalize" id="voucher_no" v-model="form.type" disabled />
            </div>

            <div class="grid gap-2">
                <Label for="payee">Payee *</Label>
                <Input id="payee" v-model="form.payee" required />
            </div>

            <div class="grid gap-2">
                <Label for="check_payable_to">Check Pay To *</Label>
                <Input id="check_payable_to" v-model="form.check_payable_to" required />
            </div>

            <div class="grid gap-2">
                <Label for="purpose">Purpose *</Label>
                <Input id="purpose" v-model="form.purpose" required />
            </div>
        </div>

        <!-- Column 2 -->
        <div class="space-y-4">
            <div class="grid gap-2">
                <Label for="voucher_date">Voucher Date *</Label>
                <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
            </div>
            <div class="grid gap-2">
                <Label for="check_amount">Check Amount *</Label>
                <Input id="check_amount" type="number" step="0.01" v-model="form.check_amount" readonly/>
            </div>
            <div class="grid gap-2" v-if="form.status === 'forCheck'">
                <Label for="check_no" class="flex items-center gap-1.5 text-red-600">
                    <CreditCard class="h-4 w-4" />
                    <span class="text-red-600">Check Number *</span>
                </Label>
                <div class="relative">
                    <Input
                        id="check_no"
                        v-model="form.check_no"
                        class="w-full pl-9"
                        placeholder="Enter check number"
                    />
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <Hash class="h-4 w-4 text-border-200" />
                    </div>
                </div>
            </div>
            <div class="grid gap-2">
                <Label for="check_date" class="flex items-center gap-1.5 text-red-600">
                    <CalendarCheck class="h-4 w-4" /><span>Check Date *</span></Label>
                <Input id="check_date" type="date" 
                v-model="form.check_date" />
            </div>
        </div>
    </div>
</template>