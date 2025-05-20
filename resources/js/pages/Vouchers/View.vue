<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types';
import { format } from 'date-fns'

const { props } = usePage();
const accounts = props.accounts || [];
const voucher = props.voucher;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: `View Voucher: ${voucher.voucher_no}`, href: `/vouchers/${voucher.id}` },
];



// Date formatting function using date-fns
const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return format(new Date(dateString), 'MMM dd, yyyy');
};
</script>

<template>
    <Head :title="`View Voucher - ${voucher.voucher_no}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card class="mt-6 mx-auto w-full">
            <CardHeader>
                <CardTitle>Voucher {{ voucher.voucher_no }}</CardTitle>
                <CardDescription>Voucher Details</CardDescription>
            </CardHeader>

            <CardContent>
                <!-- Voucher Header Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Column 1 -->
                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="voucher_no">Voucher Number</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ voucher.voucher_no }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="voucher_date">Voucher Date</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formatDate(voucher.voucher_date) }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="payee">Payee</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ voucher.payee }}</div>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="check_no">Check Number</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ voucher.check_no }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="check_date">Check Date</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formatDate(voucher.check_date) }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="check_payable_to">Payable To</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ voucher.check_payable_to }}</div>
                        </div>
                    </div>
                    
                    <!-- Column 3 -->
                    <div class="space-y-4">
                        <div class="grid gap-2">
                            <Label for="check_amount">Check Amount</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ voucher.check_amount }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="purpose">Purpose</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ voucher.purpose }}</div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="status">Status</Label>
                            <div class="text-center py-2 px-2 rounded-full font-bold capitalize inline-block min-w-[100px]"
                                :class="{
                                'bg-yellow-100 text-yellow-800': voucher.status === 'pending',
                                'bg-green-100 text-green-800': voucher.status === 'paid',
                                'bg-red-100 text-red-800': voucher.status === 'rejected',
                                }">{{ voucher.status }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voucher Details Section -->
                <div v-if="voucher.type !== 'cash'" class="border rounded-lg p-4 mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-medium">Account Details</h3>
                    </div>

                    <div 
                        v-for="(detail, index) in voucher.details" 
                        :key="index"
                        class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 pb-4 border-b last:border-0"
                    >
                        <!-- Account Selection -->
                        <div class="grid gap-2">
                            <Label :for="`account-${index}`">Account</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">
                                {{ accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A' }}
                            </div>
                        </div>

                        <!-- Charging Tag -->
                        <div class="grid gap-2">
                            <Label :for="`tag-${index}`">Charging Tag</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ detail.charging_tag }}</div>
                        </div>

                        <!-- Hours -->
                        <div class="grid gap-2">
                            <Label :for="`hours-${index}`">Hours</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ detail.hours || 'N/A' }}</div>
                        </div>

                        <!-- Rate -->
                        <div class="grid gap-2">
                            <Label :for="`rate-${index}`">Rate</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ detail.rate || 'N/A' }}</div>
                        </div>

                        <!-- Amount -->
                        <div class="grid gap-2">
                            <Label :for="`amount-${index}`">Amount</Label>
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ detail.amount }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Dates Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="grid gap-2">
                        <Label for="issue_date">Issue Date</Label>
                        <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formatDate(voucher.issue_date) }}</div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="payment_date">Payment Date</Label>
                        <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formatDate(voucher.payment_date) }}</div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="delivery_date">Delivery Date</Label>
                        <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formatDate(voucher.delivery_date) }}</div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>