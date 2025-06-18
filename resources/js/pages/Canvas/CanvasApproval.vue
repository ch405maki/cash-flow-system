<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { UserRoundCheck, Clock, CheckCircle, XCircle } from 'lucide-vue-next';
import axios from 'axios';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs'
import CanvasShowDialog from '@/components/canvas/CanvasShowDialog.vue'
import CanvasTable from '@/components/canvas/CanvasTable.vue'
import { ref, computed } from 'vue';

const props = defineProps<{
  canvases: Array<any>;
  authUserRole: string;
}>();


/* split once, reuse everywhere */
const approvedCanvases  = computed(() =>
  props.canvases.filter(c => c.status === 'approved')
)
const poCreatedCanvases = computed(() =>
  props.canvases.filter(c => c.status === 'poCreated')
)

const statusIcons = {
  pending: Clock,
  approved: CheckCircle,
  rejected: XCircle,
  forEOD: UserRoundCheck,
};

const statusVariants = {
  pending: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
  approved: 'bg-green-100 text-green-800 hover:bg-green-200',
  rejected: 'bg-red-100 text-red-800 hover:bg-red-200',
  forEOD: 'bg-purple-100 text-purple-800 hover:bg-purple-200',
};

// Dialog state management
const showDialog = ref(false);
const selectedCanvas = ref(null);

const downloadFile = async (canvas) => {
  try {
    canvas.isDownloading = true;
    
    const response = await axios.get(route('canvas.download', canvas.id), {
      responseType: 'blob',
      onDownloadProgress: (progressEvent) => {
        const percentCompleted = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
        console.log(`Download progress: ${percentCompleted}%`);
      }
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', canvas.original_filename);
    document.body.appendChild(link);
    link.click();
    
    window.URL.revokeObjectURL(url);
    document.body.removeChild(link);
    
  } catch (error) {
    console.error('Download failed:', error);
    alert('Download failed. Please try again.');
  } finally {
    canvas.isDownloading = false;
  }
};

const showCanvas = (canvas) => {
  selectedCanvas.value = null; 
  selectedCanvas.value = canvas;
  showDialog.value = true;
};

const refreshCanvases = () => {
  router.reload({ only: ['canvases'] });
};

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Canvas', href: '/' },
] 
</script>

<template>
  <Head title="My Canvases" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
    <Tabs default-value="approved" class="w-full">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Canvas</h1>

            <!-- Tabs list: only as wide as its content -->
            <TabsList class="flex gap-2">
                <TabsTrigger
                value="approved"
                class="px-3 py-1.5 text-sm leading-none"
                >
                Approved
                </TabsTrigger>
                <TabsTrigger
                value="poCreated"
                class="px-3 py-1.5 text-sm leading-none"
                >
                PO Created
                </TabsTrigger>
            </TabsList>
        </div>


        <!-- Approved Tab -->
        <TabsContent value="approved">
            <TableOrEmpty :items="approvedCanvases" empty-text="No approved canvases" />
            <CanvasTable
            :canvases="approvedCanvases"
            :status-icons="statusIcons"
            :status-variants="statusVariants"
            @show="showCanvas"
            @download="downloadFile"
            />
        </TabsContent>

        <!-- PO Created Tab -->
        <TabsContent value="poCreated">
            <TableOrEmpty :items="poCreatedCanvases" empty-text="No PO-created canvases" />
            <CanvasTable
            :canvases="poCreatedCanvases"
            :status-icons="statusIcons"
            :status-variants="statusVariants"
            @show="showCanvas"
            @download="downloadFile"
            />
        </TabsContent>
    </Tabs>
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
  </AppLayout>
</template>
