<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Download, ArrowLeft, Clock, CheckCircle, XCircle } from 'lucide-vue-next';
import { useForm, router } from '@inertiajs/vue3';

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

const downloadFile = async () => {
  try {
    const response = await axios.get(route('canvas.download', props.canvas.id), {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', props.canvas.original_filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Download failed:', error);
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
            <h3 class="text-sm font-medium text-muted-foreground">File Details</h3>
            <div class="mt-1 grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-muted-foreground">Stored as:</p>
                <p>{{ canvas.file_path }}</p>
              </div>
              <div>
                <p class="text-muted-foreground">Uploaded by:</p>
                <p>{{ canvas.creator?.name || 'Unknown' }}</p>
              </div>
            </div>
          </div>
        </CardContent>

        <CardFooter class="flex justify-end gap-2">
          <Button @click="downloadFile" class="gap-2">
            <Download class="h-4 w-4" />
            Download File
          </Button>
        </CardFooter>
      </Card>
    </div>
  </AppLayout>
</template>