<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

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
        <Table>
            <TableBody>
                <TableRow class="border-b">
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-48">
                    ISSUE DATE:
                </TableCell>
                <TableCell class="p-2 border-r">
                    {{ voucher.issue_date ? formatDate(voucher.issue_date) : '-' }}
                </TableCell>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-48">
                    DELIVERY DATE:
                </TableCell>
                <TableCell class="p-2" colspan="3">
                    {{ voucher.delivery_date ? formatDate(voucher.delivery_date) : '-' }}
                </TableCell>
                </TableRow>
                <TableRow v-if="voucher.remarks">
                <TableCell class="p-2" colspan="4">
                    REMARKS: {{ voucher.remarks }}
                </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>