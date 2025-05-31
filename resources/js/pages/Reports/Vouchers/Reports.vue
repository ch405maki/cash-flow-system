<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ReportsTable from '@/components/reports/ReportsTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Reports',
    href: '/reports/vouchers',
  },
];

const searchQuery = ref("");
const timeFilter = ref<string | null>(null);
const dateRangeLabel = ref<string | null>(null);

// Date filter options
const dateFilterOptions = [
  { value: 'week', label: 'Last Week' },
  { value: 'month', label: 'Last Month' },
  { value: 'year', label: 'Last Year' },
  { value: null, label: 'All Time' }
];

// Calculate date ranges and update label
const updateDateRange = (period: string | null) => {
  const now = new Date();
  
  if (!period) {
    dateRangeLabel.value = null;
    return { start: null, end: null };
  }
  
  if (period === 'week') {
    const start = new Date(now);
    start.setDate(now.getDate() - 7);
    dateRangeLabel.value = 'Last Week';
    return { start, end: now };
  }
  
  if (period === 'month') {
    const start = new Date(now);
    start.setMonth(now.getMonth() - 1);
    dateRangeLabel.value = 'Last Month';
    return { start, end: now };
  }
  
  if (period === 'year') {
    const start = new Date(now);
    start.setFullYear(now.getFullYear() - 1);
    dateRangeLabel.value = 'Last Year';
    return { start, end: now };
  }
  
  dateRangeLabel.value = null;
  return { start: null, end: null };
};

// Watch for timeFilter changes
watch(timeFilter, (newValue) => {
  updateDateRange(newValue);
});

// Filtered Vouchers
const filteredVouchers = computed(() => {
  let result = [...props.vouchers];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(
      (voucher: any) =>
        voucher.voucher_no?.toLowerCase().includes(query) ||
        voucher.type?.toLowerCase().includes(query) ||
        voucher.purpose?.toLowerCase().includes(query) ||
        voucher.check_amount?.toString().toLowerCase().includes(query) ||
        voucher.payee?.toLowerCase().includes(query) ||
        voucher.check_payable_to?.toLowerCase().includes(query) ||
        voucher.status?.toLowerCase().includes(query)
    );
  }
  
  // Apply time filter
  if (timeFilter.value) {
    const { start, end } = updateDateRange(timeFilter.value);
    if (start && end) {
      result = result.filter(voucher => {
        const voucherDate = voucher.created_at || voucher.voucher_date;
        if (!voucherDate) return false;
        const date = new Date(voucherDate);
        return date >= start && date <= end;
      });
    }
  }
  
  return result;
});

function goToPage(url: string) {
  if (url) {
    router.visit(url)
  }
}

function clearFilters() {
  searchQuery.value = "";
  timeFilter.value = null;
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
          <!-- Date Filter Select -->
          <Select v-model="timeFilter">
            <SelectTrigger class="w-[180px]">
              <SelectValue placeholder="Filter by date" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="option in dateFilterOptions" 
                :key="option.value" 
                :value="option.value"
              >
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>

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
      
      <!-- Filter Status Indicator -->
      <div v-if="dateRangeLabel" class="flex items-center gap-2 text-sm">
        <span class="text-muted-foreground">Showing:</span>
        <span class="font-medium">{{ dateRangeLabel }}'s reports</span>
        <Button 
          variant="ghost" 
          size="sm" 
          @click="timeFilter = null"
          class="h-6 px-2 text-muted-foreground"
        >
          Clear
        </Button>
      </div>

      <ReportsTable :vouchers="filteredVouchers" />

      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
          Showing {{ filteredVouchers.length }} of {{ vouchers.length }} records
          <span v-if="searchQuery || dateRangeLabel">(filtered)</span>
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