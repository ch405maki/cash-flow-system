<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { AlertCircle, BellRing, X } from 'lucide-vue-next'

defineProps<{
  showAlert: boolean
  selectedFiles?: Array<{
    id: number
    file?: {
      original_filename?: string
      file_path?: string
      type?: string
    }
  }>
  remarks?: string
  canvasApprovals?: Array<{
    id: number
    comments?: string
    user?: { username?: string }
  }>
}>()

const emit = defineEmits<{
  dismiss: []
  previewFile: [file: any]
}>()
</script>

<template>
  <!-- Selected Files Alert -->
  <Alert
    v-if="showAlert"
    variant="warning"
    class="relative pr-10"
  >
    <AlertCircle class="h-4 w-4" />
    <AlertTitle>Selected File</AlertTitle>
    <AlertDescription>
      <template v-if="selectedFiles?.length">
        <div class="space-y-3">
          <div v-for="selectedFile in selectedFiles" :key="selectedFile.id">
            <div>
              <span class="font-medium">File: </span>
              <span
                v-if="selectedFile.file"
                class="text-blue-600 underline cursor-pointer capitalize"
                @click="$emit('previewFile', selectedFile.file)"
              >
                {{ selectedFile.file.original_filename || 'N/A' }}
              </span>
              <span v-else>File not found</span>
            </div>
          </div>
        </div>
      </template>
      <template v-else>
        <p>No selected files found for this canvas</p>
      </template>
    </AlertDescription>
    <button
      class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
      @click="$emit('dismiss')"
      aria-label="Dismiss"
    >
      <X class="h-4 w-4 text-yellow-700" />
    </button>
  </Alert>

  <!-- Remarks Alert -->
  <Alert
    v-if="showAlert && (remarks || canvasApprovals?.length)"
    variant="success"
    class="relative pr-10"
  >
    <BellRing class="h-4 w-4" />
    <AlertTitle>Remarks and Comments</AlertTitle>
    <AlertDescription>
      <div v-if="remarks">
        Purchasing: <span class="font-medium">{{ remarks }}.</span>
      </div>
      <div
        v-if="canvasApprovals?.length"
        v-for="comment in canvasApprovals"
        :key="comment.id"
        class="capitalize"
      >
        {{ comment.user?.username }}: <span class="font-medium">{{ comment.comments }}.</span>
      </div>
    </AlertDescription>
    <button
      class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
      @click="$emit('dismiss')"
      aria-label="Dismiss"
    >
      <X class="h-4 w-4 text-purple-700" />
    </button>
  </Alert>
</template>