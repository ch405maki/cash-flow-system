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
                    <td class="p-2 border-r">{{ voucher.issue_date ? formatDate(voucher.issue_date) : '-' }}</td>
                    <td class="p-2 font-medium text-muted-foreground border-r w-48">DELIVERY DATE:</td>
                    <td class="p-2" colspan="3">{{ voucher.delivery_date ? formatDate(voucher.delivery_date) : '-' }}</td>
                </tr>
                <tr v-if="voucher.remarks">
                    <td class="p-2" colspan="4">REMARKS: {{ voucher.remarks }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>