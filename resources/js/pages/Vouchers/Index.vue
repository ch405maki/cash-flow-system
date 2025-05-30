<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import VoucherTable from '@/components/vouchers/VoucherTable.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button'
import { Filter, PlusCircle, Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { ref, computed } from 'vue'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Vouchers',
    href: '/vouchers',
  },
];

const searchQuery = ref("");
const timeFilter = ref<string | null>(null); // 'week', 'month', 'year'

// Calculate date ranges
const getDateRange = (period: string | null) => {
  const now = new Date();
  if (!period) return { start: null, end: null };
  
  if (period === 'week') {
    const start = new Date(now);
    start.setDate(now.getDate() - 7);
    return { start, end: now };
  }
  
  if (period === 'month') {
    const start = new Date(now);
    start.setMonth(now.getMonth() - 1);
    return { start, end: now };
  }
  
  if (period === 'year') {
    const start = new Date(now);
    start.setFullYear(now.getFullYear() - 1);
    return { start, end: now };
  }
  
  return { start: null, end: null };
};

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
    const { start, end } = getDateRange(timeFilter.value);
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
  <Head title="Vouchers" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Vouchers</h1>
        <div class="flex gap-2">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="sm">
                <Filter class="h-4 w-4 mr-2"/>
                Filter by Date
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="w-48">
              <DropdownMenuItem @click="timeFilter = 'week'">
                Last Week
              </DropdownMenuItem>
              <DropdownMenuItem @click="timeFilter = 'month'">
                Last Month
              </DropdownMenuItem>
              <DropdownMenuItem @click="timeFilter = 'year'">
                Last Year
              </DropdownMenuItem>
              </DropdownMenuContent>
          </DropdownMenu>
          <div class="flex items-center gap-2">
            <Input
              type="search"
              placeholder="Search vouchers..."
              class="w-[200px] lg:w-[300px]"
              v-model="searchQuery"
            />
          </div>
          <Button variant="default" size="sm" @click="goToCreate()">
            <PlusCircle class="h-4 w-4"/>
            Create New Voucher
          </Button>
        </div>
      </div>
      
      <!-- Filter Status Indicator -->
      <div v-if="timeFilter" class="flex items-center gap-2 text-sm">
        <span class="text-muted-foreground">Showing:</span>
        <span class="font-medium capitalize">This {{ timeFilter }}'s vouchers</span>
        <Button 
          variant="ghost" 
          size="sm" 
          @click="timeFilter = null"
          class="h-6 px-2 text-muted-foreground"
        >
          Clear
        </Button>
      </div>

      <VoucherTable :vouchers="filteredVouchers" />

      <div class="mt-4 flex items-center justify-between">
        <div class="flex-1 text-sm text-muted-foreground">
          Showing {{ filteredVouchers.length }} of {{ vouchers.length }} vouchers
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