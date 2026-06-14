<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { AlertCircle, BellRing, X } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
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

// Computed property to check if there are any files
const hasFiles = computed(() => {
  return props.selectedFiles?.some(file => file.file) ?? false
})

// Computed property to check if there are any remarks or comments
const hasRemarksOrComments = computed(() => {
  return !!(props.remarks?.trim() || props.canvasApprovals?.some(approval => approval.comments?.trim()))
})

// Computed property to determine if file alert should be shown
const showFileAlert = computed(() => {
  return props.showAlert && hasFiles.value
})

// Computed property to determine if remarks alert should be shown
const showRemarksAlert = computed(() => {
  return props.showAlert && hasRemarksOrComments.value
})
</script>

<template>
  <!-- Selected Files Alert - Only show if there are files -->
  <Alert
    v-if="showFileAlert"
    variant="info"
    class="relative pr-10 border-blue-200 bg-blue-50"
  >
    <AlertCircle class="h-4 w-4 text-blue-600" />
    <AlertTitle class="text-blue-900">Selected File</AlertTitle>
    <AlertDescription class="text-blue-700">
      <div class="space-y-3">
        <div v-for="selectedFile in selectedFiles" :key="selectedFile.id">
          <div v-if="selectedFile.file">
            <span class="font-medium">File: </span>
            <span
              class="text-blue-700 underline cursor-pointer capitalize hover:text-blue-900"
              @click="$emit('previewFile', selectedFile.file)"
            >
              {{ selectedFile.file.original_filename || 'N/A' }}
            </span>
          </div>
        </div>
      </div>
    </AlertDescription>
    <button
      class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
      @click="$emit('dismiss')"
      aria-label="Dismiss"
    >
      <X class="h-4 w-4 text-blue-500 hover:text-blue-700" />
    </button>
  </Alert>

  <!-- Remarks Alert - Only show if there are remarks or comments -->
  <Alert
    v-if="showRemarksAlert"
    variant="info"
    class="relative pr-10 border-indigo-200 bg-indigo-50"
  >
    <BellRing class="h-4 w-4 text-indigo-600" />
    <AlertTitle class="text-indigo-900">Remarks and Comments</AlertTitle>
    <AlertDescription class="text-indigo-700">
      <div v-if="remarks?.trim()">
        Purchasing: <span class="font-medium">{{ remarks }}.</span>
      </div>
      <div
        v-if="canvasApprovals?.length"
        v-for="comment in canvasApprovals"
        :key="comment.id"
        class="capitalize"
      >
        <div v-if="comment.comments?.trim()">
          {{ comment.user?.username }}: <span class="font-medium">{{ comment.comments }}.</span>
        </div>
      </div>
    </AlertDescription>
    <button
      class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
      @click="$emit('dismiss')"
      aria-label="Dismiss"
    >
      <X class="h-4 w-4 text-indigo-500 hover:text-indigo-700" />
    </button>
  </Alert>
</template>