<script setup lang="ts">
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
        <p class="font-medium text-muted-foreground">Voucher Details</p>
        <table class="w-full text-sm border border-border rounded-md">
            <thead>
            <tr class="bg-gray-50 border-b">
            <th colspan="4" class="p-2 text-left text-muted-foreground">
                VOUCHER TYPE:
                <span class="uppercase font-normal">{{ voucher.type }}</span> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                STATUS:
                <span class="uppercase font-normal" :class="{ 'text-red-600': voucher.status === 'unreleased' }">
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
                <td class="p-2">{{ formatDate(voucher.check_date) }}</td>
            </tr>
        </tbody>
        </table>
    </div>
</template>