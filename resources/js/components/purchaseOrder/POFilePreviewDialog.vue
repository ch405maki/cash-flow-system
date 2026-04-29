<script setup lang="ts">
import {
  Dialog, DialogContent,
} from '@/components/ui/dialog'

defineProps<{
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
</script>

<template>
  <Dialog :open="open" @update:open="$emit('update:open', $event)">
    <DialogContent class="max-w-4xl h-[80vh]">
      <div class="h-full py-4">
        <iframe
          v-if="file?.type === 'application/pdf'"
          :src="file.path"
          class="w-full h-full border rounded"
        ></iframe>
        <p v-else class="text-center text-muted-foreground">
          Preview not available for this file type
        </p>
      </div>
    </DialogContent>
  </Dialog>
</template>