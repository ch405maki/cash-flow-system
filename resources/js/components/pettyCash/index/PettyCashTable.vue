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
import StatusBadge from '@/components/StatusBadge.vue';

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
          <TableHead>PCV NO</TableHead>
          <TableHead>Paid to</TableHead>
          <TableHead>Requested By</TableHead>
          <TableHead>Date</TableHead>
          <TableHead>Status</TableHead>
          <TableHead class="text-right">Remarks</TableHead>
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
              <StatusBadge 
                :status="item.status"
                show-icon
                size="md"
              />
            </TableCell>
            
            <TableCell class="capitalize text-right">
              "{{ item.remarks }}"
            </TableCell>
          </TableRow>
        </TooltipProvider>
      </TableBody>
    </Table>
</template>