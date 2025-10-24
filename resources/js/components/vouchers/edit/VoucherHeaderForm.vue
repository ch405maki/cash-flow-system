<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

defineProps<{
  form: any,
  isCashVoucher: boolean,
  voucherNo: string
}>()
</script>

<template>
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
                <Label for="tin_no">TIN *</Label>
                <Input id="tin_no" v-model="form.tin_no" required />
            </div>

            <div class="grid gap-2">
                <Label for="purpose">Purpose *</Label>
                <Textarea id="purpose" v-model="form.purpose" required />
            </div>
        </div>

        <!-- Column 2 -->
        <div class="space-y-4">
            <div class="grid gap-2">
                <Label for="voucher_no">Voucher Number *</Label>
                <Input id="voucher_no" v-model="form.voucher_no" required />
            </div>
            <div class="grid gap-2">
                <Label for="voucher_date">Voucher Date *</Label>
                <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
            </div>
            <div class="grid gap-2">
                <Label for="check_amount">Check Amount *</Label>
                <Input id="check_amount" type="number" step="0.01" v-model="form.check_amount" readonly/>
            </div>
            <div class="grid gap-2">
                <Label for="check_payable_to">Check Pay To *</Label>
                <Input id="check_payable_to" v-model="form.check_payable_to" required />
            </div>
        </div>
    </div>
</template>