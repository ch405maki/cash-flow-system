<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useToast } from 'vue-toastification';
import DataTable from '@/components/common/DataTable.vue';
import CrudDialog from '@/components/common/CrudDialog.vue';
import ConfirmDeleteDialog from '@/components/common/ConfirmDeleteDialog.vue';
import { usePagination } from '@/composables/usePagination';
import { configurationService } from '@/services/configurationService';
import type { Column } from '@/components/common/DataTable.vue';

interface Signatory { id: number; full_name: string; position: string; status: string }

const props = defineProps<{ signatories: Signatory[] }>();

const toast = useToast();
const items = ref<Signatory[]>(props.signatories);
const createForm = ref({ full_name: '', position: '', status: 'active' });
const editForm = ref<Partial<Signatory>>({});
const showCreate = ref(false);
const showEdit = ref(false);

const columns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'full_name', label: 'Full Name' },
    { key: 'position', label: 'Position' },
    { key: 'status', label: 'Status' },
];

const { paginatedItems, currentPage, totalPages, hasPrevPage, hasNextPage, prevPage, nextPage } = usePagination(items, 20);

async function handleCreate() {
    const data = await configurationService.create<Signatory>('signatories', createForm.value);
    if (data) {
        items.value.push(data);
        createForm.value = { full_name: '', position: '', status: 'active' };
        showCreate.value = false;
        toast.success('Signatory created successfully');
    }
}

function openEdit(item: Signatory) {
    editForm.value = { ...item };
    showEdit.value = true;
}

async function handleUpdate() {
    if (!editForm.value?.id) return;
    const data = await configurationService.update<Signatory>('signatories', editForm.value.id, editForm.value);
    if (data) {
        const idx = items.value.findIndex(s => s.id === data.id);
        if (idx !== -1) items.value[idx] = data;
        editForm.value = {};
        showEdit.value = false;
    }
}

async function handleDelete(id: number) {
    await configurationService.delete('signatories', id);
    items.value = items.value.filter(s => s.id !== id);
    toast.success('Signatory deleted successfully');
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Signatory Management', href: '#' },
];
</script>

<template>
    <Head title="Signatories" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Signatory Management</h2>
                <CrudDialog
                    title="Add New Signatory"
                    description="Create a new signatory for your organization."
                    :fields="[
                        { key: 'full_name', label: 'Full Name', type: 'text', placeholder: 'Enter full name' },
                        { key: 'position', label: 'Position', type: 'text', placeholder: 'Enter position' },
                        { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
                    ]"
                    v-model="createForm"
                    v-model:open="showCreate"
                    trigger-label="Add New Signatory"
                    @save="handleCreate"
                />
            </div>

            <CrudDialog
                title="Edit Signatory"
                :fields="[
                    { key: 'full_name', label: 'Full Name', type: 'text' },
                    { key: 'position', label: 'Position', type: 'text' },
                    { key: 'status', label: 'Status', type: 'select', options: [{ value: 'active', label: 'Active' }, { value: 'inactive', label: 'Inactive' }] },
                ]"
                v-model="editForm"
                v-model:open="showEdit"
                :is-editing="true"
                save-label="Save Changes"
                @save="handleUpdate"
            />

            <DataTable :columns="columns" :data="paginatedItems">
                <template #cell-status="{ value }">
                    <Badge :variant="value === 'active' ? 'default' : 'secondary'" class="capitalize">{{ value }}</Badge>
                </template>
                <template #actions="{ item }">
                    <div class="flex gap-2 justify-end">
                        <Button variant="outline" size="sm" @click="openEdit(item)">Edit</Button>
                        <ConfirmDeleteDialog item-name="this signatory" @confirm="handleDelete(item.id)" />
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