<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ReportsTable from '@/components/reports/ReportsTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Reports',
    href: '/reports/vouchers',
  },
];

const searchQuery = ref(""); // Search query
  
// Filtered Vouchers
const filteredVouchers = computed(() => {
  if (!searchQuery.value) {
    return props.vouchers; // Return all vouchers if no search query
  }

  const query = searchQuery.value.toLowerCase();
  return props.vouchers.filter(
    (voucher: any) =>
      voucher.created_at?.toLowerCase().includes(query) ||
      voucher.voucher_no?.toLowerCase().includes(query) ||
      voucher.type?.toLowerCase().includes(query) ||
      voucher.purpose?.toLowerCase().includes(query) ||
      voucher.check_amount?.toString().toLowerCase().includes(query) ||
      voucher.payee?.toLowerCase().includes(query) ||
      voucher.check_payable_to?.toLowerCase().includes(query) ||
      voucher.status?.toLowerCase().includes(query)
  );
});

function goToPage(url: string) {
  if (url) {
    router.visit(url)
  }
}

const props = defineProps({
  vouchers: {
    type: Array,
    default: () => [],
    required: true,
  },
  authUser: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Reports</h1>
        <div class="flex gap-2">
          <div class="relative">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              type="search"
              placeholder="Search reports..."
              class="pl-8 w-[200px] lg:w-[300px]"
              v-model="searchQuery"
            />
          </div>
          
        </div>
      </div>
      
      <ReportsTable :vouchers="filteredVouchers" />

      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
          Showing {{ filteredVouchers.length }} of {{ vouchers.length }} records
          <span v-if="searchQuery">(filtered)</span>
        </div>
        <div class="flex gap-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="!vouchers.prev_page_url"
            @click="goToPage(vouchers.prev_page_url)"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            :disabled="!vouchers.next_page_url"
            @click="goToPage(vouchers.next_page_url)"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>