<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { UploadCloud } from 'lucide-vue-next';

const props = defineProps({
  request: {
    type: Object,
    required: false,
    default: null
  }
});

const isOpen = ref(false);
const fileInput = ref(null);

const form = useForm({
  file: null,
  note: '',
  request_to_order_id: props.request?.id || null // Make optional
});

const handleFileChange = (e) => {
  form.file = e.target.files[0];
};

const submit = () => {
  form.post(route('canvas.store'), {
    onSuccess: () => {
      form.reset();
      isOpen.value = false;
    },
  });
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button size="sm" class="gap-2">
        <UploadCloud class="h-4 w-4" />
        <span>Upload Canvas</span>
      </Button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-[625px] max-h-[90vh] overflow-y-auto">
      <DialogHeader>
        <DialogTitle>
          {{ request ? `Upload Canvas for Order #${request.order_no}` : 'Upload New Canvas' }}
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
          <label class="block text-sm font-medium">Canvas File</label>
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
            />
          </div>
          <p v-if="form.file" class="text-sm text-muted-foreground mt-2">
            Selected: {{ form.file.name }}
          </p>
          <p v-if="form.errors.file" class="text-sm text-destructive mt-2">
            {{ form.errors.file }}
          </p>
        </div>

        <div class="space-y-2">
          <label for="note" class="block text-sm font-medium">Note</label>
          <Textarea
            id="note"
            v-model="form.note"
            placeholder="Add any internal notes"
          />
          <p v-if="form.errors.note" class="text-sm text-destructive mt-2">
            {{ form.errors.note }}
          </p>
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
            :disabled="form.processing || !form.file"
          >
            <span v-if="form.processing">Uploading...</span>
            <span v-else>Upload Canvas</span> 
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>