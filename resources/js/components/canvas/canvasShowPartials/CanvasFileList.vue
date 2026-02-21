<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { FileText, Download } from 'lucide-vue-next';

const props = defineProps<{
  files: any[]
  selectedFileId?: number | null
  previewFileId?: number | null
  userRole?: string
}>();

const emit = defineEmits<{
  (e: 'update:selectedFileId', value: number): void
  (e: 'download', fileId: number): void
  (e: 'preview', file: any): void
}>();

const isPdfFile = (file: any) => {
  return file?.mimetype?.includes('pdf') || 
    file?.original_filename?.toLowerCase().endsWith('.pdf') ||
    file?.type?.includes('pdf');
};
</script>

<template>
  <div class="space-y-2 mt-2">
    <div 
      v-for="file in files" 
      :key="file.id" 
      class="flex items-center justify-between p-2 border rounded cursor-pointer transition-colors duration-150"
      :class="{
        'bg-blue-50 border-blue-200 dark:bg-blue-900 dark:border-blue-700': previewFileId === file.id,
        'hover:bg-gray-100 dark:hover:bg-gray-800': previewFileId !== file.id
      }"
      @click="$emit('preview', file)"
    >
      <div class="flex items-center gap-3 flex-1">
        
        <!-- RADIO ONLY FOR EXECUTIVE DIRECTOR -->
        <div 
          v-if="userRole === 'executive_director'" 
          class="flex items-center h-5"
        >
          <input
            type="radio"
            :id="`file-${file.id}`"
            :value="file.id"
            :checked="selectedFileId === file.id"
            @change="$emit('update:selectedFileId', file.id)"
            @click.stop
            class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
          >
        </div>

        <FileText class="h-4 w-4 text-muted-foreground shrink-0" />

        <label
          v-if="userRole === 'executive_director'"
          :for="`file-${file.id}`"
          class="text-sm cursor-pointer truncate max-w-[200px]"
        >
          {{ file.original_filename }}
        </label>

        <span
          v-else
          class="text-sm truncate max-w-[200px]"
        >
          {{ file.original_filename }}
        </span>

        <Badge 
          v-if="isPdfFile(file)" 
          variant="secondary" 
          class="text-xs"
        >
          PDF
        </Badge>
      </div>
      
      <Button 
        variant="ghost" 
        size="sm"
        @click.stop="$emit('download', file.id)"
      >
        <Download class="h-4 w-4" />
      </Button>
    </div>
  </div>
</template>