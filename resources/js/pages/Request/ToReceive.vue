<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import RequestForm from '@/components/requests/RequestForm.vue';
import RequestTable from '@/components/requests/RequestTable.vue';
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { ref, watch } from 'vue'
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
  {
    title: 'Dashboard',
    href: '/dashboard',
  },{
    title: 'Request',
    href: '/request',
  },
];

// Filter state
const departmentFilter = ref('all');
const statusFilter = ref('all');

function goToCreate() {
  router.visit(`/request/create`)
}

const props = defineProps({
  requests: {
    type: Array,
    default: () => [],
  },
  departments: {
    type: Array,
    default: () => [],
  },
  authUser: {
    type: Object,
    required: true,
  },
});

// Filtered requests
const filteredRequests = ref(props.requests);

// Watch for filter changes
watch([departmentFilter, statusFilter], () => {
  filteredRequests.value = props.requests.filter(request => {
    const departmentMatch = departmentFilter.value === 'all' || 
                        request.department?.department_name === departmentFilter.value;
    const statusMatch = statusFilter.value === 'all' || 
                        request.status === statusFilter.value;
    return departmentMatch && statusMatch;
  });
});
</script>

<template>
  <Head title="Create Request" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">
          Requests On Process
          <p class="text-sm font-medium text-muted-foreground capitalize"></p>
        </h1>
        <div class="flex gap-4 items-center">
          <!-- Department Filter -->
          <div class="w-[180px]" v-if="authUser.role !== 'staff' && authUser.role !== 'department_head'">
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
                  <SelectItem value="all">
                    All Departments
                  </SelectItem>
                  <SelectItem 
                    v-for="department in departments" 
                    :key="department.id" 
                    :value="department.department_name"
                  >
                    {{ department.department_name }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <!-- Status Filter -->
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
                  <SelectItem value="all">
                    All Statuses
                  </SelectItem>
                  <SelectItem value="pending">
                    Pending
                  </SelectItem>
                  <SelectItem value="approved">
                    Approved
                  </SelectItem>
                  <SelectItem value="rejected">
                    Rejected
                  </SelectItem>
                  <SelectItem value="request to order">
                    Request to Order
                  </SelectItem>
                  <SelectItem value="partially_released">
                    Partially Released
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <RequestTable :requests="filteredRequests" />
    </div>
  </AppLayout>
</template>