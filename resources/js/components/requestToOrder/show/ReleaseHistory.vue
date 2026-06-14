<script setup lang="ts">
import { formatDateTime } from '@/lib/utils'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

defineProps<{
  details: any[]
}>()
</script>

<template>
  <div class="mt-8">
    <h2 class="text-xl font-semibold mb-4">Release History</h2>
    <div class="border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[120px]">Date</TableHead>
            <TableHead>Item</TableHead>
            <TableHead class="text-right">Quantity</TableHead>
            <TableHead>Released By</TableHead>
            <TableHead>Notes</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-for="detail in details" :key="`detail-${detail.id}`">
            <TableRow v-for="release in detail.releases" :key="`release-${release.id}`" class="hover:bg-muted/50">
              <TableCell class="font-medium w-48">{{ formatDateTime(release.created_at) }}</TableCell>
              <TableCell>{{ detail.item_description }}</TableCell>
              <TableCell class="text-right">{{ release.quantity_released }} {{ detail.unit }}</TableCell>
              <TableCell class="w-48">{{ release.released_by ? `${release.released_by.first_name} ${release.released_by.last_name}` : 'N/A' }}</TableCell>
              <TableCell class="capitalize">{{ release.notes || 'No notes' }}</TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>
    </div>
  </div>
</template>
