<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Badge } from '@/components/ui/badge'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { formatDate } from '@/lib/utils'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

interface PettyCashItem {
  id: number
  pcv_no: string
  paid_to: string
  user?: {
    first_name: string
    last_name: string
  }
  date: string
  status: string
  remarks: string
}

interface Props {
  pettyCash: PettyCashItem[]
  user: any
}

const props = defineProps<Props>()

// Status color mapping (same as before)
const getStatusConfig = (status: string) => {
  const statusMap: Record<string, { color: string; label: string }> = {
    'draft': { color: 'bg-gray-500 text-white', label: 'Draft' },
    'requested': { color: 'bg-blue-500 text-white', label: 'Requested' },
    'submitted': { color: 'bg-purple-500 text-white', label: 'Submitted' },
    'approved': { color: 'bg-green-500 text-white', label: 'Approved' },
    'returned': { color: 'bg-orange-500 text-white', label: 'Returned' },
    'rejected': { color: 'bg-red-500 text-white', label: 'Rejected' },
    'liquidation': { color: 'bg-indigo-500 text-white', label: 'Liquidation' },
    'approved liquidation': { color: 'bg-teal-500 text-white', label: 'Approved Liquidation' },
    'released': { color: 'bg-emerald-500 text-white', label: 'Released' },
    'completed': { color: 'bg-green-600 text-white', label: 'Completed' },
    'cancelled': { color: 'bg-red-600 text-white', label: 'Cancelled' }
  }

  return statusMap[status.toLowerCase()] || {
    color: 'bg-gray-400 text-white',
    label: status
  }
}

// Handle row click
const handleRowClick = (item: PettyCashItem) => {
  if (item.status === 'draft' || item.status === 'returned') {
    router.get(route('petty-cash.edit', item.id))
  } else {
    router.get(route('petty-cash.view', item.id))
  }
}

// Get tooltip text
const getTooltipText = (item: PettyCashItem) => {
  if (item.status === 'draft' || item.status === 'returned') {
    return 'Click to review and edit'
  } else if (item.status === 'requested') {
    return 'Click to view pending request'
  } else {
    return 'Click to view details'
  }
}
</script>

<template>
    <Table>
      <TableCaption>A list of your petty cash.</TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[150px]">PCV NO</TableHead>
          <TableHead class="w-[100px]">Paid to</TableHead>
          <TableHead class="w-[150px]">Requested By</TableHead>
          <TableHead class="w-[100px]">Date</TableHead>
          <TableHead class="w-[100px]">Status</TableHead>
          <TableHead class="w-[200px]">Remarks</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TooltipProvider>
          <TableRow 
            v-for="item in pettyCash" 
            :key="item.id"
            class="cursor-pointer transition-colors hover:bg-muted/50 group"
            @click="handleRowClick(item)"
            title="click to view details"
          >
            <Tooltip>
              <TooltipTrigger as-child>
                <TableCell class="font-medium group-hover:underline">
                  {{ item.pcv_no }}
                </TableCell>
              </TooltipTrigger>
              <TooltipContent>
                <p>{{ getTooltipText(item) }}</p>
              </TooltipContent>
            </Tooltip>

            <TableCell class="capitalize">
              {{ item.paid_to }}
            </TableCell>
            
            <TableCell class="capitalize">
              {{ item.user?.first_name }} {{ item.user?.last_name }}
            </TableCell>
            
            <TableCell class="capitalize">
              {{ formatDate(item.date) }}
            </TableCell>
            
            <TableCell>
              <Badge 
                :class="getStatusConfig(item.status).color"
                class="capitalize font-medium text-white"
              >
                {{ getStatusConfig(item.status).label }}
              </Badge>
            </TableCell>
            
            <TableCell class="capitalize">
              "{{ item.remarks }}"
            </TableCell>
          </TableRow>
        </TooltipProvider>
      </TableBody>
    </Table>
</template>