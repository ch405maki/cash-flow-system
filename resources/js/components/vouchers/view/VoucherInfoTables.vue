<script setup lang="ts">
import { Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

defineProps({
    voucher: {
        type: Object,
        required: true
    },
    formatDate: {
        type: Function,
        required: true
    },
    formatCurrency: {
        type: Function,
        required: true
    },
    formatStatus: {
        type: Function,
        required: true
    }
});
</script>

<template>

    <!-- First Table -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <p class="font-medium text-muted-foreground">Voucher Details</p>
            <div v-if="voucher.receipt">
                <Button variant="success">
                    <Download /> Download Receipt
                </Button>
            </div>
        </div>
        <table class="w-full text-sm border border-border rounded-md">
            <thead>
            <tr class="bg-gray-50 border-b">
            <th colspan="4" class="p-2 text-left text-muted-foreground">
                VOUCHER TYPE:
                <span class="uppercase font-normal">{{ voucher.type }}</span> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                STATUS:
                    <span class="uppercase font-bold tracking-wide"
                        :class="{
                            'text-green-600': voucher.status === 'completed',
                            'text-red-600': voucher.status === 'unreleased',
                            'text-purple-600': voucher.status === 'released',
                            'text-yellow-500': voucher.status === 'forCheck',
                            'text-blue-500': voucher.status === 'forEOD',
                            'text-gray-500': voucher.status === 'draft'
                        }">
                    {{ formatStatus(voucher.status) }}
                    </span>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                CHECK AMOUNT:
                <span class="uppercase font-normal">{{ formatCurrency(voucher.check_amount) }}</span>
            </th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b">
                <td class="p-2 font-medium text-muted-foreground border-r w-48">PAYEE:</td>
                <td class="p-2 uppercase border-r">{{ voucher.payee }}</td>
                <td class="p-2 font-medium text-muted-foreground border-r w-40">VOUCHER DATE:</td>
                <td class="p-2">{{ formatDate(voucher.voucher_date) }}</td>
            </tr>
            <tr class="border-b">
                <td class="p-2 font-medium text-muted-foreground border-r">CHECK PAYABLE TO:</td>
                <td class="p-2 uppercase border-r">{{ voucher.check_payable_to }}</td>
                <td class="p-2 font-medium text-muted-foreground border-r">CHECK NUMBER:</td>
                <td class="p-2">
                    <span >{{ voucher.check_no || '' }}</span>
                </td>
            </tr>
            <tr class="border-b">
                <td class="p-2 font-medium text-muted-foreground border-r">PURPOSE:</td>
                <td class="p-2 uppercase border-r">{{ voucher.purpose }}</td>
                <td class="p-2 font-medium text-muted-foreground border-r">CHECK DATE:</td>
                <td class="p-2">{{ voucher.check_date ? formatDate(voucher.check_date) : '' }}</td>
            </tr>
        </tbody>
        </table>
    </div>
</template>