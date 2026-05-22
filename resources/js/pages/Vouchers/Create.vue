<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { CardFooter } from '@/components/ui/card';
import { Ban, FilePlus2 } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { useVoucherForm } from '@/composables/useVoucherForm';
import VoucherNumberField from '@/components/vouchers/create/VoucherNumberField.vue';
import VoucherBasicFields from '@/components/vouchers/create/VoucherBasicFields.vue';
import VoucherLineItems from '@/components/vouchers/create/VoucherLineItems.vue';

interface Props {
  accounts?: Array<{ id: number; account_title: string }>;
  auth: { user: { id: number } };
  voucher_number: string;
  purchase_order?: {
    id: number;
    po_no: string;
    tin_no: string;
    payee?: string;
    check_payable_to?: string;
    amount?: number;
  };
  distribution_expenses?: Array<{
    id: number;
    account_name: string;
    amount: number;
    date: string;
    petty_cash_id: number;
    account_id?: number;
  }>;
  petty_cash?: {
    id: number;
    pcv_no: string;
    paid_to: string;
    remarks?: string;
  };
  prefilled_data?: {
    paid_to?: string;
    total_amount?: number;
    remarks?: string;
  };
}

const props = defineProps<Props>();

const {
  form, isCashVoucher, voucherNumberError, checkingVoucher,
  checkVoucherNumber, suggestVoucherNumber, submitVoucher,
} = useVoucherForm(props);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Vouchers', href: '/vouchers' },
  { title: 'Create Voucher', href: '#' },
];
</script>

<template>
  <Head title="Create Voucher" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-6 mx-auto w-full">
      <div class="flex justify-between items-center">
        <PageHeader
          title="Create Voucher"
          subtitle="Complete all required fields"
        />
        <div class="text-right">
          <VoucherNumberField
            :model-value="form.voucher_no"
            :error="voucherNumberError"
            :checking="checkingVoucher"
            :purchase-order="purchase_order"
            @update:model-value="form.voucher_no = $event"
            @blur="checkVoucherNumber"
            @suggest="suggestVoucherNumber"
          />
        </div>
      </div>

      <form @submit.prevent="submitVoucher" class="space-y-6">
        <VoucherBasicFields :form="form" />

        <VoucherLineItems
          :form="form"
          :accounts="accounts || []"
          :is-cash-voucher="isCashVoucher"
        />

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 hidden">
          <div class="grid gap-2">
            <Label for="issue_date">Issue Date *</Label>
            <Input id="issue_date" type="date" v-model="form.issue_date" />
          </div>
          <div class="grid gap-2">
            <Label for="payment_date">Payment Date *</Label>
            <Input id="payment_date" type="date" v-model="form.payment_date" />
          </div>
          <div class="grid gap-2">
            <Label for="delivery_date">Delivery Date *</Label>
            <Input id="delivery_date" type="date" v-model="form.delivery_date" />
          </div>
        </div>

        <CardFooter class="flex justify-end gap-2 px-0 pb-0">
          <Button type="button" variant="outline" @click="router.visit('/vouchers')">
            <Ban />Cancel
          </Button>
          <Button type="submit" :disabled="!isCashVoucher && form.items.length === 0">
            <FilePlus2 class="h-4 w-4" /> Create Voucher
          </Button>
        </CardFooter>
      </form>
    </div>
  </AppLayout>
</template>
