<script setup>
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { FileText, Download } from 'lucide-vue-next';

defineProps({
  canvases: Array,
  statusIcons: Object,
  statusVariants: Object
})
const emit = defineEmits(['show', 'download'])

function formatDate(dateStr) {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

</script>

<template>
  <div v-if="canvases.length > 0">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>File</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Remarks</TableHead>
          <TableHead>Uploaded</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="canvas in canvases"
          :key="canvas.id"
          class="hover:cursor-pointer"
          @click="emit('show', canvas)"
        >
          <TableCell>
            <div class="flex items-center gap-4">
              <FileText class="h-5 w-5 text-muted-foreground" />
                <div class="grid gap-1">
                  <div class="font-medium">
                    {{ canvas.title || 'Untitled Canvas' }}
                  </div>
                  <div class="text-xs text-muted-foreground">
                    {{ canvas.files?.length || 0 }} files
                  </div>
                </div>
            </div>
          </TableCell>
          <TableCell>
            <Badge :class="statusVariants[canvas.status]">
              <component :is="statusIcons[canvas.status]" class="h-3 w-3 mr-1" />
              <span class="capitalize">{{ canvas.status.replace('_', ' ') }}</span>
            </Badge>
          </TableCell>

          <TableCell class="text-muted-foreground truncate max-w-[200px]">
            {{ canvas.note || canvas.selected_files[0]?.remarks || 'No remarks' }}
          </TableCell>

          <TableCell>{{ formatDate(canvas.created_at) }}</TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>

  <template v-else>
    <div class="flex h-48 flex-col items-center justify-center rounded-xl border">
      <FileText class="h-8 w-8 text-muted-foreground" />
      <p class="mt-2 text-sm text-muted-foreground">No canvas data found</p>
      <p class="text-xs text-muted-foreground">Approved/Created canvas will appear here for P. O. creation</p>
    </div>
  </template>
</template>