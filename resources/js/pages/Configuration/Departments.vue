<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfigurationLayout from '@/layouts/configuration/Layout.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
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

interface Department {
    id: number;
    department_name: string;
    department_description: string | null;
}

const props = defineProps<{
    departments: Department[];
}>();

const toast = useToast();
const departments = ref<Department[]>(props.departments);
const newDepartment = ref({
    department_name: '',
    department_description: ''
});
const editingDepartment = ref<Partial<Department> | null>(null);
const isDialogOpen = ref(false);
const isEditDialogOpen = ref(false);

const createDepartment = async () => {
    try {
        const response = await axios.post('/api/configuration/departments', newDepartment.value);
        departments.value.push(response.data);
        newDepartment.value = { department_name: '', department_description: '' };
        isDialogOpen.value = false;
        toast.success('Department created successfully');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((messages: any) => {
                messages.forEach((message: string) => toast.error(message));
            });
        } else {
            toast.error('Failed to create department');
        }
    }
};

const updateDepartment = async () => {
    if (!editingDepartment.value?.id) return;
    
    try {
        const response = await axios.put(`/api/configuration/departments/${editingDepartment.value.id}`, editingDepartment.value);
        const index = departments.value.findIndex(d => d.id === editingDepartment.value?.id);
        if (index !== -1) {
            departments.value[index] = response.data;
        }
        editingDepartment.value = null;
        isEditDialogOpen.value = false;
        toast.success('Department updated successfully');
    } catch (error: any) {
        if (error.response?.data?.errors) {
            Object.values(error.response.data.errors).forEach((messages: any) => {
                messages.forEach((message: string) => toast.error(message));
            });
        } else {
            toast.error('Failed to update department');
        }
    }
};

const deleteDepartment = async (id: number) => {
    try {
        await axios.delete(`/api/configuration/departments/${id}`);
        departments.value = departments.value.filter(d => d.id !== id);
        toast.success('Department deleted successfully');
    } catch (error) {
        toast.error('Failed to delete department');
    }
};

const openEditDialog = (department: Department) => {
    editingDepartment.value = { ...department };
    isEditDialogOpen.value = true;
};
</script>

<template>
    <AppLayout>
        <Head title="Departments" />
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">Department Management</h2>
                    <Dialog v-model:open="isDialogOpen">
                        <DialogTrigger as-child>
                            <Button variant="default">
                                Add New Department
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Add New Department</DialogTitle>
                                <DialogDescription>
                                    Create a new department for your organization.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="grid gap-4 py-4">
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="department_name" class="text-right">Name</label>
                                    <Input 
                                        id="department_name" 
                                        v-model="newDepartment.department_name" 
                                        placeholder="Enter department name" 
                                        class="col-span-3" 
                                    />
                                </div>
                                <div class="grid grid-cols-4 items-center gap-4">
                                    <label for="department_description" class="text-right">Description</label>
                                    <Textarea 
                                        id="department_description" 
                                        v-model="newDepartment.department_description" 
                                        placeholder="Enter department description (optional)" 
                                        class="col-span-3" 
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button type="submit" @click="createDepartment">Save</Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>

                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="department in departments" :key="department.id">
                                <TableCell>{{ department.id }}</TableCell>
                                <TableCell>{{ department.department_name }}</TableCell>
                                <TableCell>{{ department.department_description || '-' }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex gap-2 justify-end">
                                        <Dialog v-model:open="isEditDialogOpen">
                                            <DialogTrigger as-child>
                                                <Button 
                                                    variant="outline" 
                                                    size="sm" 
                                                    @click="openEditDialog(department)"
                                                >
                                                    Edit
                                                </Button>
                                            </DialogTrigger>
                                            <DialogContent>
                                                <DialogHeader>
                                                    <DialogTitle>Edit Department</DialogTitle>
                                                </DialogHeader>
                                                <div class="grid gap-4 py-4">
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-department_name" class="text-right">Name</label>
                                                        <Input 
                                                            id="edit-department_name" 
                                                            v-model="editingDepartment.department_name" 
                                                            class="col-span-3" 
                                                        />
                                                    </div>
                                                    <div class="grid grid-cols-4 items-center gap-4">
                                                        <label for="edit-department_description" class="text-right">Description</label>
                                                        <Textarea 
                                                            id="edit-department_description" 
                                                            v-model="editingDepartment.department_description" 
                                                            class="col-span-3" 
                                                        />
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <Button type="submit" @click="updateDepartment">Save Changes</Button>
                                                </DialogFooter>
                                            </DialogContent>
                                        </Dialog>
                                        <Button 
                                            variant="destructive" 
                                            size="sm" 
                                            @click="deleteDepartment(department.id)"
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
    </AppLayout>
</template>