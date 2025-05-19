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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Badge } from '@/components/ui/badge';
import { PenIcon, PlusIcon, Trash2Icon } from 'lucide-vue-next';

interface Signatory {
    id: number;
    full_name: string;
    position: string;
    status: 'active' | 'inactive';
}

const props = defineProps<{
    signatories: Signatory[];
}>();

const toast = useToast();
const signatories = ref<Signatory[]>(props.signatories);
const newSignatory = ref({
    full_name: '',
    position: '',
    status: 'active'
});
const editingSignatory = ref<Partial<Signatory> | null>(null);
const isDialogOpen = ref(false);
const isEditDialogOpen = ref(false);

const createSignatory = async () => {
    try {
        const response = await axios.post('/api/configuration/signatories', newSignatory.value);
        signatories.value.push(response.data);
        newSignatory.value = { full_name: '', position: '', status: 'active' };
        isDialogOpen.value = false;
        toast.success('Signatory created successfully');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((messages: any) => {
                messages.forEach((message: string) => toast.error(message));
            });
        } else {
            toast.error('Failed to create signatory');
        }
    }
};

const updateSignatory = async () => {
    if (!editingSignatory.value?.id) return;
    
    try {
        const response = await axios.put(`/api/configuration/signatories/${editingSignatory.value.id}`, editingSignatory.value);
        const index = signatories.value.findIndex(s => s.id === editingSignatory.value?.id);
        if (index !== -1) {
            signatories.value[index] = response.data;
        }
        editingSignatory.value = null;
        isEditDialogOpen.value = false;
        toast.success('Signatory updated successfully');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((messages: any) => {
                messages.forEach((message: string) => toast.error(message));
            });
        } else {
            toast.error('Failed to update signatory');
        }
    }
};

const deleteSignatory = async (id: number) => {
    try {
        await axios.delete(`/api/configuration/signatories/${id}`);
        signatories.value = signatories.value.filter(s => s.id !== id);
        toast.success('Signatory deleted successfully');
    } catch (error) {
        toast.error('Failed to delete signatory');
    }
};

const openEditDialog = (signatory: Signatory) => {
    editingSignatory.value = { ...signatory };
    isEditDialogOpen.value = true;
};
</script>

<template>
    <AppLayout>
        <Head title="Signatories" />
        <ConfigurationLayout>
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Signatory Management</h2>
                    <Dialog v-model:open="isDialogOpen">
                        <DialogTrigger as-child>
                            <Button variant="default">
                                <PlusIcon class="mr-2 h-4 w-4" />
                                Add New Signatory
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Add New Signatory</DialogTitle>
                                <DialogDescription>
                                    Create a new signatory for your organization.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="full_name" class="text-right">Full Name</label>
                                    <Input 
                                        id="full_name" 
                                        v-model="newSignatory.full_name" 
                                        placeholder="Enter full name" 
                                        class="col-span-3" 
                                    />
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="position" class="text-right">Position</label>
                                    <Input 
                                        id="position" 
                                        v-model="newSignatory.position" 
                                        placeholder="Enter position" 
                                        class="col-span-3" 
                                    />
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="status" class="text-right">Status</label>
                                    <Select v-model="newSignatory.status">
                                        <SelectTrigger class="col-span-3">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" @click="createSignatory">Save</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Full Name</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="signatory in signatories" :key="signatory.id">
                                <TableCell>{{ signatory.id }}</TableCell>
                                <TableCell>{{ signatory.full_name }}</TableCell>
                                <TableCell>{{ signatory.position }}</TableCell>
                                <TableCell>
                                    <Badge 
                                        :variant="signatory.status === 'active' ? 'default' : 'secondary'"
                                        class="capitalize"
                                    >
                                        {{ signatory.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <Dialog v-model:open="isEditDialogOpen">
                                            <DialogTrigger as-child>
                                                <Button 
                                                    variant="outline" 
                                                    size="sm" 
                                                    @click="openEditDialog(signatory)"
                                                >
                                                    Edit
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle>Edit Signatory</DialogTitle>
                                                </DialogHeader>
                                                <div class="grid gap-4 py-4">
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-full_name" class="text-right">Full Name</label>
                                                        <Input 
                                                            id="edit-full_name" 
                                                            v-model="editingSignatory.full_name" 
                                                            class="col-span-3" 
                                                        />
                                                    </div>
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-position" class="text-right">Position</label>
                                                        <Input 
                                                            id="edit-position" 
                                                            v-model="editingSignatory.position" 
                                                            class="col-span-3" 
                                                        />
                                                    </div>
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-status" class="text-right">Status</label>
                                                        <Select v-model="editingSignatory.status">
                                                            <SelectTrigger class="col-span-3">
                                                                <SelectValue placeholder="Select status" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem value="active">Active</SelectItem>
                                                                <SelectItem value="inactive">Inactive</SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <Button type="submit" @click="updateSignatory">Save Changes</Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                        <Button 
                                            variant="destructive" 
                                            size="sm" 
                                            @click="deleteSignatory(signatory.id)"
                                        >
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