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

// Helper function to display appropriate filename
function getDisplayFileName(canvas) {
  // For approved canvases, show the selected file name
  if (canvas.status === 'approved' && canvas.selected_files?.length > 0) {
    return canvas.selected_files[0].file?.original_filename || 'Selected file';
  }
  
  // For canvases with multiple files, show count
  if (canvas.files?.length > 0) {
    return `${canvas.files.length} file${canvas.files.length > 1 ? 's' : ''}`;
  }
  
  // Fallback to title if no files
  return canvas.title || 'No files';
}
</script>

<template>
  <div v-if="canvases.length > 0" class="w-full text-sm border border-border rounded-md">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>File</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Remarks</TableHead>
          <TableHead>Uploaded</TableHead>
          <TableHead class="text-right">Actions</TableHead>
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
              <div class="font-medium capitalize">
                {{ getDisplayFileName(canvas) }}
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
            {{ canvas.remarks || canvas.selected_files[0]?.remarks || 'No remarks' }}
          </TableCell>

          <TableCell>{{ formatDate(canvas.created_at) }}</TableCell>

          <TableCell class="text-right">
            <Button
              variant="outline"
              size="sm"
              class="gap-1"
              @click.stop="emit('download', canvas)"
              :disabled="canvas.isDownloading"
            >
              <Download class="h-3.5 w-3.5" />
              <span>{{ canvas.isDownloading ? 'Downloading...' : 'Download' }}</span>
            </Button>
          </TableCell>
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