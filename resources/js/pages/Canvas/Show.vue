<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Download, ArrowLeft, Clock, CheckCircle, XCircle } from 'lucide-vue-next';
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  canvas: Object,
});

const statusIcons = {
  pending: Clock,
  approved: CheckCircle,
  rejected: XCircle,
};

const statusVariants = {
  pending: 'bg-yellow-100 text-yellow-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
};

const isDownloading = ref(false);

const downloadFile = async () => {
  try {
    isDownloading.value = true;
    
    const response = await axios.get(route('canvas.download', props.canvas.id), {
      responseType: 'blob',
      onDownloadProgress: (progressEvent) => {
        if (progressEvent.total) {
          const percentCompleted = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          );
          console.log(`Download progress: ${percentCompleted}%`);
        }
      }
    });
    
    // Create download link
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', props.canvas.original_filename);
    document.body.appendChild(link);
    link.click();
    
    // Cleanup
    setTimeout(() => {
      window.URL.revokeObjectURL(url);
      document.body.removeChild(link);
    }, 100);
    
  } catch (error) {
    console.error('Download failed:', error);
    alert(`Download failed: ${error.response?.data?.message || error.message}`);
  } finally {
    isDownloading.value = false;
  }
};
</script>

<template>
  <Head :title="`Canvas - ${canvas.original_filename}`" />
  
  <AppLayout>
    <div class="p-6">
      <Card>
        <CardHeader>
          <div class="flex items-center gap-4">
            <Button variant="outline" size="icon" @click="router.visit(route('canvas.index'))">
              <ArrowLeft class="h-4 w-4" />
            </Button>
            <CardTitle class="text-2xl font-bold">
              {{ canvas.original_filename }}
            </CardTitle>
          </div>
        </CardHeader>

        <CardContent class="grid gap-6">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <h3 class="text-sm font-medium text-muted-foreground">Status</h3>
              <Badge :class="statusVariants[canvas.status]" class="mt-1">
                <component 
                  :is="statusIcons[canvas.status]" 
                  class="h-3 w-3 mr-1" 
                />
                <span class="capitalize">{{ canvas.status }}</span>
              </Badge>
            </div>

            <div>
              <h3 class="text-sm font-medium text-muted-foreground">Uploaded</h3>
              <p class="mt-1 text-sm">
                {{ new Date(canvas.created_at).toLocaleString() }}
              </p>
            </div>
          </div>

        <div>
            <h3 class="text-sm font-medium text-muted-foreground">Remarks</h3>
            <p class="mt-1 text-sm">
                {{ canvas.remarks || 'No remarks provided' }}
            </p>
        </div>
          <div>
            <h3 class="text-sm font-medium text-muted-foreground">Note</h3>
            <p class="mt-1 text-sm">
              {{ canvas.note || 'No Notes provided' }}
            </p>
          </div>
        </CardContent>

        <CardFooter class="flex justify-end gap-2">
          <Button 
            @click="downloadFile" 
            class="gap-2"
            :disabled="isDownloading"
          >
            <Download class="h-4 w-4" />
            <span>{{ isDownloading ? 'Downloading...' : 'Download File' }}</span>
          </Button>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>