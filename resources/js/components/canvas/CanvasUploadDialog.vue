<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { UploadCloud, X, Check, FileText, AlertCircle } from 'lucide-vue-next';
import axios from 'axios';

const toast = useToast();

const props = defineProps({
  request: {
    type: Object,
    required: false,
    default: null
  },
  canvas: {
    type: Object,
    required: false,
    default: null
  },
  open: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:open', 'success']);

const isOpen = ref(props.open);
const fileInput = ref(null);
const files = ref([]);
const isUploading = ref(false);

watch(() => props.open, (val) => {
  isOpen.value = val;
});

watch(isOpen, (val) => {
  emit('update:open', val);
  if (!val) {
    files.value = [];
    form.reset();
  }
});

const form = useForm({
  title: '',
  files: [],
  note: '',
  request_to_order_id: props.request?.id || null,
  canvas_id: props.canvas?.id || null
});

// Set initial title if editing
if (props.canvas) {
  form.title = props.canvas.title || '';
  form.note = props.canvas.note || '';
}

const handleFileChange = (e) => {
  const newFiles = Array.from(e.target.files);
  
  // Validate file types and size
  const validFiles = newFiles.filter(file => {
    const validTypes = ['application/pdf', 'application/msword', 
                       'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                       'application/vnd.ms-excel',
                       'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
    const isValidType = validTypes.includes(file.type);
    const isValidSize = file.size <= 10 * 1024 * 1024; // 10MB
    
    if (!isValidType) {
      toast.warning(`File ${file.name} has invalid type. Only PDF, DOC, DOCX, XLS, XLSX are allowed.`);
    }
    if (!isValidSize) {
      toast.warning(`File ${file.name} is too large. Maximum size is 10MB.`);
    }
    
    return isValidType && isValidSize;
  });
  
  if (validFiles.length > 0) {
    files.value = [...files.value, ...validFiles];
    form.files = files.value;
  }
};

const removeFile = (index) => {
  const removedFile = files.value[index];
  files.value.splice(index, 1);
  form.files = files.value;
  toast.info(`Removed ${removedFile.name}`);
};

const submit = () => {
  // Validate minimum files
  if (files.value.length < 1) {
    toast.error('Please upload at least 1 file');
    return;
  }

  // Validate title
  if (!form.title.trim()) {
    toast.error('Please enter a canvas title');
    return;
  }

  isUploading.value = true;
  
  const loadingToast = toast.info('Uploading canvas...', {
    timeout: false
  });

  const formData = new FormData();

  if (props.canvas) {
    formData.append('_method', 'put');
  }

  files.value.forEach(file => {
    formData.append('files[]', file);
  });

  formData.append('title', form.title);
  formData.append('note', form.note);

  if (form.request_to_order_id) {
    formData.append('request_to_order_id', form.request_to_order_id);
  }

  if (form.canvas_id) {
    formData.append('canvas_id', form.canvas_id);
  }

  const url = props.canvas 
    ? route('canvas.update', props.canvas.id)
    : route('canvas.store');

  axios.post(url, formData, {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
  .then(response => {
    toast.dismiss(loadingToast);
    toast.success(
      props.canvas 
        ? 'Canvas updated successfully!' 
        : 'Canvas uploaded successfully!',
      {
        icon: Check,
        timeout: 5000
      }
    );
    
    emit('success');
    isOpen.value = false;
    form.reset();
    files.value = [];
  })
  .catch(error => {
    toast.dismiss(loadingToast);
    console.error('Upload failed:', error);
    
    if (error.response?.data?.errors) {
      // Display validation errors
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        errors[key].forEach(message => {
          toast.error(message);
        });
      });
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message);
    } else {
      toast.error('Upload failed. Please try again.', {
        icon: AlertCircle
      });
    }
  })
  .finally(() => {
    isUploading.value = false;
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger v-if="!canvas" as-child>
      <Button size="sm" class="gap-2">
        <UploadCloud class="h-4 w-4" />
        <span>Upload Canvas</span>
      </Button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-[625px] max-h-[90dvh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>
          {{ canvas ? 'Update Canvas' : request ? `Upload Canvas for Order #${request.order_no}` : 'Upload New Canvas' }}
        </DialogTitle>
      </DialogHeader>
      
      <form @submit.prevent="submit" class="grid gap-4 py-4">
        <div class="space-y-2">
          <label for="title" class="block text-sm font-medium">Canvas Title</label>
          <Input
            id="title"
            v-model="form.title"
            placeholder="Enter canvas title"
            required
          />
        </div>

        <div class="space-y-2">
          <label class="block text-sm font-medium">Canvas Files (Minimum 1)</label>
          <div 
            @click="fileInput?.click()"
            class="flex flex-col items-center justify-center px-6 py-12 border-2 border-dashed rounded-md cursor-pointer hover:bg-accent/50 transition-colors"
            :class="{ 'opacity-50 pointer-events-none': isUploading }"
          >
            <UploadCloud class="mx-auto h-12 w-12 text-muted-foreground" />
            <div class="mt-4 flex text-sm text-muted-foreground">
              <span class="font-medium">Click to upload</span>
              <span class="ml-1">or drag and drop</span>
            </div>
            <p class="text-xs text-muted-foreground mt-2">
              PDF, DOCX, XLSX up to 10MB
            </p>
            <input 
              ref="fileInput"
              type="file" 
              class="hidden" 
              @change="handleFileChange"
              accept=".pdf,.doc,.docx,.xls,.xlsx"
              multiple
              :disabled="isUploading"
            />
          </div>
          
          <div v-if="files.length > 0" class="mt-4 space-y-2">
            <div v-for="(file, index) in files" :key="index" class="flex items-center justify-between p-2 border rounded">
              <div class="flex items-center gap-2">
                <FileText class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm truncate max-w-[300px]">{{ file.name }}</span>
                <span class="text-xs text-muted-foreground">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</span>
              </div>
              <Button 
                variant="ghost" 
                size="sm" 
                @click.stop="removeFile(index)"
                class="h-6 w-6 p-0 text-destructive"
                :disabled="isUploading"
              >
                <X class="h-3 w-3" />
              </Button>
            </div>
          </div>
          
          <p v-if="files.length < 1" class="text-sm text-muted-foreground mt-2">
            {{ 1 - files.length }} more file required
          </p>
        </div>

        <div class="space-y-2">
          <label for="note" class="block text-sm font-medium">Note</label>
          <Textarea
            id="note"
            v-model="form.note"
            placeholder="Add any internal notes"
            :disabled="isUploading"
          />
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Button 
            variant="outline" 
            type="button" 
            @click="isOpen = false"
            :disabled="isUploading"
          >
            Cancel
          </Button>
          <Button 
            type="submit" 
            :disabled="files.length < 1 || !form.title.trim() || isUploading"
            class="gap-2"
          >
            <span v-if="isUploading" class="animate-spin mr-2">⏳</span>
            <Check v-else class="h-4 w-4" />
            <span>{{ isUploading ? 'Uploading...' : (canvas ? 'Update Canvas' : 'Upload Canvas') }}</span>
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>