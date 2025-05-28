<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import VoucherTable from '@/components/vouchers/VoucherTable.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button'
import { PlusCircle, Search } from 'lucide-vue-next'
import { Input } from '@/components/ui/input'
import { ref } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  }, {
    title: 'Vouchers',
    href: '/vouchers',
  },
];

const searchQuery = ref('')

function goToCreate() {
  router.visit(`/vouchers/create`)
}

function handleSearch() {
  router.get('/vouchers',
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
</script>

<template>

  <Head title="Vouchers" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Vouchers</h1>
        <div class="flex gap-2 items-center">
            <div class="relative flex items-center">
              <Search class="absolute left-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                type="search"
                placeholder="Search vouchers..."
                class="w-full appearance-none bg-background pl-8 shadow-none md:w-64 h-9"
                v-model="searchQuery"
                @input="handleSearch"
              />
            </div>
            <Button variant="default" size="sm" @click="goToCreate()">
              <PlusCircle class="h-4 w-4"/>
              Create New Voucher
            </Button>
        </div>
      </div>

      <VoucherTable :vouchers="vouchers.data" />

      <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-muted-foreground">
          Showing page {{ vouchers.current_page }} of {{ vouchers.last_page }}
        </div>
        <div class="flex gap-2">
          <Button variant="outline" size="sm" :disabled="!vouchers.prev_page_url"
            @click="router.get(vouchers.prev_page_url)">
            Previous
          </Button>
          <Button variant="outline" size="sm" :disabled="!vouchers.next_page_url"
            @click="router.get(vouchers.next_page_url)">
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>