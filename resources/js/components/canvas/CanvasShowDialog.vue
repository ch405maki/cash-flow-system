<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Download, ArrowLeft, Clock, CheckCircle, UserRoundCheck, XCircle, Check, X, Pencil, ChevronRight, FileText, LoaderCircle } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Textarea } from '@/components/ui/textarea';
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { formatDateTime } from '@/lib/utils';
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

// State management
const isDownloading = ref(false);
const showAlert = ref(true);
const showTwoColumnLayout = ref(false);
const selectedFileForPreview = ref<{
  id: number;
  original_filename: string;
  mimetype?: string;
  url?: string;
} | null>(null);
const previewLoading = ref(false);
const previewError = ref(false);
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

// Status icons and variants
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
  previewLoading.value = true;
  previewError.value = false;

  if (!isPdfFile(file)) {
    previewError.value = true;
    previewLoading.value = false;
  }
};

const closePreviewPanel = () => {
  showTwoColumnLayout.value = false;
  dialogWidth.value = 'sm:max-w-[625px]';
  selectedFileForPreview.value = null;
};

const getPreviewUrl = (file) => {
  if (file.url) {
    return file.url;
  }
  
  return route('canvas.preview.file', { 
    canvas: props.canvas.id, 
    file: file.id 
  }) + '#view=fitH&toolbar=0&navpanes=0';
};

