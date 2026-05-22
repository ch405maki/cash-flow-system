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

interface Account { id: number; account_title: string }

const props = defineProps<{ accounts: Account[] }>();

const toast = useToast();
const items = ref<Account[]>(props.accounts);
const createForm = ref({ account_title: '' });
const editForm = ref<Partial<Account>>({});
const showCreate = ref(false);
const showEdit = ref(false);

const columns: Column[] = [
    { key: 'id', label: 'ID' },
    { key: 'account_title', label: 'Account Title' },
];

const { paginatedItems, currentPage, totalPages, hasPrevPage, hasNextPage, prevPage, nextPage } = usePagination(items, 20);

async function handleCreate() {
    const data = await configurationService.create<Account>('accounts', createForm.value);
    if (data) {
        items.value.push(data);
        createForm.value = { account_title: '' };
        showCreate.value = false;
        toast.success('Account created successfully');
    }
}

function openEdit(item: Account) {
    editForm.value = { ...item };
    showEdit.value = true;
}

async function handleUpdate() {
    if (!editForm.value?.id) return;
    const data = await configurationService.update<Account>('accounts', editForm.value.id, editForm.value);
    if (data) {
        const idx = items.value.findIndex(a => a.id === data.id);
        if (idx !== -1) items.value[idx] = data;
        editForm.value = {};
        showEdit.value = false;
        toast.success('Account updated successfully');
    }
}

async function handleDelete(id: number) {
    await configurationService.delete('accounts', id);
    items.value = items.value.filter(a => a.id !== id);
    toast.success('Account deleted successfully');
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Account Management', href: '#' },
];
</script>

<template>
    <Head title="Accounts" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Account Management</h2>
                <CrudDialog
                    title="Add New Account"
                    description="Create a new account title for your organization."
                    :fields="[{ key: 'account_title', label: 'Account Title', type: 'text', placeholder: 'Enter account title' }]"
                    v-model="createForm"
                    v-model:open="showCreate"
                    trigger-label="Add New Account"
                    @save="handleCreate"
                />
            </div>

            <CrudDialog
                title="Edit Account"
                :fields="[{ key: 'account_title', label: 'Account Title', type: 'text' }]"
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
                        <ConfirmDeleteDialog item-name="this account" @confirm="handleDelete(item.id)" />
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