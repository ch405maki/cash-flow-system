<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { formatDate } from '@/lib/utils'
import AppLayout from '@/layouts/AppLayout.vue';
import { UserRoundCheck, Clock, CheckCircle, XCircle, Download } from 'lucide-vue-next';
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
import { ref, computed, watch } from 'vue';
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
  canvases: Array<any>;
  authUserRole: string;
  status?: string;
}>();

// Use status from props if available, otherwise from URL
const currentStatus = ref(props.status || 'pending_approval');
const activeTab = ref(currentStatus.value);

// Handle tab changes
const handleTabChange = (tabValue) => {
  activeTab.value = tabValue;
  router.get(route('canvas.approval', { status: tabValue }), {
    preserveState: true,
    replace: true
  });
};

// Filter canvases based on status
const filteredCanvases = computed(() => {
  return props.canvases.filter(canvas => {
    if (!activeTab.value) return true;
    return canvas.status === activeTab.value;
  });
});

const statusIcons = {
  pending_approval: UserRoundCheck,
  approved: CheckCircle,
  rejected: XCircle,
  poCreated: CheckCircle,
};

const statusVariants = {
  pending_approval: 'bg-purple-100 text-purple-800 hover:bg-purple-200',
  approved: 'bg-green-100 text-green-800 hover:bg-green-200',
  submitted: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
  poCreated: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
};

// Dialog state management
const showDialog = ref(false);
const selectedCanvas = ref(null);

const downloadFile = async (canvas) => {
  try {
    // For approved canvases, download the selected file
    if (canvas.status === 'approved' && canvas.selected_files?.length > 0) {
      const selectedFile = canvas.selected_files[0].file;
      if (!selectedFile) {
        throw new Error('No approved file selected');
      }
      
      const link = document.createElement('a');
      link.href = route('canvas.download.file', { 
        canvas: canvas.id, 
        file: selectedFile.id 
      });
      link.setAttribute('download', selectedFile.original_filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } else {
      // For other statuses, download all files as zip
      const link = document.createElement('a');
      link.href = route('canvas.download.all', canvas.id);
      link.setAttribute('download', `${canvas.title || 'canvas'}_files.zip`);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  } catch (error) {
    console.error('Download failed:', error);
    alert('Download failed. Please try again.');
  }
};

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
];
</script>

<template>
  <Head title="My Canvases" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <Tabs :model-value="activeTab" @update:model-value="handleTabChange" class="w-full">
        <div class="flex items-center justify-between pb-2">
          <PageHeader 
            title="Canvas" 
            subtitle="Monitoring submitted canvas requests"
          />

          <TabsList class="flex gap-2">
            <TabsTrigger value="submitted" class="px-3 py-1.5 text-sm leading-none">
              Under Review
            </TabsTrigger>
            <TabsTrigger value="approved" class="px-3 py-1.5 text-sm leading-none">
              Approved
            </TabsTrigger>
            <TabsTrigger value="poCreated" class="px-3 py-1.5 text-sm leading-none">
              PO Created
            </TabsTrigger>
          </TabsList>
        </div>

        <TabsContent v-for="tab in ['pending_approval', 'approved', 'poCreated', 'submitted']" 
                    :key="tab" 
                    :value="tab">
          <CanvasTable
            :canvases="filteredCanvases.filter(c => c.status === tab)"
            :status-icons="statusIcons"
            :status-variants="statusVariants"
            @show="showCanvas"
            @download="downloadFile"
          />
        </TabsContent>
      </Tabs>
    </div>

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