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
    
    // Create a temporary anchor element
    const link = document.createElement('a');
    
    if (fileId) {
      // Download single file
      const file = props.canvas.files.find(f => f.id === fileId);
      if (!file) {
        throw new Error('File not found');
      }
      
      link.href = route('canvas.download.file', { 
        canvas: props.canvas.id, 
        file: fileId 
      });
      link.setAttribute('download', file.original_filename);
    }
    
    // Append to body, click and remove
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
  } catch (error) {
    console.error('Download failed:', error);
    toast.error(`Download failed: ${error.message}`);
  } finally {
    isDownloading.value = false;
  }
};

const handleAction = async (action) => {
  try {
    const payload = {
      status: "approved", 
      comments: "This file Approved",
      remarks: "",
      selected_file: 1, 
      file_remarks: "I want this supplier" 
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

// Get the approved file
const approvedFile = computed(() => {
  if (props.canvas.status !== 'approved' && props.canvas.status !== 'poCreated') return null;
  return props.canvas.selected_files?.[0]?.file || null;
});

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
        <!-- Status and Upload Info -->
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

        <!-- Approved File Section (only shown for approved canvases) -->
        <div v-if="approvedFile">
          <h3 class="text-sm font-medium text-muted-foreground">Approved File</h3>
          <div class="p-3 border rounded-lg bg-green-50 mt-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <FileText class="h-5 w-5 text-green-600" />
                <span class="font-medium">{{ approvedFile.original_filename }}</span>
              </div>
              <Button 
                variant="outline" 
                size="sm"
                @click="downloadFile(approvedFile.id)"
              >
                <Download class="h-4 w-4 mr-1" />
                Download
              </Button>
            </div>
            <p v-if="canvas.selected_files[0]?.remarks" class="mt-2 text-sm text-muted-foreground">
              Remarks: {{ canvas.selected_files[0].remarks }}
            </p>
          </div>
        </div>

        <!-- Approval History (always shown if exists) -->
        <div v-if="canvas.approvals?.length">
          <h3 class="text-sm font-medium text-muted-foreground">Approval History</h3>
          <div class="space-y-2 mt-2">
            <div 
              v-for="approval in canvas.approvals" 
              :key="approval.id" 
              class="p-3 border rounded"
              :class="{
                'bg-green-50': approval.approved,
                'bg-red-50': !approval.approved
              }"
            >
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

        <!-- Only show these sections if NOT approved -->
        <template v-if="!isApproved">
          <!-- Files Section (for non-approved canvases) -->
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
        </template>

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
          <!-- ... (keep existing action buttons code) ... -->
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
          <!-- Only show download all button for non-approved canvases -->
          <Button 
            v-if="!isApproved"
            @click="downloadFile()"
            class="gap-2"
            variant="outline"
            :disabled="isDownloading"
          >
            <Download class="h-4 w-4" />
            <span>{{ isDownloading ? 'Downloading...' : 'Download All' }}</span>
          </Button>
          <!-- Show download approved file button for approved canvases -->
          <Button 
            v-else-if="approvedFile"
            @click="downloadFile(approvedFile.id)"
            class="gap-2"
            variant="outline"
            :disabled="isDownloading"
          >
            <Download class="h-4 w-4" />
            <span>{{ isDownloading ? 'Downloading...' : 'Download Approved File' }}</span>
          </Button>
        </div>
      </div>
    </DialogContent>
  </Dialog>
</template>