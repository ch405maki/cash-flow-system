<script setup lang="ts">
import { computed } from 'vue'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { FileX } from 'lucide-vue-next'

const props = defineProps<{
  open: boolean
  file?: {
    name: string
    path: string
    type: string
    path_name: string
  } | null
}>()

const emit = defineEmits<{
  'update:open': [value: boolean]
}>()

// Derive type from MIME or fallback to extension
const fileKind = computed(() => {
  if (!props.file) return 'unsupported'

  const mime = props.file.type?.toLowerCase() ?? ''
  const ext  = props.file.path_name?.split('.').pop()?.toLowerCase() ?? ''

  if (mime.startsWith('image/') || ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'bmp'].includes(ext)) {
    return 'image'
  }
  if (mime === 'application/pdf' || ext === 'pdf') {
    return 'pdf'
  }

  return 'unsupported'
})

// Build the correct public storage URL
const fileUrl = computed(() => {
  if (!props.file) return ''
  // If path already starts with /storage, use as-is; otherwise build it
  if (props.file.path.startsWith('/storage')) return props.file.path
  return `/storage/canvases/${props.file.path_name}`
})
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-4xl h-[85vh] flex flex-col gap-0 p-0 overflow-hidden">

      <!-- Title bar -->
      <div class="flex items-center justify-between px-4 py-3 border-b shrink-0">
        <DialogTitle class="text-sm font-medium truncate max-w-[90%]">
          {{ file?.name ?? 'File Preview' }}
        </DialogTitle>
        <a
          v-if="file"
          :href="fileUrl"
          target="_blank"
          rel="noopener noreferrer"
          class="text-xs text-muted-foreground hover:text-foreground underline shrink-0 mr-10"
        >
          Open in new tab
        </a>
      </div>

      <!-- Preview body -->
      <div class="flex-1 overflow-hidden bg-muted/30 flex items-center justify-center">

        <!-- Image -->
        <img
          v-if="fileKind === 'image'"
          :src="fileUrl"
          :alt="file?.name"
          class="max-h-full max-w-full object-contain p-4"
        />

        <!-- PDF -->
        <iframe
          v-else-if="fileKind === 'pdf'"
          :src="fileUrl"
          class="w-full h-full border-0"
        />

        <!-- Unsupported -->
        <div v-else class="text-center text-muted-foreground space-y-3 p-8">
          <FileX class="h-12 w-12 mx-auto opacity-30" />
          <p class="text-sm">Preview not available for this file type.</p>
          <a
            v-if="file"
            :href="fileUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="text-sm text-primary underline"
          >
            Download file
          </a>
        </div>

      </div>
    </DialogContent>
  </Dialog>
</template>