<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Download, ArrowLeft, Clock, CheckCircle, UserRoundCheck, XCircle, Check, X, Pencil, ChevronRight } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { formatDate } from '@/lib/utils';
import { router } from '@inertiajs/vue3';

const toast = useToast();

const props = defineProps({
  canvas: {
    type: Object,
    required: true,
    default: () => ({
      request_to_order: null,
      files: [],
      approvals: [],
      selected_files: []
    })
  },
  open: Boolean,
  userRole: String,
});

const emit = defineEmits(['update:open', 'updated', 'reupload']);

const isApproved = computed(
  () => props.canvas.status === 'approved' || props.canvas.status === 'poCreated'
);

const hasLinkedOrder = computed(() => props.canvas.request_to_order !== null);

const statusIcons = {
  draft: Pencil,
  submitted: ChevronRight,
  pending_approval: UserRoundCheck,
  approved: CheckCircle,
  rejected: XCircle,
  forEOD: CheckCircle,
};

const statusVariants = {
  draft: 'bg-gray-100 text-gray-800',
  submitted: 'bg-blue-100 text-blue-800',
  pending_approval: 'bg-purple-100 text-purple-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
  forEOD: 'bg-green-100 text-green-800',
};

const isDownloading = ref(false);

const form = useForm({
  remarks: props.canvas.remarks || '',
  status: props.canvas.status,
  comments: props.canvas.latest_approval?.comments || '',
  selected_file: props.canvas.selected_files?.[0]?.canvas_file_id || null,
  file_remarks: props.canvas.selected_files?.[0]?.remarks || ''
});

watch(
  () => props.canvas,
  (newCanvas) => {
    form.remarks = newCanvas?.remarks || '';
    form.status = newCanvas?.status;
    form.comments = newCanvas.latest_approval?.comments || '';
    form.selected_file = newCanvas.selected_files?.[0]?.canvas_file_id || null;
    form.file_remarks = newCanvas.selected_files?.[0]?.remarks || '';
  },
  { immediate: true, deep: true }
);

