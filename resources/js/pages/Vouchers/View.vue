<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

const { props } = usePage();
const accounts = props.accounts || [];
const voucher = props.voucher;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: `View Voucher: ${voucher.voucher_no}`, href: `/vouchers/${voucher.id}` },
];

const formattedDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return dateString;
        
        const months = [
            'January', 'February', 'March', 'April', 
            'May', 'June', 'July', 'August',
            'September', 'October', 'November', 'December'
        ];
        
        const day = date.getDate();
        const month = months[date.getMonth()];
        const year = date.getFullYear();
        
        return `${month} ${day}, ${year}`;
    } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
    }
};
</script>

<template>
    <Head :title="`View Voucher - ${voucher.voucher_no}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Card class="mt-6 mx-auto w-full">
            <CardHeader>
                <div class="flex justify-between items-start">
                    <div>
                        <CardTitle>Voucher {{ voucher.voucher_no }}</CardTitle>
                        <CardDescription>Voucher Details</CardDescription>
                    </div>
                    <Button 
                        variant="outline" 
                        @click="router.visit('/vouchers')"
                        class="flex items-center gap-2"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Button>
                </div>
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
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formattedDate(voucher.voucher_date) }}</div>
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
                            <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formattedDate(voucher.check_date) }}</div>
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

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Account</TableHead>
                                <TableHead>Charging Tag</TableHead>
                                <TableHead>Hours</TableHead>
                                <TableHead>Rate</TableHead>
                                <TableHead>Amount</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(detail, index) in voucher.details" :key="index">
                                <TableCell>
                                    {{ accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A' }}
                                </TableCell>
                                <TableCell>{{ detail.charging_tag }}</TableCell>
                                <TableCell>{{ detail.hours || 'N/A' }}</TableCell>
                                <TableCell>{{ detail.rate || 'N/A' }}</TableCell>
                                <TableCell>{{ detail.amount }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                
                <!-- Dates Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="grid gap-2">
                        <Label for="issue_date">Issue Date</Label>
                        <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formattedDate(voucher.issue_date) }}</div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="payment_date">Payment Date</Label>
                        <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formattedDate(voucher.payment_date) }}</div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="delivery_date">Delivery Date</Label>
                        <div class="p-2 border rounded-md bg-gray-50 text-sm">{{ formattedDate(voucher.delivery_date) }}</div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </AppLayout>
</template>