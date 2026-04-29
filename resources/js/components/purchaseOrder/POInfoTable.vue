<script setup lang="ts">
import {
  Table, TableBody, TableCell, TableRow,
} from '@/components/ui/table'

defineProps<{
  payee: string
  poNo: string
  date: string
  checkPayableTo?: string
  tinNo?: string
  formatDate: (d: string) => string
}>()
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-1">
    <Table>
      <TableBody>
        <TableRow class="border-b">
          <TableCell class="p-2 font-medium text-muted-foreground border-r w-48">COMPANY NAME:</TableCell>
          <TableCell class="p-2 uppercase border-r w-xl">{{ payee }}</TableCell>
          <TableCell class="p-2 font-medium text-muted-foreground border-r w-40">P.O. NUMBER:</TableCell>
          <TableCell class="p-2 w-40 border-r">{{ poNo }}</TableCell>
          <TableCell class="p-2 w-40 font-medium text-muted-foreground border-r">P.O. DATE:</TableCell>
          <TableCell class="p-2 w-40">{{ formatDate(date) }}</TableCell>
        </TableRow>
        <TableRow class="border-b">
          <TableCell class="p-2 font-medium text-muted-foreground border-r">CHECK PAYABLE TO:</TableCell>
          <TableCell class="p-2 uppercase border-r">{{ checkPayableTo }}</TableCell>
          <TableCell class="p-2 font-medium text-muted-foreground border-r">TIN:</TableCell>
          <TableCell class="p-2">{{ tinNo }}</TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>