<script setup>
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { FileText } from 'lucide-vue-next';
import { formatDate } from '@/lib/utils'

defineProps({
  canvases: Array,
  statusIcons: Object,
  statusVariants: Object
})
const emit = defineEmits(['show', 'download'])
</script>

<template>
  <div v-if="canvases.length > 0">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>File</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Uploaded</TableHead>
          <TableHead class="text-right">Remarks</TableHead>
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
                    <!-- If approved or PO created, show approved filename -->
                    <template v-if="['approved', 'poCreated'].includes(canvas.status)">
                      {{ canvas.selected_files?.[0]?.file?.original_filename || 'No approved file' }}
                    </template>

                    <!-- Otherwise show file count -->
                    <template v-else>
                      {{ canvas.files?.length || 0 }} files
                    </template>
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
          <TableCell>{{ formatDate(canvas.created_at) }}</TableCell>
          <TableCell class="text-right text-muted-foreground">
            {{ canvas.note || canvas.selected_files[0]?.remarks || 'No remarks' }}
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