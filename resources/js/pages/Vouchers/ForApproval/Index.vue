<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import VoucherTable from '@/components/vouchers/VoucherTable.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button'
import { PlusCircle, Filter, Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
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
  }, {
    title: 'Vouchers',
    href: '/voucher-approval',
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

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((voucher: any) => {
      // Get dates (fall back to each other if not available)
      const date = new Date(voucher.created_at || voucher.voucher_date);

      // Format dates for searching
      const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      }).toLowerCase();

      // Format month name separately for "June" searches
      const monthName = date.toLocaleDateString('en-US', { month: 'long' }).toLowerCase();

      // Check if query matches any date part
      const matchesDate =
        formattedDate.includes(query) ||
        monthName.includes(query) ||
        date.getFullYear().toString().includes(query) ||
        date.getDate().toString().includes(query) ||
        (date.getMonth() + 1).toString().includes(query); // Supports "6" for June

      return (
        // Existing search fields
        voucher.voucher_no?.toLowerCase().includes(query) ||
        voucher.type?.toLowerCase().includes(query) ||
        voucher.purpose?.toLowerCase().includes(query) ||
        voucher.check_amount?.toString().toLowerCase().includes(query) ||
        voucher.payee?.toLowerCase().includes(query) ||
        voucher.check_payable_to?.toLowerCase().includes(query) ||
        voucher.status?.toLowerCase().includes(query) ||
        // Date search
        matchesDate
      );
    });
  }

  // Keep your existing time filter logic
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

function goToCreate() {
  router.visit(`/vouchers/create`)
}

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

  <Head title="Vouchers" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-3.5 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Vouchers</h1>
        <div class="flex gap-2">
          <!-- Date Filter Select -->
          <Select v-model="timeFilter">
            <SelectTrigger class="w-[180px] h-8">
              <div class="flex items-center gap-2">
                <Filter class="h-4 w-4" />
                <SelectValue placeholder="Filter by date" />
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="option in dateFilterOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </SelectItem>
            </SelectContent>
          </Select>

          <div class="flex items-center gap-2">
            <Input type="search" placeholder="Search vouchers..." class="w-[200px] lg:w-[300px] h-8"
              v-model="searchQuery" />
          </div>
          
          <!-- Only show if the user is NOT an executive_director and staff -->
          <Button v-if="authUser.role !== 'executive_director' && authUser.access_id !== '3' " variant="default" size="sm" @click="goToCreate()">
            <PlusCircle class="h-4 w-4" />
            Create New Voucher
          </Button>

        </div>
      </div>

      <!-- Filter Status Indicator -->
      <div v-if="dateRangeLabel" class="flex items-center gap-2 text-sm">
        <span class="text-muted-foreground">Showing:</span>
        <span class="font-medium">{{ dateRangeLabel }}'s vouchers</span>
        <Button variant="ghost" size="sm" @click="timeFilter = null" class="h-6 px-2 text-muted-foreground">
          Clear
        </Button>
      </div>

      <VoucherTable :vouchers="filteredVouchers" :authUser="authUser" />

      <div class="mt-4 flex items-center justify-between">
        <div class="flex-1 text-sm text-muted-foreground">
          Showing {{ filteredVouchers.length }} of {{ vouchers.length }} vouchers
        </div>
        <div class="flex justify-between items-center gap-2">
          <Button variant="outline" size="sm" :disabled="!vouchers.prev_page_url"
            @click="goToPage(vouchers.prev_page_url)">
            Previous
          </Button>
          <Button variant="outline" size="sm" :disabled="!vouchers.next_page_url"
            @click="goToPage(vouchers.next_page_url)">
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>