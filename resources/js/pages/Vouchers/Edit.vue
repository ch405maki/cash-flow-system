<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';
import VoucherHeaderForm from '@/components/vouchers/edit/VoucherHeaderForm.vue';
import VoucherAccountsForm from '@/components/vouchers/edit/VoucherAccountsForm.vue';
import VoucherDatesForm from '@/components/vouchers/edit/VoucherDatesForm.vue';
import { Button } from '@/components/ui/button'
import { CardFooter } from '@/components/ui/card'


const { props } = usePage();
const toast = useToast();
const accounts = props.accounts || [];
const voucher = props.voucher;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: `${voucher.voucher_no}`, href: `/vouchers/${voucher.id}/edit` },
];

const form = useForm({
    id: voucher.id,
    issue_date: voucher.issue_date,
    payment_date: voucher.payment_date,
    check_date: voucher.check_date,
    delivery_date: voucher.delivery_date,
    voucher_date: voucher.voucher_date,
    purpose: voucher.purpose,
    payee: voucher.payee,
    check_no: voucher.check_no,
    remarks: voucher.remarks,
    check_payable_to: voucher.check_payable_to,
    check_amount: voucher.check_amount,
    status: voucher.status,
    type: voucher.type,
    user_id: voucher.user_id,
    voucher_no: voucher.voucher_no,
    check: voucher.details.map(detail => ({
        id: detail.id,
        amount: detail.amount,
        charging_tag: detail.charging_tag,
        hours: detail.hours,
        rate: detail.rate,
        account_id: detail.account_id.toString()
    })),
    receipt: null,
});

const isCashVoucher = computed(() => form.type === 'cash');

const calculateTotalAmount = () => {
    form.check_amount = form.check.reduce((sum, item) => {
        return sum + (parseFloat(item.amount) || 0);
    }, 0);
};

const addDetailItem = () => {
    if (form.check.length > 0) {
        const lastItem = form.check[form.check.length - 1];
        if (!lastItem.account_id || !lastItem.amount) {
            toast.warning('Please fill the current account details before adding a new one');
            return;
        }
    }

    form.check.push({
        id: null,
        amount: 0,
        charging_tag: '',
        hours: '',
        rate: '',
        account_id: ''
    });
};

const removeDetailItem = async (index) => {
    if (form.check.length > 1) {
        const detail = form.check[index];

        if (detail.id) {
            try {
                await axios.delete(`/api/vouchers/details/${detail.id}`);
                toast.success('Account detail removed successfully');
            } catch (error) {
                toast.error('Failed to remove account detail');
                return;
            }
        }
        form.check.splice(index, 1);
        calculateTotalAmount();
    }
};

const calculateAmountFromRate = (index) => {
    const detail = form.check[index];
    if (detail.hours && detail.rate) {
        detail.amount = parseFloat(detail.hours) * parseFloat(detail.rate);
        calculateTotalAmount();
    }
};

const handleFileSelected = async (file) => {
    try {
        const formData = new FormData();
        formData.append('receipt', file);
        
        const response = await axios.post(
            `/api/vouchers/${voucher.id}/receipt`,
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            }
        );
        
        toast.success(response.data.message);
        // Optionally update local state if needed
        form.receipt = response.data.data.receipt_path;
    } catch (error) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((msg) => {
                toast.error(msg[0]);
            });
        } else {
            toast.error(error.response?.data?.message || 'Failed to upload receipt');
        }
    }
};

// Keep your existing updateVoucher function as is
async function updateVoucher() {
    try {
        const accountIds = form.check.map(item => item.account_id);
        const uniqueAccountIds = new Set(accountIds);

        if (accountIds.length !== uniqueAccountIds.size) {
            toast.error('Duplicate account entries with the same name detected.');
            return;
        }
        const response = await axios.put(`/api/vouchers/${voucher.id}`, form);
        toast.success(response.data.message || 'Voucher updated successfully');
        router.visit('/vouchers');
    } catch (error) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((msg) => {
                toast.error(msg[0]);
            });
        } else {
            toast.error(error.response?.data?.message || 'Failed to update Voucher');
        }
    }
}
</script>

<template>
    <Head :title="`Voucher Details - ${voucher.voucher_no}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto w-full px-4 pb-10">
            <div>
                <form @submit.prevent="updateVoucher">
                    <VoucherHeaderForm 
                        :form="form" 
                        :is-cash-voucher="isCashVoucher"
                        :voucher-no="voucher.voucher_no"
                    />
                    
                    <VoucherAccountsForm 
                        :form="form" 
                        :voucher="voucher" 
                        :accounts="accounts" 
                        :is-cash-voucher="isCashVoucher"
                        @add-detail="addDetailItem"
                        @remove-detail="removeDetailItem"
                        @calculate-amount="calculateAmountFromRate"
                        @calculate-total="calculateTotalAmount"
                    />
                    
                    <VoucherDatesForm 
                        :form="form" 
                        @file-selected="handleFileSelected"
                    />
                    
                    <CardFooter class="flex justify-end gap-4 px-0 pb-0">
                        <Button variant="outline" type="button" @click="router.visit('/vouchers')">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Voucher</span>
                        </Button>
                    </CardFooter>
                </form>
            </div>
        </div>
    </AppLayout>
</template>