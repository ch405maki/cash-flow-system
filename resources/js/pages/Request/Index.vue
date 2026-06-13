<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import RequestTable from '@/components/request/RequestTable.vue';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { requestService } from '@/services/requestService';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Filter, PlusCircle, RefreshCw } from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';
import { useToast } from 'vue-toastification';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Request', href: '/request' },
];

const props = defineProps({
    pageType: { type: String, default: 'index' },
});

const toast = useToast();
const authUser = usePage().props.auth.user;

const requests = ref<any[]>([]);
const departments = ref<any[]>([]);
const loading = ref(true);

const pageConfig = computed(() => {
    const configs = {
        index: {
            title: 'Requests',
            subtitle: authUser?.role === 'staff' ? 'Create and track your requests' : 'Review and manage department requests',
            showFilters: true,
            showCreateBtn: authUser?.role === 'staff',
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
        'on-process-orders': {
            title: 'On Process Orders',
            subtitle: 'Orders currently being processed',
            showFilters: false,
            showCreateBtn: false,
        },
    };
    return configs[props.pageType] || configs.index;
});

const departmentFilter = ref('all');
const statusFilter = ref('all');

function goToCreate() {
    router.visit(`/request/create`);
}

const showDepartmentFilter = computed(() => pageConfig.value.showFilters && authUser?.role !== 'staff' && authUser?.role !== 'department_head');

const displayRequests = ref<any[]>([]);

watch([departmentFilter, statusFilter], () => {
    displayRequests.value = requests.value.filter((request) => {
        const departmentMatch = departmentFilter.value === 'all' || request.department?.department_name === departmentFilter.value;
        const statusMatch = statusFilter.value === 'all' || request.status === statusFilter.value;
        return departmentMatch && statusMatch;
    });
});

const itemsToShow = computed(() => (pageConfig.value.showFilters ? displayRequests.value : requests.value));

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await requestService.index(props.pageType);
        requests.value = response.data.requests;
        departments.value = response.data.departments;
        displayRequests.value = response.data.requests;
    } catch (error) {
        console.error('Error fetching requests:', error);
        toast.error('Failed to load requests');
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>

<template>
    <Head :title="pageConfig.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <PageHeader :title="pageConfig.title" :subtitle="pageConfig.subtitle" />
                <div v-if="pageConfig.showFilters" class="flex items-center gap-4">
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

                    <Button variant="outline" size="sm" class="h-8" @click="fetchData" :disabled="loading">
                        <RefreshCw :class="['h-4 w-4', loading && 'animate-spin']" />
                    </Button>

                    <Button v-if="pageConfig.showCreateBtn" variant="default" size="sm" @click="goToCreate()" class="h-8">
                        <PlusCircle class="h-4 w-4" />
                        Create Request
                    </Button>
                </div>
            </div>

            <div v-if="loading" class="overflow-hidden rounded-lg border shadow-sm">
                <div class="p-4">
                    <Skeleton class="mb-4 h-8 w-full" />
                    <Skeleton class="mb-4 h-8 w-full" />
                    <Skeleton class="mb-4 h-8 w-full" />
                    <Skeleton class="h-8 w-full" />
                </div>
            </div>

            <RequestTable v-else :requests="itemsToShow" />
        </div>
    </AppLayout>
</template>
