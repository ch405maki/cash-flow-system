<script setup lang="ts">
import { Table, TableBody, TableRow, TableCell } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

const props = defineProps<{ request: any }>()

// Status badge configuration
const getStatusConfig = (status: string) => {
  const statusMap: Record<string, { color: string; label: string }> = {
    'pending': {
      color: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-100',
      label: 'Pending'
    },
    'approved': {
      color: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-100',
      label: 'Approved'
    },
    'released': {
      color: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-100',
      label: 'Released'
    },
    'rejected': {
      color: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-100',
      label: 'Rejected'
    },
    'to_property': {
      color: 'bg-yellow-100 text-yellow-800 border-yellow-200 hover:bg-yellow-100',
      label: 'To Property'
    },
    'draft': {
      color: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-100',
      label: 'Draft'
    },
    'in_review': {
      color: 'bg-purple-100 text-purple-800 border-purple-200 hover:bg-purple-100',
      label: 'In Review'
    },
    'completed': {
      color: 'bg-emerald-100 text-emerald-800 border-emerald-200 hover:bg-emerald-100',
      label: 'Completed'
    },
    'cancelled': {
      color: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-100',
      label: 'Cancelled'
    }
  }

  return statusMap[status.toLowerCase()] || {
    color: 'bg-gray-100 text-gray-800 border-gray-200 hover:bg-gray-100',
    label: status.charAt(0).toUpperCase() + status.slice(1)
  }
}
</script>

<template>
  <Table>
    <TableBody>
      <TableRow>
        <TableCell class="border-r p-2 w-10">Request No:</TableCell>
        <TableCell class="border-r p-2">{{ request.request_no }}</TableCell>
        <TableCell class="border-r p-2 w-32">Status:</TableCell>
        <TableCell class="border-r p-2">
          <Badge 
            variant="outline" 
            :class="getStatusConfig(request.status).color"
            class="font-medium capitalize"
          >
            {{ getStatusConfig(request.status).label }}
          </Badge>
        </TableCell>
      </TableRow>
      <TableRow>
        <TableCell class="border-r p-2">Department:</TableCell>
        <TableCell class="border p-2">{{ request.department.department_name || 'N/A' }}</TableCell>
        <TableCell class="border-r p-2">Requested By:</TableCell>
        <TableCell class="border p-2">{{ request.user.first_name }} {{ request.user.last_name }}</TableCell>
      </TableRow>
      <TableRow>
        <TableCell class="border-r p-2">Purpose:</TableCell>
        <TableCell colspan="3" class="p-2">{{ request.purpose || 'N/A' }}</TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>