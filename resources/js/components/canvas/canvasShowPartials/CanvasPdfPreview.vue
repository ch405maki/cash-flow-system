<script setup lang="ts">
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { LoaderCircle, X, Download, FileText } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  file: any
  canvasId: number
}>();

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'download', fileId: number): void
}>();

const previewLoading = ref(true);
const previewError = ref(false);

watch(() => props.file, () => {
  previewLoading.value = true;
  previewError.value = false;
}, { immediate: true });

const getPreviewUrl = (file: any) => {
  if (file.url) {
    return file.url;
  }
  
  return route('canvas.preview.file', { 
    canvas: props.canvasId, 
    file: file.id 
  }) + '#view=fitH&toolbar=0&navpanes=0';
};

const isPdfFile = (file: any) => {
  return file?.mimetype?.includes('pdf') || 
    file?.original_filename?.toLowerCase().endsWith('.pdf') ||
    file?.type?.includes('pdf');
};
</script>

<template>
  <div class="w-1/2 pl-4 overflow-y-auto">
    <div class="sticky top-0 bg-background z-10 pb-2 flex justify-between items-center">
      <h3 class="text-sm font-medium">Preview: {{ file?.original_filename }}</h3>
      <Button variant="ghost" size="sm" @click="$emit('close')">
        <X class="h-4 w-4" />
      </Button>
    </div>
    
    <div v-if="isPdfFile(file)" class="h-full border rounded-md overflow-hidden bg-gray-50">
      <div v-if="previewLoading" class="h-full flex items-center justify-center">
        <LoaderCircle class="h-8 w-8 animate-spin text-gray-400" />
      </div>
      
      <iframe 
        v-show="!previewLoading && !previewError"
        :src="getPreviewUrl(file)"
        class="w-full h-full min-h-[500px]"
        frameborder="0"
        @load="previewLoading = false"
        @error="previewError = true"
      />
      
      <div v-if="previewError" class="h-full flex flex-col items-center justify-center p-4 text-center">
        <X class="h-8 w-8 text-red-400 mb-2" />
        <p class="text-sm text-gray-600">Failed to load preview</p>
        <Button 
          variant="outline" 
          size="sm" 
          class="mt-4"
          @click="$emit('download', file.id)"
        >
          <Download class="h-4 w-4 mr-2" />
          Download Instead
        </Button>
      </div>
    </div>
    
    <div v-else class="h-full flex flex-col items-center justify-center p-4 text-center">
      <FileText class="h-12 w-12 mx-auto text-gray-400 mb-2" />
      <p class="text-sm text-gray-500">Preview not available for this file type</p>
      <Button 
        variant="default" 
        size="sm" 
        class="mt-4"
        @click="$emit('download', file.id)"
      >
        <Download class="h-4 w-4 mr-2" />
        Download File
      </Button>
    </div>
  </div>
</template>