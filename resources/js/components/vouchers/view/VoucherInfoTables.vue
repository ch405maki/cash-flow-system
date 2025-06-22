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
    <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
        <!-- First Table -->
        <table class="w-full text-sm border border-border rounded-md">
            <tbody>
                <tr class="border-b">
                    <td class="p-2 font-medium text-muted-foreground border-r w-48">PAYEE:</td>
                    <td class="p-2 uppercase border-r w-xl">{{ voucher.payee }}</td>
                    <td class="p-2 font-medium text-muted-foreground border-r w-40">VOUCHER NO:</td>
                    <td class="p-2 w-40">{{ voucher.voucher_no }}</td>
                </tr>
                <tr class="border-b">
                    <td class="p-2 font-medium text-muted-foreground border-r">CHECK PAYABLE TO:</td>
                    <td class="p-2 uppercase border-r">{{ voucher.check_payable_to }}</td>
                    <td class="p-2 font-medium text-muted-foreground border-r">VOUCHER DATE:</td>
                    <td class="p-2">{{ formatDate(voucher.voucher_date) }}</td>
                </tr>
                <tr class="border-b">
                    <td class="p-2 font-medium text-muted-foreground border-r">PURPOSE:</td>
                    <td class="p-2 uppercase border-r">{{ voucher.purpose }}</td>
                    <td class="p-2 font-medium text-muted-foreground border-r">CHECK AMOUNT:</td>
                    <td class="p-2">₱{{ formatCurrency(voucher.check_amount) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Second Table -->
        <table class="w-full text-sm border border-border rounded-md">
            <tbody>
                <tr class="border-b">
                    <td class="p-2 font-medium text-muted-foreground border-r w-48">CHECK NUMBER:</td>
                    <td class="p-2 border-r">
                        <span v-if="voucher.status !== 'forCheck'"> *To be filled-up by accounting upon check releasing*</span>
                        <span v-else>{{ voucher.check_no }}</span>
                    </td>
                    <td class="p-2 font-medium text-muted-foreground border-r">CHECK DATE:</td>
                    <td class="p-2">{{ formatDate(voucher.check_date) }}</td>
                </tr>
                <tr>
                    <td class="p-2 font-medium text-muted-foreground border-r">STATUS:</td>
                    <td class="p-2 uppercase" colspan="3">
                        <span class="py-1 rounded-full font-bold capitalize min-w-[100px]">
                            {{ formatStatus(voucher.status) }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>