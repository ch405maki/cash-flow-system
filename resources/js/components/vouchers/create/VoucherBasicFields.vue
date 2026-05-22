<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

defineProps<{
  form: {
    payee: string;
    tin_no: string;
    voucher_date: string;
    check_payable_to: string;
    check_amount: number;
    purpose: string;
  };
}>();
</script>

<template>
  <div class="grid grid-cols-3 gap-4">
    <div class="grid gap-2">
      <Label for="payee">Payee *</Label>
      <Input id="payee" v-model="form.payee" required />
    </div>
    <div class="grid gap-2">
      <Label for="tin_no">TIN</Label>
      <Input id="tin_no" v-model="form.tin_no" />
    </div>
    <div class="grid gap-2">
      <Label for="voucher_date">Voucher Date *</Label>
      <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
    </div>
  </div>

  <div class="grid grid-cols-2 gap-4">
    <div class="grid gap-2">
      <Label for="check_payable_to">Check Pay To *</Label>
      <Input id="check_payable_to" v-model="form.check_payable_to" required />
    </div>
    <div class="grid gap-2">
      <Label for="check_amount">Check Amount</Label>
      <Input id="check_amount" type="number" step="1" v-model="form.check_amount" :disabled="true" />
    </div>
  </div>

  <div class="grid gap-2">
    <Label for="purpose">Purpose *</Label>
    <Textarea id="purpose" v-model="form.purpose" required />
  </div>
</template>
