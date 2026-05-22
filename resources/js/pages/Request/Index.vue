<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import RequestTable from '@/components/requests/RequestTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ref, watch, computed } from 'vue'
import PageHeader from '@/components/PageHeader.vue';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Filter, PlusCircle } from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
];

const props = defineProps({
  requests: { type: Array, default: () => [] },
  departments: { type: Array, default: () => [] },
  authUser: { type: Object, required: true },
  pageType: { type: String, default: 'index' },
});

const pageConfig = computed(() => {
  const configs = {
    index: {
      title: 'Requests',
      subtitle: props.authUser.role === 'staff'
        ? 'Create and track your requests'
        : 'Review and manage department requests',
      showFilters: true,
      showCreateBtn: props.authUser.role === 'staff',
    },
    rejected: {
      title: 'Rejected Requests',
      subtitle: 'View requests that were not approved',
      showFilters: false,
      showCreateBtn: false,
    },
    released: {
      title: 'Released Requests',
      subtitle: 'Active requests currently being processed',
      showFilters: false,
      showCreateBtn: false,
    },
    'to-receive': {
      title: 'Requests On Process',
      subtitle: 'Active requests currently being processed',
      showFilters: true,
      showCreateBtn: false,
    },
  };
  return configs[props.pageType] || configs.index;
});

const departmentFilter = ref('all');
const statusFilter = ref('all');

function goToCreate() {
  router.visit(`/request/create`)
}

const showDepartmentFilter = computed(() =>
  pageConfig.value.showFilters &&
  props.authUser.role !== 'staff' &&
  props.authUser.role !== 'department_head'
);

const displayRequests = ref(props.requests);

watch([departmentFilter, statusFilter], () => {
  displayRequests.value = props.requests.filter(request => {
    const departmentMatch = departmentFilter.value === 'all' ||
      request.department?.department_name === departmentFilter.value;
    const statusMatch = statusFilter.value === 'all' ||
      request.status === statusFilter.value;
    return departmentMatch && statusMatch;
  });
});

const itemsToShow = computed(() =>
  pageConfig.value.showFilters ? displayRequests.value : props.requests
);
</script>

<template>
  <Head :title="pageConfig.title" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <PageHeader
          :title="pageConfig.title"
          :subtitle="pageConfig.subtitle"
        />
        <div v-if="pageConfig.showFilters" class="flex gap-4 items-center">
          <div class="w-[180px]" v-if="showDepartmentFilter">
            <Select v-model="departmentFilter">
              <SelectTrigger class="h-8">
                <div class="flex items-center gap-2">
                  <Filter class="h-4 w-4" />
                  <SelectValue placeholder="Department" />
                </div>
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Departments</SelectLabel>
                  <SelectItem value="all">All Departments</SelectItem>
                  <SelectItem v-for="department in departments" :key="department.id" :value="department.department_name">
                    {{ department.department_name }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <div class="w-[180px]">
            <Select v-model="statusFilter">
              <SelectTrigger class="h-8">
                <div class="flex items-center gap-2">
                  <Filter class="h-4 w-4" />
                  <SelectValue placeholder="Status" />
                </div>
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectLabel>Status</SelectLabel>
                  <SelectItem value="all">All Statuses</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="approved">Approved</SelectItem>
                  <SelectItem value="rejected">Rejected</SelectItem>
                  <SelectItem value="request to order">Request to Order</SelectItem>
                  <SelectItem value="partially_released">Partially Released</SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <Button v-if="pageConfig.showCreateBtn" variant="default" size="sm" @click="goToCreate()" class="h-8">
            <PlusCircle class="h-4 w-4" />
            Create Request
          </Button>
        </div>
      </div>

      <RequestTable :requests="itemsToShow" />
    </div>
  </AppLayout>
</template>