const isPdfFile = (file) => {
  return file?.mimetype?.includes('pdf') || 
         file?.original_filename?.toLowerCase().endsWith('.pdf') ||
         file?.type?.includes('pdf');
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
    <DialogContent 
      :class="[
        showTwoColumnLayout ? 'sm:max-w-[94vw]' : 'sm:max-w-[625px]',
        'max-h-[94vh] overflow-y-auto'
      ]">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <span class="truncate max-w-[400px] capitalize">{{ canvas.title || 'Untitled Canvas' }}</span>
        </DialogTitle>
        <DialogDescription v-if="hasLinkedOrder" class="hover:underline hover:cursor-pointer hover:text-violet-800" @click="viewRequest(canvas.request_to_order.id)">
          Linked to Order # {{ canvas.request_to_order.order_no }} | {{ formatDateTime(canvas.request_to_order.order_date) }}
        </DialogDescription>
        <DialogDescription v-else>
          Not linked to any order. 
        </DialogDescription>
      </DialogHeader>

      <div class="flex h-[calc(90vh-150px)]">
        <!-- Left Column (Main Content) -->
        <div class="flex-1 " :class="showTwoColumnLayout ? 'w-1/2 border-r pr-4' : 'w-full'">
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
                <h3 class="text-sm font-medium text-muted-foreground">Canvas Uploaded</h3>
                <p class="mt-1 text-sm">
                  {{ formatDateTime(canvas.created_at)}}
                </p>
              </div>
            </div>

            <Alert 
              v-if="showAlert && canvas?.note.length > 0" 
              variant="warning" 
              class="relative pr-10"
            >
              <AlertCircle class="h-4 w-4" />
              <AlertTitle>Note!</AlertTitle>
              <AlertDescription>
                <template v-if="canvas">
                  <h1 class="capitalize">{{ canvas?.note || 'No Note' }}</h1>
                </template>
                <template v-else>
                  (Canvas details not available)
                </template>
              </AlertDescription>
              <button
                class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
                @click="showAlert = false"
                aria-label="Dismiss"
              >
                <X class="h-4 w-4 text-yellow-700" />
              </button>
            </Alert>

            <!-- Approved File Section -->
            <div v-if="approvedFile">
              <h3 class="text-sm font-medium text-muted-foreground">Approved File</h3>
              <div class="p-3 border rounded-lg mt-2">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <FileText class="h-5 w-5 text-muted-foreground" />
                    <span class="text-muted-foreground capitalize text-sm">{{ approvedFile.original_filename }}</span>
                  </div>
                  <Button 
                    variant="ghost" 
                    size="sm"
                    @click="downloadFile(approvedFile.id)"
                  >
                    <Download class="h-4 w-4" />
                  </Button>
                </div>
                <p v-if="canvas.selected_files[0]?.remarks" class="mt-2 text-sm text-muted-foreground">
                  Remarks: {{ canvas.selected_files[0].remarks }}
                </p>
              </div>
            </div>
            <!-- Approval History -->
            <div v-if="canvas.approvals?.length">
              <h3 class="text-sm font-medium text-muted-foreground mb-3">History</h3>
              <div class="relative pl-6">
                <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>

                <div v-for="(approval, index) in canvas.approvals" 
                    :key="approval.id"
                    class="relative mb-6 last:mb-0">
                  
                  <div class="bg-green-500 border-2 border-green-500 absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10">
                    <component 
                      :is="approval.approved ? CheckCircle : CheckCircle"
                      class="h-5 w-5 text-white"
                    />
                  </div>

                  <div v-if="index < canvas.approvals.length - 1"
                      class="absolute -left-6 top-8 h-full w-0.5 ml-4 bg-green-500 z-0"
                  ></div>
                  
                  <div class="pl-4">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2">
                        <span class="capitalize">{{ approval.user.username }}</span>
                      </div>
                      <span class="text-xs text-muted-foreground">
                        {{ formatDateTime(approval.created_at) }}
                      </span>
                    </div>

                    <div class="mt-1 flex items-start gap-2">
                      <p class="text-sm text-gray-700">
                        "{{ approval.comments || 'No comments' }}"
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Only show these sections if NOT approved -->
            <template v-if="!isApproved || isApproved">
              <!-- Files Section -->
              <div v-if="userRole != 'executive_director' && canvas.files?.length && canvas.status != 'approved'">
                <h3 class="text-sm font-medium text-muted-foreground">Files</h3>
                <div class="space-y-2 mt-2">
                  <div 
                    v-for="file in canvas.files" 
                    :key="file.id" 
                    class="flex items-center justify-between p-2 border rounded cursor-pointer transition-colors duration-150 relative z-10"
                    :class="{
                      'bg-blue-50 border-blue-200 dark:bg-blue-900 dark:border-blue-700': selectedFileForPreview?.id === file.id,
                      'hover:bg-gray-100 dark:hover:bg-gray-800': selectedFileForPreview?.id !== file.id
                    }"
                    @click="openPdfPreview(file)"
                  >
                    <div class="flex items-center gap-2">
                      <FileText class="max-h-4 max-w-4 text-muted-foreground" />
                      <span class="text-sm text-foreground truncate max-w-[200px]">
                        {{ file.original_filename }}
                      </span>
                      <Badge v-if="isPdfFile(file)" variant="secondary" class="text-xs">
                        PDF
                      </Badge>
                    </div>
                    <Button 
                      variant="ghost" 
                      size="sm"
                      @click.stop="downloadFile(file.id)"
                    >
                      <Download class="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </div>

              <!-- File Selection for Executive -->
              <div v-if="userRole === 'executive_director' && canvas.files?.length">
                <h3 class="text-sm font-medium text-muted-foreground">Select File for Approval</h3>
                <p class="text-xs text-muted-foreground mb-2">Please select one file to approve</p>
                
                <div class="space-y-2 mt-2">
                  <div 
                    v-for="file in canvas.files" 
                    :key="file.id" 
                    class="flex items-center items-start gap-3 p-2 border rounded hover:bg-zinc-50 dark:hover:bg-zinc-900 cursor-pointer"
                    :class="{
                      'bg-zinc-50 dark:bg-zinc-800 border-zinc-200': selectedFileForPreview?.id === file.id
                    }"
                    @click="openPdfPreview(file)"
                  >
                    <div class="flex items-center h-5">
                      <input
                        type="radio"
                        :id="`file-${file.id}`"
                        v-model="form.selected_file"
                        :value="file.id"
                        class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                        @click.stop
                      >
                    </div>
                    
                    <div class="flex-1 flex items-center justify-between">
                      <label :for="`file-${file.id}`" class="flex items-center gap-2 cursor-pointer">
                        <FileText class="max-h-4 max-w-4 text-muted-foreground" />
                        <span class="text-sm">{{ file.original_filename }}</span>
                        <Badge v-if="isPdfFile(file)" variant="secondary" class="text-xs">
                          PDF
                        </Badge>
                      </label>
                      <Button 
                        variant="ghost" 
                        size="sm"
                        @click.stop="downloadFile(file.id)"
                      >
                        <Download class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Comments Section -->
              <div v-if="['accounting', 'executive_director'].includes(userRole) ">
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
              <div v-if="['purchasing'].includes(userRole) && canvas.status != 'approved'">
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
            </template>
          </div>
        </div>

        <!-- Right Column (PDF Preview) -->
        <div 
          v-if="showTwoColumnLayout" 
          class="w-1/2 pl-4 overflow-y-auto"
        >
          <div class="sticky top-0 bg-background z-10 pb-2 flex justify-between items-center">
            <h3 class="text-sm font-medium">Preview: {{ selectedFileForPreview?.original_filename }}</h3>
            <Button variant="ghost" size="sm" @click="closePreviewPanel">
              <X class="h-4 w-4" />
            </Button>
          </div>
          
          <div v-if="isPdfFile(selectedFileForPreview)" class="h-full border rounded-md overflow-hidden bg-gray-50">
            <div v-if="previewLoading" class="h-full flex items-center justify-center">
              <LoaderCircle class="h-8 w-8 animate-spin text-gray-400" />
            </div>
            
            <iframe 
              v-show="!previewLoading && !previewError"
              :src="getPreviewUrl(selectedFileForPreview)"
              class="w-full h-full min-h-[500px]"
              frameborder="0"
              @load="previewLoading = false"
              @error="previewError = true"
            />
            
            <div v-if="previewError" class="h-full flex flex-col items-center justify-center p-4 text-center">
              <XCircle class="h-8 w-8 text-red-400 mb-2" />
              <p class="text-sm text-gray-600">Failed to load preview</p>
              <Button 
                variant="outline" 
                size="sm" 
                class="mt-4"
                @click="downloadFile(selectedFileForPreview.id)"
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
              @click="downloadFile(selectedFileForPreview.id)"
            >
              <Download class="h-4 w-4 mr-2" />
              Download File
            </Button>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <DialogFooter class="border-t">
        <div class="flex justify-between items-center mt-4">
          <!-- Purchasing Officer Actions -->
          <div v-if="userRole === 'purchasing'">
            <div v-if="canvas.status === 'draft'" class="space-x-2">
              <Button 
                variant="default" 
                @click="handleAction('submit')"
                :disabled="form.processing"
              >
                <ChevronRight class="h-4 w-4" />
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
          <div v-if="userRole === 'accounting' && canvas.status !== 'pending'" class="space-x-2">
            <Button 
              variant="success" 
              @click="handleAction('approve')"
              :disabled="form.processing || !form.comments.trim()"
            >
              <Check class="h-4 w-4 mr-1" />
              Submit
            </Button>
          </div>

          <!-- Executive Director Actions -->
          <div v-if="(userRole === 'executive_director')" class="space-x-2">
            <div v-if="canvas.status === 'pending_approval' || canvas.status === 'submitted'">
              <Button 
                variant="success" 
                size="sm"
                @click="handleAction('final_approve')"
                :disabled="form.processing || !form.comments.trim() || !form.selected_file"
              >
                <Check class="h-4 w-4 mr-1" />
                Approve
              </Button>
            </div>
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
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>