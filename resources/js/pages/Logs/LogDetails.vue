<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { formatDateTime } from '@/lib/utils'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import JsonViewer from '@/components/logs/JsonViewer.vue'

interface Log {
  id: number
  description: string
  log_name: string
  subject_type: string
  subject_id: number
  causer: {
    username: string
    first_name: string
    last_name: string
    email: string
  } | null
  subject: {
    id: number
    name?: string
    title?: string
    request_no?: string
  } | null
  properties: Record<string, unknown>
  created_at: string
}

const props = defineProps<{
  log: Log | null
}>()

const emit = defineEmits(['close'])

</script>

<template>
  <Dialog :open="!!log" @update:open="(val) => !val && emit('close')">
    <DialogContent class="max-w-2xl max-h-[90vh] overflow-auto">
      <DialogHeader>
        <DialogTitle>Activity Details</DialogTitle>
      </DialogHeader>

      <div v-if="log" class="space-y-6">
        <!-- Basic Info Section -->
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
          <div class="space-y-1">
            <h3 class="font-medium">Description</h3>
            <p class="text-sm text-muted-foreground">
                {{ log.description }}
            </p>
          </div>
          
          
          <div class="space-y-1">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-medium">Performed By</h3>
                    <p class="text-sm text-muted-foreground">
                        {{ log.causer?.username || 'System' }}
                        <span v-if="log.causer?.email">({{ log.causer.email }})</span>
                    </p>
                </div>
                <div>
                    <h3 class="font-medium">Date</h3>
                    <p class="text-sm text-muted-foreground">
                        {{ formatDateTime(log.created_at) }}
                    </p>
                </div>
            </div>
          </div>
        </div>

        <!-- Full Properties Section -->
        <div class="space-y-2"> 
          <h3 class="font-medium">All Properties</h3>
          <div class="border rounded-lg p-4 bg-muted/50">
            <JsonViewer :data="log.properties" />
          </div>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>