const downloadFile = async (fileId = null) => {
  try {
    isDownloading.value = true;
    
    const url = fileId 
      ? route('canvas.download', { canvas: props.canvas.id, fileId })
      : route('canvas.download', props.canvas.id);

    const response = await axios.get(url, {
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
    
    const urlObject = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = urlObject;
    link.setAttribute('download', fileId 
      ? props.canvas.files.find(f => f.id === fileId)?.original_filename 
      : props.canvas.original_filename);
    document.body.appendChild(link);
    link.click();
    
    setTimeout(() => {
      window.URL.revokeObjectURL(urlObject);
      document.body.removeChild(link);
    }, 100);
    
  } catch (error) {
    console.error('Download failed:', error);
    toast.error(`Download failed: ${error.response?.data?.message || error.message}`);
  } finally {
    isDownloading.value = false;
  }
};

const handleAction = async (action) => {
  try {
    const payload = {
      status: "approved", // or "pending_approval"
      comments: "This file Approved",
      remarks: "", // general remarks
      selected_file: 1, // ID of the selected file
      file_remarks: "I want this supplier" // remarks for the selected file
    };

    if (action === 'final_approve') {
      if (!form.selected_file) {
        toast.error('Please select a file for approval');
        return;
      }
      
      payload.selected_files = {
        [form.selected_file]: { remarks: form.file_remarks }
      };
    }

    await form.patch(route('canvas.update', props.canvas.id), {
      ...payload,
      preserveScroll: true,
      onSuccess: () => {
        emit('updated');
        emit('update:open', false);
        toast.success(`Canvas ${action}d successfully`);
      },
      onError: (errors) => {
        toast.error(`Action failed: ${errors.message || 'Unknown error'}`);
      }
    });
    
  } catch (error) {
    console.error('Action failed:', error);
    toast.error(`Action failed: ${error.message}`);
  }
};

function goToCreate() {
  const canvasId = props.canvas?.id;
  router.visit(`/purchase-order/create?canvas_id=${canvasId}`);
}

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`);
}
</script>

<template>
  <Dialog :open="open" @update:open="val => emit('update:open', val)">
    <DialogContent class="sm:max-w-[625px] max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <span class="truncate max-w-[400px] capitalize">{{ canvas.title || 'Untitled Canvas' }}</span>
        </DialogTitle>
        <DialogDescription v-if="hasLinkedOrder" class="hover:underline hover:cursor-pointer hover:text-violet-800" @click="viewRequest(canvas.request_to_order.id)">
          Linked to Order # {{ canvas.request_to_order.order_no }} | {{ formatDate(canvas.request_to_order.order_date) }}
        </DialogDescription>
        <DialogDescription v-else>
          Not linked to any order. 
        </DialogDescription>
      </DialogHeader>

      <div class="grid gap-6 py-4">
        <!-- Order Information Section -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <h3 class="text-sm font-medium text-muted-foreground">Status</h3>
            <Badge :class="statusVariants[canvas.status]" class="mt-1">
              <component 
                :is="statusIcons[canvas.status]" 
                class="h-3 w-3 mr-1" 
              />
              <span class="capitalize">{{ canvas.status.replace('_', ' ') }}</span>
            </Badge>
          </div>

          <div>
            <h3 class="text-sm font-medium text-muted-foreground">Uploaded</h3>
            <p class="mt-1 text-sm">
              {{ formatDate(canvas.created_at)}}
            </p>
          </div>
        </div>

        <Alert variant="default" class="relative pr-10">
          <component :is="statusIcons[canvas.status]" class="h-4 w-4" />
          <AlertTitle>Note</AlertTitle>
          <AlertDescription>
            {{ canvas.note || 'No Note' }}
          </AlertDescription>
        </Alert>

        <!-- Approval History -->
        <div v-if="canvas.approvals?.length">
          <h3 class="text-sm font-medium text-muted-foreground">Approval History</h3>
          <div class="space-y-2 mt-2">
            <div v-for="approval in canvas.approvals" :key="approval.id" class="p-3 border rounded">
              <div class="flex justify-between items-start">
                <div>
                  <p class="font-medium">{{ approval.user.name }} ({{ approval.role }})</p>
                  <p class="text-xs text-muted-foreground">{{ formatDate(approval.created_at) }}</p>
                </div>
                <Badge :variant="approval.approved ? 'success' : 'destructive'">
                  {{ approval.approved ? 'Approved' : 'Rejected' }}
                </Badge>
              </div>
              <p v-if="approval.comments" class="mt-2 text-sm">
                "{{ approval.comments }}"
              </p>
            </div>
          </div>
        </div>

        <!-- Files Section -->
        <div v-if="canvas.files?.length">
          <h3 class="text-sm font-medium text-muted-foreground">Files</h3>
          <div class="space-y-2 mt-2">
            <div v-for="file in canvas.files" :key="file.id" class="flex items-center justify-between p-2 border rounded">
              <div class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm">{{ file.original_filename }}</span>
              </div>
              <Button 
                variant="ghost" 
                size="sm"
                @click="downloadFile(file.id)"
              >
                <Download class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>

        <!-- Comments Section -->
        <div v-if="['accounting', 'executive_director'].includes(userRole)">
          <h3 class="text-sm font-medium text-muted-foreground">
            {{ userRole === 'accounting' ? 'Audit Comments' : 'Approval Comments' }}
          </h3>
          <Textarea
            v-model="form.comments"
            :placeholder="userRole === 'accounting' 
              ? 'Enter your audit comments...' 
              : 'Enter approval comments...'"
            class="mt-2"
          />
        </div>

        <!-- File Selection for Executive -->
        <div v-if="userRole === 'executive_director' && canvas.files?.length">
          <h3 class="text-sm font-medium text-muted-foreground mt-4">Select File for Approval</h3>
          <p class="text-xs text-muted-foreground mb-2">Please select one file to approve</p>
          
          <div class="space-y-2 mt-2">
            <div v-for="file in canvas.files" :key="file.id" class="flex items-start gap-3 p-2 border rounded">
              <div class="flex items-center h-5">
                <input
                  type="radio"
                  :id="`file-${file.id}`"
                  v-model="form.selected_file"
                  :value="file.id"
                  class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                >
              </div>
              <div class="flex-1">
                <label :for="`file-${file.id}`" class="flex items-center gap-2 cursor-pointer">
                  <FileText class="h-4 w-4 text-muted-foreground" />
                  <span class="text-sm">{{ file.original_filename }}</span>
                </label>
                
                <div v-if="form.selected_file === file.id" class="mt-2">
                  <Textarea
                    v-model="form.file_remarks"
                    placeholder="File remarks (optional)"
                    class="w-full text-xs h-8"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- General Remarks -->
        <div>
          <h3 class="text-sm font-medium text-muted-foreground">Remarks</h3>
          <Textarea
            v-model="form.remarks"
            placeholder="Enter general remarks"
            class="mt-2"
          />
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="sticky bottom-0 bg-background pt-4 border-t">
        <div class="flex justify-between items-center">
          <!-- Purchasing Officer Actions -->
          <div v-if="userRole === 'purchasing'">
            <div v-if="canvas.status === 'draft'" class="space-x-2">
              <Button 
                variant="default" 
                @click="handleAction('submit')"
                :disabled="form.processing"
              >
                <ChevronRight class="h-4 w-4 mr-1" />
                Submit
              </Button>
            </div>
            <div v-if="canvas.status === 'rejected'" class="space-x-2">
              <Button 
                variant="outline" 
                @click="emit('reupload')"
              >
                <Upload class="h-4 w-4 mr-1" />
                Re-upload
              </Button>
            </div>
          </div>

          <!-- Accounting Actions -->
          <div v-if="userRole === 'accounting' && canvas.status === 'submitted'" class="space-x-2">
            <Button 
              variant="success" 
              @click="handleAction('approve')"
              :disabled="form.processing || !form.comments.trim()"
            >
              <Check class="h-4 w-4 mr-1" />
              Approve
            </Button>
            <Button 
              variant="destructive" 
              @click="handleAction('reject')"
              :disabled="form.processing || !form.comments.trim() || !form.remarks.trim()"
            >
              <X class="h-4 w-4 mr-1" />
              Reject
            </Button>
          </div>

          <!-- Executive Director Actions -->
          <div v-if="userRole === 'executive_director' && canvas.status === 'pending_approval'" class="space-x-2">
            <Button 
              variant="success" 
              @click="handleAction('final_approve')"
              :disabled="form.processing || !form.comments.trim() || !form.selected_file"
            >
              <Check class="h-4 w-4 mr-1" />
              Final Approve
            </Button>
            <Button 
              variant="destructive" 
              @click="handleAction('reject')"
              :disabled="form.processing || !form.comments.trim() || !form.remarks.trim()"
            >
              <X class="h-4 w-4 mr-1" />
              Reject
            </Button>
          </div>
        </div>

        <!-- Common Actions -->
        <div class="flex gap-2 justify-end mt-4">
          <div v-if="canvas.status === 'approved' && userRole === 'purchasing'">
            <Button 
              variant="default" 
              @click="goToCreate()"
              :disabled="form.processing"
            >
              <Check class="h-4 w-4 mr-1" />
              Create P.O.
            </Button>
          </div>
          <Button 
            @click="downloadFile()"
            class="gap-2"
            variant="outline"
            :disabled="isDownloading"
          >
            <Download class="h-4 w-4" />
            <span>{{ isDownloading ? 'Downloading...' : 'Download All' }}</span>
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>