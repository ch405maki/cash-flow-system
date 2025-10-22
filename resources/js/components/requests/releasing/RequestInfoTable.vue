<script setup lang="ts">
import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from '@/components/ui/table'

defineProps({
  request: {
    type: Object,
    required: true
  }
})
</script>

<template>
  <Table>
    <TableBody>
      <TableRow>
        <TableCell class="border-r p-2 w-10">Request No:</TableCell>
        <TableCell class="border-r p-2">{{ request.request_no }}</TableCell>
        <TableCell class="border-r p-2 w-32">Status:</TableCell>
        <TableCell class="p-2 capitalize">
          <slot name="status-badge"></slot>
        </TableCell>
      </TableRow>
      <TableRow>
        <TableCell class="border-r p-2">Department:</TableCell>
        <TableCell class="border-r p-2">{{ request.department.department_name || 'N/A' }}</TableCell>
        <TableCell class="border-r p-2">Requested By:</TableCell>
        <TableCell class="p-2">{{ request.user.first_name }} {{ request.user.last_name }}</TableCell>
      </TableRow>
      <TableRow>
        <TableCell class="border-r p-2">Purpose:</TableCell>
        <TableCell colspan="3" class="p-2">{{ request.purpose || 'N/A'}}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>