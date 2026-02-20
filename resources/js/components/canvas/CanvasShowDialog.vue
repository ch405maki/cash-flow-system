<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { router } from '@inertiajs/vue3';
import { formatDateTime } from '@/lib/utils';

import { Dialog, DialogContent, DialogHeader, DialogFooter, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';

// Import components
import CanvasStatusBadge from './canvasShowPartials/CanvasStatusBadge.vue';
import CanvasAlertNote from './canvasShowPartials/CanvasAlertNote.vue';
import CanvasApprovalHistory from './canvasShowPartials/CanvasApprovalHistory.vue';
import CanvasFileList from './canvasShowPartials/CanvasFileList.vue';
import CanvasComments from './canvasShowPartials/CanvasComments.vue';
import CanvasPdfPreview from './canvasShowPartials/CanvasPdfPreview.vue';
import CanvasActionButtons from './canvasShowPartials/CanvasActionButtons.vue';

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

// State management
const isDownloading = ref(false);
const showTwoColumnLayout = ref(false);
const selectedFileForPreview = ref<any>(null);
const dialogWidth = ref('sm:max-w-[625px]');

// Computed properties
const isApproved = computed(
  () => props.canvas.status === 'approved' || props.canvas.status === 'poCreated'
);

const hasLinkedOrder = computed(() => props.canvas.request_to_order !== null);

const approvedFile = computed(() => {
  if (props.canvas.status !== 'approved' && props.canvas.status !== 'poCreated') return null;
  return props.canvas.selected_files?.[0]?.file || null;
});

// Form handling
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

// PDF Preview functions
const openPdfPreview = (file) => {
  selectedFileForPreview.value = file;
  showTwoColumnLayout.value = true;
  dialogWidth.value = 'sm:max-w-[90vw]';
};

const closePreviewPanel = () => {
  showTwoColumnLayout.value = false;
  dialogWidth.value = 'sm:max-w-[625px]';
  selectedFileForPreview.value = null;
};

// File download
const downloadFile = async (fileId = null) => {
  try {
    isDownloading.value = true;
    const link = document.createElement('a');
    
    if (fileId) {
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

// Action handlers
const handleAction = async (action) => {
  try {
    const payload = {
      status: "approved", 
      comments: form.comments,
      remarks: form.remarks,
    };

    if (action === 'final_approve') {
      if (!form.selected_file) {
        toast.error('Please select a file for approval');
        return;
      }
      
      payload.selected_file = form.selected_file;
      payload.file_remarks = form.file_remarks;
    }

    await form.patch(route('canvas.update', props.canvas.id), {
      ...payload,
      preserveScroll: true,
      onSuccess: () => {
        emit('updated');
        emit('update:open', false);
        toast.success(`Canvas ${action} successfully`);
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

// Navigation
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
    <DialogContent :class="[dialogWidth, 'p-0 gap-0 flex flex-col max-h-[90vh]']">
      <DialogHeader class="px-4 py-4 border-b shrink-0">
        <DialogTitle class="flex items-center gap-2">
          <PageHeader :title="canvas.title || 'Untitled Canvas'" />
        </DialogTitle>
        <DialogDescription 
          v-if="hasLinkedOrder" 
          class="hover:underline hover:cursor-pointer hover:text-violet-800" 
          @click="viewRequest(canvas.request_to_order.id)"
        >
          Linked to Order # {{ canvas.request_to_order.order_no }} | {{ formatDateTime(canvas.request_to_order.order_date) }}
        </DialogDescription>
        <DialogDescription v-else>
          Not linked to any order. 
        </DialogDescription>
      </DialogHeader>

      <!-- Scrollable Content Area - Dynamic Height -->
      <div class="flex flex-1 min-h-0">
        <!-- Left Column (Main Content) - Scrollable -->
        <div 
          class="overflow-y-auto px-6 py-4 scrollbar-thin scrollbar-thumb-border scrollbar-track-transparent" 
          :class="showTwoColumnLayout ? 'w-1/2 border-r' : 'w-full'"
        >
          <div class="grid gap-4 py-4">
            <!-- Status and Upload Info -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <h3 class="text-sm font-medium text-muted-foreground">Status</h3>
                <CanvasStatusBadge :status="canvas.status" />
              </div>
              <div>
                <h3 class="text-sm font-medium text-muted-foreground">Canvas Uploaded</h3>
                <p class="mt-1 text-sm">
                  {{ formatDateTime(canvas.created_at)}}
                </p>
              </div>
            </div>

            <!-- Alert Note -->
            <CanvasAlertNote :note="canvas.note" />

            <!-- Approved File Section -->
            <div 
              v-if="approvedFile"
              class="flex items-center justify-between cursor-pointer hover:bg-gray-50 p-2 rounded border"
              @click="openPdfPreview(approvedFile)"
            >
              <div class="flex items-center gap-3">
                <FileText class="h-5 w-5 text-muted-foreground" />
                <span class="text-muted-foreground capitalize text-sm">
                  {{ approvedFile?.original_filename }}
                </span>
                <Badge v-if="isPdfFile(approvedFile)" variant="secondary" class="text-xs">
                  PDF
                </Badge>
              </div>
              <Button 
                variant="ghost" 
                size="sm"
                @click.stop="downloadFile(approvedFile.id)"
              >
                <Download class="h-4 w-4" />
              </Button>
            </div>

            <!-- Approval History -->
            <CanvasApprovalHistory :approvals="canvas.approvals" />

            <!-- Files Section -->
            <template v-if="!isApproved && canvas.files?.length">
              <div>
                <h3 class="text-sm font-medium text-muted-foreground">Files</h3>
                <CanvasFileList 
                  :files="canvas.files"
                  :selected-file-id="selectedFileForPreview?.id"
                  @preview="openPdfPreview"
                  @download="downloadFile"
                  @select-file="(file) => form.selected_file = file.id"
                />
              </div>
            </template>

            <!-- Comments Section -->
            <div v-if="['audit', 'executive_director', 'purchasing'].includes(userRole) && !isApproved">
              <CanvasComments
                v-model="form.comments"
                :label="userRole === 'audit' ? 'Audit Comments' : 'Approval Comments'"
                :placeholder="userRole === 'audit' 
                  ? 'Enter your audit comments...' 
                  : 'Enter approval comments...'"
              />
            </div>
          </div>
        </div>

        <!-- Right Column (PDF Preview) -->
        <CanvasPdfPreview
          v-if="showTwoColumnLayout && selectedFileForPreview"
          :file="selectedFileForPreview"
          :canvas-id="canvas.id"
          @close="closePreviewPanel"
          @download="downloadFile"
        />
      </div>
      <!-- Fixed Footer with Action Buttons -->
      <DialogFooter class="border-t px-4 mt-0 shrink-0">
        <div class="w-full flex justify-end gap-2">
          <CanvasActionButtons
            :user-role="userRole"
            :canvas-status="canvas.status"
            :has-comments="!!form.comments.trim()"
            :has-selected-file="!!form.selected_file"
            :is-downloading="isDownloading"
            :has-approved-file="!!approvedFile"
            :form-processing="form.processing"
            @action="handleAction"
            @reupload="emit('reupload')"
            @create-po="goToCreate"
            @download-approved="downloadFile(approvedFile?.id)"
          />
        </div>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>