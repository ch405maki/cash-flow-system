<script setup lang="ts">
import { formatDate } from '@/lib/utils'
import {
  Table,
  TableBody,
  TableCell,
  TableRow,
} from "@/components/ui/table"

defineProps<{
  pettyCash: any
}>()
</script>

<template>
  <Table>
    <TableBody>
      <TableRow>
        <TableCell class="font-medium w-[20%] border-r">
          <div>
            <div class="text-xs text-muted-foreground mb-1">PCV No</div>
            <div>{{ pettyCash.pcv_no }}</div>
          </div>
        </TableCell>
        <TableCell class="w-[20%] border-r">
          <div>
            <div class="text-xs text-muted-foreground mb-1">Status</div>
            <div class="capitalize">{{ pettyCash.status }}</div>
          </div>
        </TableCell>
        <TableCell class="w-[40%] border-r">
          <div>
            <div class="text-xs text-muted-foreground mb-1">Paid To</div>
            <div>{{ pettyCash.paid_to }}</div>
          </div>
        </TableCell>
        <TableCell class="w-[20%]">
          <div>
            <div class="text-xs text-muted-foreground mb-1">Date</div>
            <div>{{ formatDate(pettyCash.date) }}</div>
          </div>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>