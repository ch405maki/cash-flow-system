<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { FileText, Clock, CheckCircle, XCircle, Download, Eye } from 'lucide-vue-next';
import axios from 'axios';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import CanvasUploadDialog from '@/components/canvas/CanvasUploadDialog.vue'
import CanvasShowDialog from '@/components/canvas/CanvasShowDialog.vue'
import { ref } from 'vue';

defineProps({
  canvases: Array,
});

const statusIcons = {
  pending: Clock,
  approved: CheckCircle,
  rejected: XCircle,
};

const statusVariants = {
  pending: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
  approved: 'bg-green-100 text-green-800 hover:bg-green-200',
  rejected: 'bg-red-100 text-red-800 hover:bg-red-200',
};

// Dialog state management
const showDialog = ref(false);
const selectedCanvas = ref(null);

const downloadFile = async (canvas) => {
  try {
    canvas.isDownloading = true;
    
    const response = await axios.get(route('canvas.download', canvas.id), {
      responseType: 'blob',
      onDownloadProgress: (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
        console.log(`Download progress: ${percentCompleted}%`);
      }
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', canvas.original_filename);
    document.body.appendChild(link);
    link.click();
    
    window.URL.revokeObjectURL(url);
    document.body.removeChild(link);
    
  } catch (error) {
    console.error('Download failed:', error);
    alert('Download failed. Please try again.');
  } finally {
    canvas.isDownloading = false;
  }
};

const showCanvas = (canvas) => {
  selectedCanvas.value = null; 
  selectedCanvas.value = canvas;
  showDialog.value = true;
};


const refreshCanvases = () => {
  router.reload({ only: ['canvases'] });
};

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Canvas', href: '/' },
] 
</script>

<template>
  <Head title="My Canvases" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <div class="flex flex-row items-center justify-between mb-4">
        <CardTitle class="text-2xl font-bold">Canvas</CardTitle>
        <CanvasUploadDialog />
      </div>
    <div class="w-full text-sm border border-border rounded-md">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>File</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Note</TableHead>
            <TableHead>Uploaded</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="canvas in canvases" :key="canvas.id">
            <TableCell>
              <div class="flex items-center gap-4">
                <FileText class="h-5 w-5 text-muted-foreground" />
                <div class="grid gap-1">
                  <div class="font-medium capitalize">
                    {{ canvas.original_filename }}
                  </div>
                </div>
              </div>
            </TableCell>
            
            <TableCell>
              <Badge :class="statusVariants[canvas.status]">
                <component 
                  :is="statusIcons[canvas.status]" 
                  class="h-3 w-3 mr-1" 
                />
                <span class="capitalize">{{ canvas.status }}</span>
              </Badge>
            </TableCell>
            
            <TableCell>
              <div class="text-sm text-muted-foreground max-w-[200px] truncate">
                {{ canvas.note || 'No Notes' }}
              </div>
            </TableCell>
            
            <TableCell>
              <div class="text-sm">
                {{ formatDate(canvas.created_at) }}
              </div>
            </TableCell>
            
            <TableCell class="text-right">
              <div class="flex gap-2 justify-end">
                <Button 
                  variant="outline" 
                  size="sm" 
                  class="gap-1"
                  @click="showCanvas(canvas)"
                >
                  <Eye class="h-3.5 w-3.5" />
                  <span>Show</span>
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  class="gap-1"
                  @click="downloadFile(canvas)"
                  :disabled="canvas.isDownloading"
                >
                  <Download class="h-3.5 w-3.5" />
                  <span>{{ canvas.isDownloading ? 'Downloading...' : 'Download' }}</span>
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
      <!-- Canvas Show Dialog -->
      <CanvasShowDialog 
        v-if="selectedCanvas"
        :canvas="selectedCanvas" 
        :open="showDialog"
        @update:open="val => showDialog = val"
        @updated="refreshCanvases"
      />
    </div>
  </AppLayout>
</template>