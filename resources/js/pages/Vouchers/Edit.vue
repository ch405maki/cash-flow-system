<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select'
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Plus, Trash2 } from 'lucide-vue-next'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref, onMounted, computed } from 'vue'
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';

const { props } = usePage();
const toast = useToast();
const accounts = props.accounts || [];
const voucher = props.voucher; // The voucher being edited

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: `Voucher Details: ${voucher.voucher_no}`, href: `/vouchers/${voucher.id}/edit` },
];

// Initialize form with existing voucher data
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
        account_id: detail.account_id.toString() // Ensure account_id is string for Select component
    }))
});

const addDetailItem = () => {
    // Check if the last item is empty (newly added but not filled yet)
    if (form.check.length > 0) {
        const lastItem = form.check[form.check.length - 1];
        if (!lastItem.account_id || !lastItem.amount) {
            toast.warning('Please fill the current account details before adding a new one');
            return;
        }
    }

    form.check.push({
        id: null, // This will be null for new entries
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
        
        // If this is an existing record (has an ID), confirm deletion
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

const calculateTotalAmount = () => {
    form.check_amount = form.check.reduce((sum, item) => {
        return sum + (parseFloat(item.amount) || 0);
    }, 0);
};

const calculateAmountFromRate = (index) => {
    const detail = form.check[index];
    if (detail.hours && detail.rate) {
        detail.amount = parseFloat(detail.hours) * parseFloat(detail.rate);
        calculateTotalAmount();
    }
};

const isCashVoucher = computed(() => form.type === 'cash');

async function updateVoucher() {
    try {
        // Check for duplicate account IDs before submitting
        const accountIds = form.check.map(item => item.account_id);
        const uniqueAccountIds = new Set(accountIds);
        
        if (accountIds.length !== uniqueAccountIds.size) {
            toast.error('Duplicate account entries with the same name detected.');
            return;
        }

        const response = await axios.put(`/api/vouchers/${voucher.id}`, form);

        toast.success(response.data.message || 'Voucher updated successfully');

        // Redirect to the vouchers index page
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
        <Card class="mt-6 mx-auto w-full">
            <CardHeader>
                <CardTitle>Voucher {{ voucher.voucher_no }}</CardTitle>
                <CardDescription>Update voucher details</CardDescription>
            </CardHeader>

            <CardContent>
                <form @submit.prevent="updateVoucher">
                    <!-- Voucher Header Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Column 1 -->
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="voucher_no">Voucher Number</Label>
                                <Input id="voucher_no" v-model="form.voucher_no" disabled />
                            </div>

                            <div class="grid gap-2">
                                <Label for="voucher_date">Voucher Date *</Label>
                                <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
                            </div>

                            <div class="grid gap-2">
                                <Label for="payee">Payee *</Label>
                                <Input id="payee" v-model="form.payee" required />
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="check_no">Check Number *</Label>
                                <Input id="check_no" v-model="form.check_no"/>
                            </div>

                            <div class="grid gap-2">
                                <Label for="check_date">Check Date *</Label>
                                <Input id="check_date" type="date" v-model="form.check_date" required />
                            </div>

                            <div class="grid gap-2">
                                <Label for="check_payable_to">Payable To *</Label>
                                <Input id="check_payable_to" v-model="form.check_payable_to" required />
                            </div>
                        </div>

                        <!-- Column 3 -->
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="check_amount">Check Amount *</Label>
                                <Input 
                                    id="check_amount" 
                                    type="number" 
                                    step="0.01" 
                                    v-model="form.check_amount"
                                    :disabled="!isCashVoucher"
                                />
                            </div>

                            <div class="grid gap-2">
                                <Label for="purpose">Purpose *</Label>
                                <Input id="purpose" v-model="form.purpose" required />
                            </div>

                            <div class="grid gap-2">
                                 <Label for="status">Status *</Label>
                                    <Select v-model="form.status" required>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="pending">Pending</SelectItem>
                                        <SelectItem value="paid">Paid</SelectItem>
                                        <SelectItem value="rejected">Rejected</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                        </div>
                    </div>

                    <!-- Voucher Details Section -->
                    <div v-if="form.type !== 'cash'" class="border rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-medium">Update Accounts</h3>
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm" 
                                @click="addDetailItem"
                                :disabled="form.type === 'cash'"
                            >
                                <Plus class="h-4 w-4 mr-2" />
                                Add Account
                            </Button>
                        </div>

                        <div 
                            v-for="(detail, index) in form.check" 
                            :key="index"
                            class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 pb-4 border-b last:border-0"
                        >
                            <!-- Account Selection -->
                            <div class="grid gap-2">
                                <Label :for="`account-${index}`">Account *</Label>
                                <Select v-model="detail.account_id" required :disabled="form.type === 'cash'">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select account">
                                            {{ accounts.find(a => a.id == detail.account_id)?.account_title || 'Select account' }}
                                        </SelectValue>
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="account in accounts" :key="account.id" :value="account.id.toString()">
                                            {{ account.account_title }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Charging Tag -->
                            <div class="grid gap-2">
                                <Label :for="`tag-${index}`">Charging Tag *</Label>
                                <Select v-model="detail.charging_tag" :disabled="form.type === 'cash'">
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
                                <Input 
                                    :id="`hours-${index}`" 
                                    type="number" 
                                    step="0.01" 
                                    v-model="detail.hours"
                                    @blur="calculateAmountFromRate(index)" 
                                    placeholder="Optional"
                                    :disabled="form.type === 'cash'"
                                />
                            </div>

                            <!-- Rate -->
                            <div class="grid gap-2">
                                <Label :for="`rate-${index}`">Rate</Label>
                                <Input 
                                    :id="`rate-${index}`" 
                                    type="number" 
                                    step="0.01" 
                                    v-model="detail.rate"
                                    @blur="calculateAmountFromRate(index)" 
                                    placeholder="Optional"
                                    :disabled="form.type === 'cash'"
                                />
                            </div>

                            <!-- Amount -->
                            <div class="grid gap-2">
                                <Label :for="`amount-${index}`">Amount *</Label>
                                <div class="flex gap-2">
                                    <Input 
                                        :id="`amount-${index}`" 
                                        type="number" 
                                        step="0.01" 
                                        v-model="detail.amount"
                                        @change="calculateTotalAmount" 
                                        required 
                                        class="flex-1"
                                        :disabled="form.type === 'cash'"
                                    />
                                    <Button 
                                        type="button" 
                                        variant="destructive" 
                                        size="icon"
                                        @click="removeDetailItem(index)" 
                                        :disabled="form.check.length <= 1 || form.type === 'cash'"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dates Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="grid gap-2">
                            <Label for="issue_date">Issue Date *</Label>
                            <Input id="issue_date" type="date" v-model="form.issue_date" required />
                        </div>

                        <div class="grid gap-2">
                            <Label for="payment_date">Payment Date *</Label>
                            <Input id="payment_date" type="date" v-model="form.payment_date" required />
                        </div>

                        <div class="grid gap-2">
                            <Label for="delivery_date">Delivery Date *</Label>
                            <Input id="delivery_date" type="date" v-model="form.delivery_date" required />
                        </div>
                    </div>

                    <!-- Form Actions -->
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
            </CardContent>
        </Card>
    </AppLayout>
</template>