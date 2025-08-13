<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { UploadCloud, X, Check } from 'lucide-vue-next';
import axios from 'axios';

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

watch(() => props.open, (val) => {
  isOpen.value = val;
});

watch(isOpen, (val) => {
  emit('update:open', val);
  if (!val) {
    files.value = [];
  }
});

const form = useForm({
  title: '',
  files: [],
  note: '',
  request_to_order_id: props.request?.id || null,
  canvas_id: props.canvas?.id || null
});

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
      alert(`File ${file.name} has invalid type. Only PDF, DOC, DOCX, XLS, XLSX are allowed.`);
    }
    if (!isValidSize) {
      alert(`File ${file.name} is too large. Maximum size is 10MB.`);
    }
    
    return isValidType && isValidSize;
  });
  
  files.value = [...files.value, ...validFiles];
  form.files = files.value;
};

const removeFile = (index) => {
  files.value.splice(index, 1);
  form.files = files.value;
};

const submit = () => {
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
  }).then(response => {
    emit('success');
    isOpen.value = false;
    form.reset();
    files.value = [];
  }).catch(error => {
    console.error('Upload failed:', error);
    alert('Upload failed. Please try again.');
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
        <div v-if="request" class="space-y-2">
          <label class="block text-sm font-medium">Order Number</label>
          <Input 
            :model-value="request.order_no" 
            disabled
          />
        </div>

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
          <label class="block text-sm font-medium">Canvas Files (Minimum 3)</label>
          <div 
            @click="fileInput?.click()"
            class="flex flex-col items-center justify-center px-6 py-12 border-2 border-dashed rounded-md cursor-pointer hover:bg-accent/50 transition-colors"
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
              >
                <X class="h-3 w-3" />
              </Button>
            </div>
          </div>
          
          <p v-if="files.length < 3" class="text-sm text-muted-foreground mt-2">
            {{ 3 - files.length }} more files required
          </p>
        </div>

        <div class="space-y-2">
          <label for="note" class="block text-sm font-medium">Note</label>
          <Textarea
            id="note"
            v-model="form.note"
            placeholder="Add any internal notes"
          />
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Button 
            variant="outline" 
            type="button" 
            @click="isOpen = false"
          >
            Cancel
          </Button>
          <Button 
            type="submit" 
            :disabled="files.length < 1"
            class="gap-2"
          >
            <Check class="h-4 w-4" />
            <span>{{ canvas ? 'Update Canvas' : 'Upload Canvas' }}</span>
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>