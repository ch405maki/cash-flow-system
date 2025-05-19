<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigurationLayout from '@/layouts/configuration/Layout.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { PlusIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next';

interface Account {
    id: number;
    account_title: string;
}

const props = defineProps<{
    accounts: Account[];
}>();

const toast = useToast();
const accounts = ref<Account[]>(props.accounts);
const newAccount = ref({
    account_title: ''
});
const editingAccount = ref<Partial<Account> | null>(null);
const isDialogOpen = ref(false);
const isEditDialogOpen = ref(false);

const createAccount = async () => {
    try {
        const response = await axios.post('/api/configuration/accounts', newAccount.value);
        accounts.value.push(response.data);
        newAccount.value = { account_title: '' };
        isDialogOpen.value = false;
        toast.success('Account created successfully');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((messages: any) => {
                messages.forEach((message: string) => toast.error(message));
            });
        } else {
            toast.error('Failed to create account');
        }
    }
};

const updateAccount = async () => {
    if (!editingAccount.value?.id) return;
    
    try {
        const response = await axios.put(`/api/configuration/accounts/${editingAccount.value.id}`, editingAccount.value);
        const index = accounts.value.findIndex(a => a.id === editingAccount.value?.id);
        if (index !== -1) {
            accounts.value[index] = response.data;
        }
        editingAccount.value = null;
        isEditDialogOpen.value = false;
        toast.success('Account updated successfully');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((messages: any) => {
                messages.forEach((message: string) => toast.error(message));
            });
        } else {
            toast.error('Failed to update account');
        }
    }
};

const deleteAccount = async (id: number) => {
    try {
        await axios.delete(`/api/configuration/accounts/${id}`);
        accounts.value = accounts.value.filter(a => a.id !== id);
        toast.success('Account deleted successfully');
    } catch (error) {
        toast.error('Failed to delete account');
    }
};

const openEditDialog = (account: Account) => {
    editingAccount.value = { ...account };
    isEditDialogOpen.value = true;
};
</script>

<template>
    <AppLayout>
        <Head title="Accounts" />
        <ConfigurationLayout>
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Account Management</h2>
                    <Dialog v-model:open="isDialogOpen">
                        <DialogTrigger as-child>
                            <Button variant="default">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Add New Account
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Add New Account</DialogTitle>
                                <DialogDescription>
                                    Create a new account for your organization.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="account_title" class="text-right">Account Title</label>
                                    <Input 
                                        id="account_title" 
                                        v-model="newAccount.account_title" 
                                        placeholder="Enter account title" 
                                        class="col-span-3" 
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" @click="createAccount">Save</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Account Title</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="account in accounts" :key="account.id">
                                <TableCell>{{ account.id }}</TableCell>
                                <TableCell>{{ account.account_title }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <Dialog v-model:open="isEditDialogOpen">
                                            <DialogTrigger as-child>
                                                <Button 
                                                    variant="outline" 
                                                    size="sm" 
                                                    @click="openEditDialog(account)"
                                                >
                                                    <PencilIcon class="mr-2 h-3 w-3" />
                                                    Edit
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle>Edit Account</DialogTitle>
                                                </DialogHeader>
                                                <div class="grid gap-4 py-4">
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-account_title" class="text-right">Account Title</label>
                                                        <Input 
                                                            id="edit-account_title" 
                                                            v-model="editingAccount.account_title" 
                                                            class="col-span-3" 
                                                        />
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <Button type="submit" @click="updateAccount">Save Changes</Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                        <Button 
                                            variant="destructive" 
                                            size="sm" 
                                            @click="deleteAccount(account.id)"
                                        >
                                            <Trash2Icon class="mr-2 h-3 w-3" />
                                            Delete
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </ConfigurationLayout>
    </AppLayout>
</template>