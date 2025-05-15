<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Plus, Trash2, Save, ArrowLeft } from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import { ref, computed, onMounted } from 'vue';
import { type BreadcrumbItem } from '@/types';

const toast = useToast();
const { props } = usePage();
const voucher = props.voucher;
const accounts = props.accounts || [];

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: `Edit Voucher ${voucher.voucher_no}`, href: '' }
];

// Form for editing voucher details
const form = useForm({
    voucher_id: voucher.id,
    details: voucher.details.map(detail => ({
        id: detail.id,
        account_id: detail.account_id,
        amount: detail.amount,
        charging_tag: detail.charging_tag,
        hours: detail.hours,
        rate: detail.rate,
        _destroy: false // For marking deletions
    }))
});

// Calculate total amount
const totalAmount = computed(() => {
    return form.details.reduce((sum, item) => {
        return item._destroy ? sum : sum + parseFloat(item.amount || 0);
    }, 0);
});

// Add new detail line
const addDetailItem = () => {
    form.details.push({
        id: null, // Indicates new record
        account_id: '',
        amount: 0,
        charging_tag: '',
        hours: null,
        rate: null,
        _destroy: false
    });
};

// Mark detail for deletion
const removeDetailItem = (index: number) => {
    if (form.details[index].id) {
        // Existing record - mark for deletion
        form.details[index]._destroy = true;
    } else {
        // New record - just remove from array
        form.details.splice(index, 1);
    }
};

// Calculate amount when hours or rate changes
const calculateAmountFromRate = (index: number) => {
    const detail = form.details[index];
    if (detail.hours && detail.rate) {
        detail.amount = parseFloat(detail.hours) * parseFloat(detail.rate);
    }
};

// Submit the form
const submit = () => {
    form.put(`/api/vouchers/${voucher.id}/details`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Voucher details updated successfully');
            router.visit(`/vouchers/${voucher.id}`);
        },
        onError: (errors) => {
            toast.error('Failed to update voucher details');
            if (errors.details) {
                toast.warning('Please check all line items for errors');
            }
        }
    });
};

// Go back to voucher view
const goBack = () => {
    router.visit(`/vouchers/${voucher.id}`);
};
</script>

<template>

    <Head :title="`Edit Voucher ${voucher.voucher_no}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card class="mt-6 max-w-6xl mx-auto">
            <CardHeader>
                <div class="flex justify-between items-start">
                    <div>
                        <CardTitle>Edit Voucher Details</CardTitle>
                        <CardDescription>
                            Voucher #{{ voucher.voucher_no }} - {{ voucher.purpose }}
                        </CardDescription>
                    </div>
                    <Button variant="outline" @click="goBack">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Voucher
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <form @submit.prevent="submit">
                    <!-- Voucher Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div>
                            <p class="font-medium">Payee:</p>
                            <p>{{ voucher.payee }}</p>
                        </div>
                        <div>
                            <p class="font-medium">Check Payable To:</p>
                            <p>{{ voucher.check_payable_to }}</p>
                        </div>
                        <div>
                            <p class="font-medium">Total Amount:</p>
                            <p>{{ totalAmount.toFixed(2) }}</p>
                        </div>
                    </div>

                    <!-- Voucher Details Section -->
                    <div class="border rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-medium">Voucher Line Items</h3>
                            <Button type="button" variant="outline" size="sm" @click="addDetailItem">
                                <Plus class="h-4 w-4 mr-2" />
                                Add Item
                            </Button>
                        </div>

                        <div v-for="(detail, index) in form.details" :key="index"
                            class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 pb-4 border-b last:border-0"
                            :class="{ 'opacity-50': detail._destroy }">

                            <!-- Account Selection -->
                            <div class="grid gap-2">
                                <Label :for="`account-${index}`">Account *</Label>
                                <Select :id="`account-${index}`" v-model="detail.account_id" :disabled="detail._destroy"
                                    required>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select account" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="account in accounts" :key="account.id" :value="account.id">
                                            {{ account.account_title }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Charging Tag -->
                            <div class="grid gap-2">
                                <Label :for="`tag-${index}`">Charging Tag *</Label>
                                <Input :id="`tag-${index}`" v-model="detail.charging_tag" :disabled="detail._destroy"
                                    required />
                            </div>

                            <!-- Hours -->
                            <div class="grid gap-2">
                                <Label :for="`hours-${index}`">Hours</Label>
                                <Input :id="`hours-${index}`" type="number" step="0.01" v-model="detail.hours"
                                    @blur="calculateAmountFromRate(index)" :disabled="detail._destroy"
                                    placeholder="Optional" />
                            </div>

                            <!-- Rate -->
                            <div class="grid gap-2">
                                <Label :for="`rate-${index}`">Rate</Label>
                                <Input :id="`rate-${index}`" type="number" step="0.01" v-model="detail.rate"
                                    @blur="calculateAmountFromRate(index)" :disabled="detail._destroy"
                                    placeholder="Optional" />
                            </div>

                            <!-- Amount & Actions -->
                            <div class="grid gap-2">
                                <Label :for="`amount-${index}`">Amount *</Label>
                                <div class="flex gap-2">
                                    <Input :id="`amount-${index}`" type="number" step="0.01" v-model="detail.amount"
                                        :disabled="detail._destroy" required class="flex-1" />
                                    <Button type="button" variant="destructive" size="icon"
                                        @click="removeDetailItem(index)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <CardFooter class="flex justify-end gap-4 px-0 pb-0">
                        <Button variant="outline" type="button" @click="goBack">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <Save class="h-4 w-4 mr-2" />
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </Button>
                    </CardFooter>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>