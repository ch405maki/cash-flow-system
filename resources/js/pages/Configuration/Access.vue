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

interface Access { id: number; program_name: string; access_level: string }

const props = defineProps<{ accesses: Access[] }>();

const toast = useToast();
const items = ref<Access[]>(props.accesses);
const createForm = ref({ program_name: '', access_level: '' });
const editForm = ref<Partial<Access>>({});
const showCreate = ref(false);
const showEdit = ref(false);

const columns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'program_name', label: 'Program Name' },
    { key: 'access_level', label: 'Access Level' },
];

const { paginatedItems, currentPage, totalPages, hasPrevPage, hasNextPage, prevPage, nextPage } = usePagination(items, 20);

async function handleCreate() {
    const data = await configurationService.create<Access>('access', createForm.value);
    if (data) {
        items.value.push(data);
        createForm.value = { program_name: '', access_level: '' };
        showCreate.value = false;
        toast.success('Access level created successfully');
    }
}

function openEdit(item: Access) {
    editForm.value = { ...item };
    showEdit.value = true;
}

async function handleUpdate() {
    if (!editForm.value?.id) return;
    const data = await configurationService.update<Access>('access', editForm.value.id, editForm.value);
    if (data) {
        const idx = items.value.findIndex(a => a.id === data.id);
        if (idx !== -1) items.value[idx] = data;
        editForm.value = {};
        showEdit.value = false;
        toast.success('Access level updated successfully');
    }
}

async function handleDelete(id: number) {
    await configurationService.delete('access', id);
    items.value = items.value.filter(a => a.id !== id);
    toast.success('Access level deleted successfully');
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Access Management', href: '#' },
];
</script>

<template>
    <Head title="User Access" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">User Access Management</h2>
                <CrudDialog
                    title="Add New Access Level"
                    description="Create a new access level for your organization."
                    :fields="[
                        { key: 'program_name', label: 'Program Name', type: 'text', placeholder: 'Enter program name' },
                        { key: 'access_level', label: 'Access Level', type: 'text', placeholder: 'Enter access level' },
                    ]"
                    v-model="createForm"
                    v-model:open="showCreate"
                    trigger-label="Add New Access Level"
                    @save="handleCreate"
                />
            </div>

            <CrudDialog
                title="Edit Access Level"
                :fields="[
                    { key: 'program_name', label: 'Program Name', type: 'text' },
                    { key: 'access_level', label: 'Access Level', type: 'text' },
                ]"
                v-model="editForm"
                v-model:open="showEdit"
                :is-editing="true"
                save-label="Save Changes"
                @save="handleUpdate"
            />

            <DataTable :columns="columns" :data="paginatedItems">
                <template #actions="{ item }">
                    <div class="flex gap-2 justify-end">
                        <Button variant="outline" size="sm" @click="openEdit(item)">Edit</Button>
                        <ConfirmDeleteDialog item-name="this access level" @confirm="handleDelete(item.id)" />
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