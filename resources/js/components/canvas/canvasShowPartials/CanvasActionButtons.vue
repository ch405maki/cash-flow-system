<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronRight, Check, Download, Upload } from 'lucide-vue-next';

const props = defineProps<{
  userRole: string
  canvasStatus: string
  hasComments: boolean
  hasSelectedFile: boolean
  isDownloading: boolean
  hasApprovedFile: boolean
  formProcessing: boolean
}>();

const emit = defineEmits<{
  (e: 'action', action: string): void
  (e: 'reupload'): void
  (e: 'create-po'): void
  (e: 'download-approved'): void
}>();
</script>

<template>
  <div class="bottom-0 flex justify-end pb-6">
    <div class="flex justify-between items-center mt-4 gap-2">
      <!-- Purchasing Officer Actions -->
      <template v-if="userRole === 'purchasing'">
        <div v-if="canvasStatus === 'draft'" class="space-x-2">
          <Button 
            variant="default" 
            @click="$emit('action', 'submitted')"
            :disabled="formProcessing"
          >
            <ChevronRight class="h-4 w-4 mr-1" />
            Submit
          </Button>
        </div>
        <div v-if="canvasStatus === 'rejected'" class="space-x-2">
          <Button 
            variant="outline" 
            @click="$emit('reupload')"
          >
            <Upload class="h-4 w-4 mr-1" />
            Re-upload
          </Button>
        </div>
      </template>

      <!-- Audit Actions -->
      <div v-if="userRole === 'audit' && canvasStatus !== 'pending'" class="space-x-2">
        <Button 
          variant="success" 
          @click="$emit('action', 'approve')"
          :disabled="formProcessing || !hasComments"
        >
          <Check class="h-4 w-4 mr-1" />
          Submit
        </Button>
      </div>

      <!-- Executive Director Actions -->
      <div v-if="userRole === 'executive_director'" class="space-x-2">
        <div v-if="canvasStatus === 'pending_approval' || canvasStatus === 'submitted'">
          <Button 
            variant="success" 
            size="sm"
            @click="$emit('action', 'final_approve')"
            :disabled="formProcessing || !hasComments || !hasSelectedFile"
          >
            <Check class="h-4 w-4 mr-1" />
            Approve
          </Button>
        </div>
      </div>

      <!-- Download Approved File Button -->
      <Button 
        v-else-if="canvasStatus === 'approved' || hasApprovedFile"
        @click="$emit('download-approved')"
        class="gap-2"
        variant="outline"
        :disabled="isDownloading"
      >
        <Download class="h-4 w-4" />
        <span>{{ isDownloading ? 'Downloading...' : 'Download Approved File' }}</span>
      </Button>

      <!-- Create PO Button -->
      <div v-if="canvasStatus === 'approved' && userRole === 'purchasing'">
        <Button 
          variant="default" 
          @click="$emit('create-po')"
          :disabled="formProcessing"
        >
          <Check class="h-4 w-4 mr-1" />
          Create P.O.
        </Button>
      </div>
    </div>
  </div>
</template>