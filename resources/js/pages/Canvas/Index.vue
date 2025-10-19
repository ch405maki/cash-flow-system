<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
  FileText, 
  UserRoundCheck, 
  Clock, 
  CheckCircle, 
  XCircle, 
  Download, 
  Eye,
  Pencil,
  Check,
  X,
  ChevronRight,
  ChevronLeft,
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
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import CanvasUploadDialog from '@/components/canvas/CanvasUploadDialog.vue'
import CanvasShowDialog from '@/components/canvas/CanvasShowDialog.vue'
import { ref } from 'vue';
import { formatDateTime } from '@/lib/utils';

defineProps<{
  canvases: Array<any>;
  authUserRole: string;
}>();

const statusIcons = {
  draft: Pencil,
  submitted: ChevronRight,
  pending_approval: UserRoundCheck,
  approved: CheckCircle,
  rejected: XCircle,
};

const statusVariants = {
  draft: 'bg-gray-100 text-gray-800 hover:bg-gray-200',
  submitted: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
  pending_approval: 'bg-purple-100 text-purple-800 hover:bg-purple-200',
  approved: 'bg-green-100 text-green-800 hover:bg-green-200',
  rejected: 'bg-red-100 text-red-800 hover:bg-red-200',
};

const actionButtons = {
  purchasing: {
    draft: [
      { label: 'Submit', icon: ChevronRight, action: 'submit', variant: 'default' }
    ],
    submitted: [],
    pending_approval: [],
    approved: [],
    rejected: [
      { label: 'Re-upload', icon: Upload, action: 'reupload', variant: 'outline' }
    ]
  },
  auditor: {
    submitted: [
      { label: 'Review', icon: Eye, action: 'review', variant: 'default' },
      { label: 'Approve', icon: Check, action: 'approve', variant: 'success' },
      { label: 'Reject', icon: X, action: 'reject', variant: 'destructive' }
    ],
    pending_approval: [],
    approved: [],
    rejected: []
  },
  executive_director: {
    pending_approval: [
      { label: 'Review', icon: Eye, action: 'review', variant: 'default' },
      { label: 'Final Approve', icon: Check, action: 'final_approve', variant: 'success' }
    ],
    approved: [],
    rejected: []
  }
};

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
  <Head title="My Canvases" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
    <PageHeader 
      title="Canvas" 
      subtitle="Monitoring submitted canvas requests"
    />
    <div v-if="canvases.length > 0">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Files</TableHead>
            <TableHead>Status</TableHead>
            <TableHead>Notes</TableHead>
            <TableHead>Uploaded</TableHead>
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
              <Badge :class="statusVariants[canvas.status]">
                <component 
                  :is="statusIcons[canvas.status]" 
                  class="h-3 w-3 mr-1" 
                />
                <span class="capitalize">{{ canvas.status.replace('_', ' ') }}</span>
              </Badge>
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