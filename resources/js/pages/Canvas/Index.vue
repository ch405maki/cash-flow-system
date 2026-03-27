<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
  FileText, 
  UserRoundCheck, 
  CheckCircle, 
  XCircle, 
  Eye,
  Pencil,
  Check,
  X,
  ChevronRight,
  Upload
} from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

import StatusBadge from '@/components/StatusBadge.vue';
import CanvasUploadDialog from '@/components/canvas/CanvasUploadDialog.vue'
import CanvasShowDialog from '@/components/canvas/CanvasShowDialog.vue'
import { ref } from 'vue';
import { formatDateTime } from '@/lib/utils';

defineProps<{
  canvases: Array<any>;
  authUserRole: string;
}>();

// Dialog state management
const showDialog = ref(false);
const showUploadDialog = ref(false);
const selectedCanvas = ref(null);
const canvasToReupload = ref(null);

const showCanvas = (canvas) => {
  selectedCanvas.value = canvas;
  showDialog.value = true;
};

const refreshCanvases = () => {
  router.reload({ only: ['canvases'] });
};

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Canvas', href: '/' },
] 
</script>

<template>
  <Head title="Canvases" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
    <PageHeader 
      title="Canvas" 
      subtitle="Monitoring submitted canvas requests"
    />
    <div v-if="canvases.length > 0">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Files</TableHead>
            <TableHead>Notes</TableHead>
            <TableHead>Uploaded</TableHead>
            <TableHead>Status</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="canvas in canvases" :key="canvas.id"  @click="showCanvas(canvas)" class="hover:cursor-pointer">
            <TableCell>
              <div class="flex items-center gap-4">
                <FileText class="h-5 w-5 text-muted-foreground" />
                <div class="grid gap-1">
                  <div class="font-medium">
                    {{ canvas.title || 'Untitled Canvas' }}
                  </div>
                  <div class="text-xs text-muted-foreground">
                    {{ canvas.files?.length || 0 }} files
                  </div>
                </div>
              </div>
            </TableCell>

            <TableCell>
              <div class="text-sm text-muted-foreground max-w-[200px] truncate">
                {{ canvas.note || 'No Notes' }}
              </div>
            </TableCell>
            
            <TableCell>
              <div class="text-sm">
                {{ formatDateTime(canvas.created_at) }}
              </div>
            </TableCell>
            <TableCell>
              <StatusBadge 
                :status="canvas.status"
                show-icon
                size="md"
              />
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
      <FileText class="h-8 w-8 text-muted-foreground" />
      <p class="mt-2 text-sm text-muted-foreground">No recent canvas found</p>
      <p class="text-xs text-muted-foreground">Canvas files will appear here</p>
    </div>
    
    <!-- Canvas Show Dialog -->
    <CanvasShowDialog
      v-if="selectedCanvas"
      :canvas="selectedCanvas"
      :open="showDialog"
      :user-role="authUserRole"
      @update:open="val => showDialog = val"
      @updated="refreshCanvases"
    />
    
    <!-- Reupload Dialog -->
    <CanvasUploadDialog
      v-if="canvasToReupload"
      :canvas="canvasToReupload"
      :open="showUploadDialog"
      @update:open="val => showUploadDialog = val"
      @success="refreshCanvases"
    />
    </div>
  </AppLayout>
</template>