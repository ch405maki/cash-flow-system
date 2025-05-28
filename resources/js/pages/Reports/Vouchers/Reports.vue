<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import ReportsTable from '@/components/reports/ReportsTable.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next'
import { ref } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  }, {
    title: 'Reports',
    href: '/reports/vouchers',
  },
];

const searchQuery = ref('')

function handleSearch() {
  router.get('/reports/vouchers',
    { search: searchQuery.value },
    {
      preserveState: true,
      replace: true,
    }
  )
}

const props = defineProps({
  vouchers: {
    type: Object,
    required: true,
  },
  authUser: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

function goToPage(url: string | null) {
  if (url) {
    router.get(url, { search: searchQuery.value }, {
      preserveState: true,
      replace: true,
    })
  }
}
</script>

<template>

  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Reports</h1>
        <div class="relative">
          <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
          <Input type="search" placeholder="Search Voucher Number"
            class="w-full appearance-none bg-background pl-8 shadow-none md:w-64" v-model="searchQuery"
            @input="handleSearch" />
        </div>
      </div>

      <ReportsTable :vouchers="vouchers.data || vouchers" />

      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
          Showing page {{ vouchers.current_page }} of {{ vouchers.last_page }}
        </div>
        <div class="flex gap-2">
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