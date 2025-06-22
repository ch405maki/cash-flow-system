<script setup lang="ts">
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

defineProps({
    voucher: {
        type: Object,
        required: true
    },
    accounts: {
        type: Array,
        required: true
    },
    formatCurrency: {
        type: Function,
        required: true
    }
});
</script>

<template>
    <div class="space-y-2 ">
        <h3 class="font-medium">Account Details</h3>
        <Table class="w-full text-sm border border-border rounded-md">
            <TableHeader>
                <TableRow>
                    <TableHead>Account</TableHead>
                    <TableHead>Charging Tag</TableHead>
                    <TableHead>Hours</TableHead>
                    <TableHead>Rate</TableHead>
                    <TableHead class="text-right">Amount</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="(detail, index) in voucher.details" :key="index">
                    <TableCell>
                        {{accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A'}}
                    </TableCell>
                    <TableCell>{{ detail.charging_tag }}</TableCell>
                    <TableCell>{{ detail.hours || 'N/A' }}</TableCell>
                    <TableCell>{{ detail.rate || 'N/A' }}</TableCell>
                    <TableCell class="text-right">₱{{ formatCurrency(detail.amount) }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell colspan="4" class="text-right font-medium">Total</TableCell>
                    <TableCell class="text-right font-bold">
                        ₱{{ formatCurrency(voucher.check_amount) }}
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>