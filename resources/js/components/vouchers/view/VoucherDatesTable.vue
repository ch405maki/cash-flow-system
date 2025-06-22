<script setup lang="ts">
defineProps({
    voucher: {
        type: Object,
        required: true
    },
    formatDate: {
        type: Function,
        required: true
    }
});
</script>

<template>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-1 ">
        <table class="w-full text-sm border border-border rounded-md">
            <tbody>
                <tr class="border-b">
                    <td class="p-2 font-medium text-muted-foreground border-r w-48">ISSUE DATE:</td>
                    <td class="p-2 border-r">{{ formatDate(voucher.issue_date) }}</td>
                    <td class="p-2 font-medium text-muted-foreground border-r">PAYMENT DATE:</td>
                    <td class="p-2">{{ formatDate(voucher.payment_date) }}</td>
                </tr>
                <tr>
                    <td class="p-2 font-medium text-muted-foreground border-r">DELIVERY DATE:</td>
                    <td class="p-2" colspan="3">{{ formatDate(voucher.delivery_date) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>