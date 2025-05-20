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

interface Access {
    id: number;
    program_name: string;
    access_level: string;
}

const props = defineProps<{
    accesses: Access[];
}>();

const toast = useToast();
const accesses = ref<Access[]>(props.accesses);
const newAccess = ref({
    program_name: '',
    access_level: ''
});
const editingAccess = ref<Partial<Access> | null>(null);
const isDialogOpen = ref(false);
const editingAccessId = ref<number | null>(null);

const createAccess = async () => {
    try {
        const response = await axios.post('/api/configuration/access', newAccess.value);
        accesses.value.push(response.data);
        newAccess.value = { program_name: '', access_level: '' };
        isDialogOpen.value = false;
        toast.success('Access level created successfully');
    } catch (error) {
        toast.error('Failed to create access level');
    }
};

const updateAccess = async () => {
    if (!editingAccess.value?.id) return;

    try {
        const response = await axios.put(`/api/configuration/access/${editingAccess.value.id}`, editingAccess.value);
        const index = accesses.value.findIndex(a => a.id === editingAccess.value?.id);
        if (index !== -1) {
            accesses.value[index] = response.data;
        }
        editingAccess.value = null;
        editingAccessId.value = null; 
        toast.success('Access level updated successfully');
    } catch (error) {
        toast.error('Failed to update access level');
    }
};


const deleteAccess = async (id: number) => {
    try {
        await axios.delete(`/api/configuration/access/${id}`);
        accesses.value = accesses.value.filter(a => a.id !== id);
        toast.success('Access level deleted successfully');
    } catch (error) {
        toast.error('Failed to delete access level');
    }
};

const openEditDialog = (access: Access) => {
    editingAccess.value = { ...access };
    editingAccessId.value = access.id;
};

</script>

<template>
    <AppLayout>
        <Head title="User Access" />
        <ConfigurationLayout>
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Access Management</h2>
                    <Dialog v-model:open="isDialogOpen">
                        <DialogTrigger as-child>
                            <Button variant="default">Add New Access</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Add New Access Level</DialogTitle>
                                <DialogDescription>
                                    Create a new program access level for users.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="program_name" class="text-right">Program Name</label>
                                    <Input 
                                        id="program_name" 
                                        v-model="newAccess.program_name" 
                                        class="col-span-3" 
                                    />
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="access_level" class="text-right">Access Level</label>
                                    <Input 
                                        id="access_level" 
                                        v-model="newAccess.access_level" 
                                        class="col-span-3" 
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" @click="createAccess">Save</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Program Name</TableHead>
                                <TableHead>Access Level</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="access in accesses" :key="access.id">
                                <TableCell>{{ access.id }}</TableCell>
                                <TableCell>{{ access.program_name }}</TableCell>
                                <TableCell>{{ access.access_level }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <Dialog :open="editingAccessId === access.id" @update:open="val => { if (!val) editingAccessId = null }">
                                            <DialogTrigger as-child>
                                                <Button 
                                                    variant="outline" 
                                                    size="sm" 
                                                    @click="openEditDialog(access)"
                                                >
                                                    Edit
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent v-if="editingAccess">
                                                <DialogHeader>
                                                    <DialogTitle>Edit Access Level</DialogTitle>
                                                </DialogHeader>
                                                <div class="grid gap-4 py-4">
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-program_name" class="text-right">Program Name</label>
                                                        <Input 
                                                            id="edit-program_name" 
                                                            v-model="editingAccess.program_name" 
                                                            class="col-span-3" 
                                                        />
                                                    </div>
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-access_level" class="text-right">Access Level</label>
                                                        <Input 
                                                            id="edit-access_level" 
                                                            v-model="editingAccess.access_level" 
                                                            class="col-span-3" 
                                                        />
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <Button type="submit" @click="updateAccess">Save Changes</Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                        <Button 
                                            variant="destructive" 
                                            size="sm" 
                                            @click="deleteAccess(access.id)"
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