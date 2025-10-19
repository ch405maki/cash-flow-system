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
    <div class="space-y-2">
        <h3 class="font-medium capitalize">{{ voucher.type }} Details</h3>
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="border-r">Charged</TableHead>
                    <TableHead class="border-r">Account</TableHead>
                    <TableHead class="text-right border-r">Amount</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="(detail, index) in voucher.details" :key="index">
                    <TableCell class="border-r">{{ detail.charging_tag }}</TableCell>
                    <TableCell class="border-r">
                        {{accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A'}}
                    </TableCell>
                    <TableCell class="border-r" v-if="voucher.type === 'salary'">{{ detail.hours || 'N/A' }}</TableCell>
                    <TableCell class="border-r" v-if="voucher.type === 'salary'">{{ detail.rate || 'N/A' }}</TableCell>
                    <TableCell class="text-right border-r">{{ formatCurrency(detail.amount) }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell colspan="5" class="text-right font-bold">
                        NET AMOUNT: {{ formatCurrency(voucher.check_amount) }}
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>