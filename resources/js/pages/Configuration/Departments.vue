<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useToast } from 'vue-toastification';
import DataTable from '@/components/common/DataTable.vue';
import CrudDialog from '@/components/common/CrudDialog.vue';
import ConfirmDeleteDialog from '@/components/common/ConfirmDeleteDialog.vue';
import { usePagination } from '@/composables/usePagination';
import { configurationService } from '@/services/configurationService';
import type { Column } from '@/components/common/DataTable.vue';

interface Department {
    id: number;
    department_name: string;
    department_description: string | null;
}

const props = defineProps<{ departments: Department[] }>();

const toast = useToast();
const items = ref<Department[]>(props.departments);
const createForm = ref({ department_name: '', department_description: '' });
const editForm = ref<Partial<Department>>({});
const showCreate = ref(false);
const showEdit = ref(false);

const columns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'department_name', label: 'Name' },
    { key: 'department_description', label: 'Description' },
];

const { paginatedItems, currentPage, totalPages, hasPrevPage, hasNextPage, prevPage, nextPage } = usePagination(items, 20);

async function handleCreate() {
    const data = await configurationService.create<Department>('departments', createForm.value);
    if (data) {
        items.value.push(data);
        createForm.value = { department_name: '', department_description: '' };
        showCreate.value = false;
        toast.success('Department created successfully');
    }
}

function openEdit(item: Department) {
    editForm.value = { ...item };
    showEdit.value = true;
}

async function handleUpdate() {
    if (!editForm.value?.id) return;
    const data = await configurationService.update<Department>('departments', editForm.value.id, editForm.value);
    if (data) {
        const idx = items.value.findIndex(d => d.id === data.id);
        if (idx !== -1) items.value[idx] = data;
        editForm.value = {};
        showEdit.value = false;
        toast.success('Department updated successfully');
    }
}

async function handleDelete(id: number) {
    await configurationService.delete('departments', id);
    items.value = items.value.filter(d => d.id !== id);
    toast.success('Department deleted successfully');
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Department Management', href: '#' },
];
</script>

<template>
    <Head title="Departments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Department Management</h2>
                <CrudDialog
                    title="Add New Department"
                    description="Create a new department for your organization."
                    :fields="[
                        { key: 'department_name', label: 'Name', type: 'text', placeholder: 'Enter department name' },
                        { key: 'department_description', label: 'Description', type: 'textarea', placeholder: 'Enter department description (optional)' },
                    ]"
                    v-model="createForm"
                    v-model:open="showCreate"
                    trigger-label="Add New Department"
                    @save="handleCreate"
                />
            </div>

            <CrudDialog
                title="Edit Department"
                :fields="[
                    { key: 'department_name', label: 'Name', type: 'text' },
                    { key: 'department_description', label: 'Description', type: 'textarea' },
                ]"
                v-model="editForm"
                v-model:open="showEdit"
                :is-editing="true"
                save-label="Save Changes"
                @save="handleUpdate"
            />

            <DataTable :columns="columns" :data="paginatedItems">
                <template #cell-department_description="{ value }">
                    {{ value || '-' }}
                </template>
                <template #actions="{ item }">
                    <div class="flex gap-2 justify-end">
                        <Button variant="outline" size="sm" @click="openEdit(item)">Edit</Button>
                        <ConfirmDeleteDialog item-name="this department" @confirm="handleDelete(item.id)" />
                    </div>
                </template>
                <template #pagination>
                    <div class="flex items-center justify-between mt-4">
                        <p class="text-sm text-muted-foreground">Page {{ currentPage }} of {{ totalPages }}</p>
                        <div class="flex gap-2">
                            <Button variant="outline" size="sm" :disabled="!hasPrevPage" @click="prevPage">Previous</Button>
                            <Button variant="outline" size="sm" :disabled="!hasNextPage" @click="nextPage">Next</Button>
                        </div>
                    </div>
                </template>
            </DataTable>
        </div>
    </AppLayout>
</template>