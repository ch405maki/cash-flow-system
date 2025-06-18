<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { FileText, UserRoundCheck, Clock, CheckCircle, XCircle, Download, Eye } from 'lucide-vue-next';
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

defineProps<{
  canvases: Array<any>;
  authUserRole: string;
}>();

const statusIcons = {
  pending: Clock,
  approved: CheckCircle,
  rejected: XCircle,
  forEOD: UserRoundCheck,
};

const statusVariants = {
  pending: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
  approved: 'bg-green-100 text-green-800 hover:bg-green-200',
  rejected: 'bg-red-100 text-red-800 hover:bg-red-200',
  forEOD: 'bg-purple-100 text-purple-800 hover:bg-purple-200',
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
        <div v-if="authUserRole === 'purchasing'">
          <CanvasUploadDialog />
        </div>
      </div>
    <div v-if="canvases.length > 0" class="w-full text-sm border border-border rounded-md">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>File</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Remarks</TableHead>
            <TableHead>Uploaded</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow @click="showCanvas(canvas)" v-for="canvas in canvases" :key="canvas.id" class="hover:cursor-pointer" title="Click to View">
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
                {{ canvas.remarks || 'No Notes' }}
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

    <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
      <FileText class="h-8 w-8 text-muted-foreground" />
      <p class="mt-2 text-sm text-muted-foreground">No recent canvas found</p>
      <p class="text-xs text-muted-foreground">Canvas from purchasing will appear here</p>
    </div>
    
      <!-- Canvas Show Dialog -->
      <CanvasShowDialog
        v-if="selectedCanvas"
        :canvas="selectedCanvas"
        :open="showDialog"
        :user-role="authUserRole"
        @update:open="val => showDialog = val"
        @updated="refreshCanvases"
      />
    </div>
  </AppLayout>
</template>