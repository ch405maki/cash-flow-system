<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Vouchers', href: '/vouchers' },
  { title: 'Report Preview', href: '#' },
];

const props = defineProps({
  voucher: {
    type: Object,
    required: true,
  },
  pdfHtml: {
    type: String,
    required: true,
  },
  authUser: {
    type: Object,
    required: true,
  },
  pdfUrl: {
    type: String,
    required: true,
  },
});

const isLoading = ref(false);

const handlePrint = () => {
  window.print();
};

const handleDownload = async () => {
  isLoading.value = true;
  try {
    // Option 1: Direct download
    window.location.href = props.pdfUrl;
    
    // Option 2: Using Inertia (if you prefer)
    // await router.get(props.pdfUrl);
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <Head :title="`Voucher Report - ${voucher.voucher_no}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Header with action buttons -->
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Voucher Report Preview</h1>
        <div class="flex gap-2">
          <Button
            variant="default"
            size="sm"
            @click="handlePrint"
            class="no-print"
          >
            Print Report
          </Button>
          <Button
            variant="default"
            size="sm"
            @click="handleDownload"
            :disabled="isLoading"
            class="no-print"
          >
            <span v-if="isLoading">Generating PDF...</span>
            <span v-else>Download PDF</span>
          </Button>
          <Link :href="route('vouchers.index')">
            <Button
              variant="outline"
              size="sm"
              class="no-print"
            >
              Back to Vouchers
            </Button>
          </Link>
        </div>
      </div>

      <!-- PDF Template Content -->
      <div 
        class="bg-white rounded-lg shadow-sm overflow-hidden print:p-0 print:shadow-none"
        v-html="pdfHtml"
      />

      <!-- Print button at bottom (hidden on screen, visible in print) -->
      <div class="print-only text-center mt-4">
        <p class="text-sm text-gray-500">
          Generated on {{ new Date().toLocaleDateString() }}
        </p>
      </div>
    </div>
  </AppLayout>
</template>

