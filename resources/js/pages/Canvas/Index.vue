<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { FileText, Clock, CheckCircle, XCircle, Download, Plus, Eye } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
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
  router.get(route('canvas.show', canvas.id));
};
</script>

<template>
  <Head title="My Canvases" />
  
  <AppLayout>
    <div class="p-6">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between pb-2">
          <CardTitle class="text-2xl font-bold">My Canvases</CardTitle>
          <Button as-child>
            <a :href="route('canvas.create')" class="flex items-center gap-2">
              <Plus class="h-4 w-4" />
              <span>Upload New</span>
            </a>
          </Button>
        </CardHeader>
        
        <CardContent>
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
              <TableRow v-for="canvas in canvases" :key="canvas.id">
                <TableCell>
                  <div class="flex items-center gap-4">
                    <FileText class="h-5 w-5 text-muted-foreground" />
                    <div class="grid gap-1">
                      <div class="font-medium">
                        {{ canvas.original_filename }}
                      </div>
                      <div class="text-sm text-muted-foreground">
                        {{ canvas.file_path }}
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
                    {{ canvas.remarks || '-' }}
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="text-sm">
                    {{ new Date(canvas.created_at).toLocaleDateString() }}
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
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>