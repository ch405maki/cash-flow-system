<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Download, ArrowLeft, Clock, CheckCircle, XCircle, Check, X, Pencil } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
import { watch } from 'vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
  canvas: Object,
  open: Boolean
});

const emit = defineEmits(['update:open', 'updated']);

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

const form = useForm({
  remarks: props.canvas.remarks || '',
  status: props.canvas.status
});

watch(
  () => props.canvas,
  (newCanvas) => {
    form.remarks = newCanvas?.remarks || '';
    form.status = newCanvas?.status;
  },
  { immediate: true }
);

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
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', props.canvas.original_filename);
    document.body.appendChild(link);
    link.click();
    
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

const updateStatus = (status) => {
  if (status === 'rejected' && !form.remarks.trim()) {
    toast.error('Remarks are required when rejecting.');
    return;
  }

  form.status = status;
  form.patch(route('canvas.update', props.canvas.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('updated');
      toast.success(`Canvas ${status} successfully.`);
    },
  });
};

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}
</script>

<template>
  <Dialog :open="open" @update:open="val => emit('update:open', val)">
    <DialogContent class="sm:max-w-[625px] max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <span class="truncate max-w-[400px] capitalize">{{ canvas.original_filename }}</span>
        </DialogTitle>
      </DialogHeader>

      <div class="grid gap-6 py-4">
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
              {{ formatDate(canvas.created_at)}}
            </p>
          </div>
        </div>

        <Alert variant="success" class="relative pr-10">
          <Bell class="h-4 w-4 text-green-500" />
          <AlertTitle>Note</AlertTitle>
          <AlertDescription>
            {{ canvas.note || 'No Note' }}
          </AlertDescription>
        </Alert>

        <div>
        <h3 class="text-sm font-medium text-muted-foreground">Remarks</h3>
        <p class="text-xs text-red-500 mb-2 italic">* Remarks are required when rejecting the canvas.</p>
        <div>
        <Textarea
            v-model="form.remarks"
            placeholder="Enter your remarks"
            class="mb-2"
        />
        </div>
        </div>
      </div>

      <div class="sticky bottom-0 bg-background pt-4 border-t">
        <div class="flex justify-between">
          <div v-if="canvas.status === 'pending'" class="flex gap-2">
            <Button 
              variant="default" 
              @click="updateStatus('approved')"
              :disabled="form.processing"
            >
              <Check class="h-4 w-4 mr-1" />
              Approve
            </Button>
            <Button 
                variant="destructive" 
                @click="updateStatus('rejected')"
                :disabled="form.processing || !form.remarks.trim()"
                >
                <X class="h-4 w-4 mr-1" />
                Reject
            </Button>

          </div>
          <Button 
            @click="downloadFile"
            class="gap-2"
            :disabled="isDownloading"
          >
            <Download class="h-4 w-4" />
            <span>{{ isDownloading ? 'Downloading...' : 'Download File' }}</span>
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>