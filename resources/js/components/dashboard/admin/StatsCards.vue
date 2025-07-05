<script setup lang="ts">
import { ref, computed } from 'vue';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import {
    Users,
    CheckCircle,
    XCircle,
    Plus,
    Edit,
    Trash,
    Search,
    Filter,
} from 'lucide-vue-next';

const props = defineProps<{
    userStats: {
        totalUsers: number;
        activeUsers: number;
        inactiveUsers: number;
        departments: Array<{
            id: number;
            department_name: string;
            users_count: number;
        }>;
        accessLevels: Array<{
            id: number;
            program_name: string;
            access_level: string;
            users_count: number;
        }>;
    };
}>();

const searchQuery = ref('');
const selectedDepartment = ref('all');
const selectedStatus = ref('all');

// Sample user data - in a real app, this would come from the backend
const users = ref([
    {
        id: 1,
        username: 'jdoe',
        first_name: 'John',
        last_name: 'Doe',
        email: 'jdoe@example.com',
        department: { department_name: 'IT' },
        access: { program_name: 'Admin', access_level: 'full' },
        status: 'active',
        role: 'admin'
    },
    // Add more sample users as needed
]);

const filteredUsers = computed(() => {
    return users.value.filter(user => {
        const matchesSearch = 
            user.username.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.first_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.last_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            user.email.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        const matchesDepartment = 
            selectedDepartment.value === 'all' || 
            user.department.department_name === selectedDepartment.value;
            
        const matchesStatus = 
            selectedStatus.value === 'all' || 
            user.status === selectedStatus.value;
            
        return matchesSearch && matchesDepartment && matchesStatus;
    });
});

const openEditDialog = (user) => {
    // Implement edit functionality
    console.log('Edit user:', user);
};

const openDeleteDialog = (user) => {
    // Implement delete functionality
    console.log('Delete user:', user);
};
</script>

<template>
    <div class="space-y-6">
        <!-- User Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                    <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500 border">
                        <Users class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ userStats.totalUsers }}</div>
                    <p class="text-xs text-muted-foreground">All system users</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Active Users</CardTitle>
                    <div class="p-2 rounded-lg bg-green-500/10 text-green-500 border">
                        <CheckCircle class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ userStats.activeUsers }}</div>
                    <p class="text-xs text-muted-foreground">Currently active</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Inactive Users</CardTitle>
                    <div class="p-2 rounded-lg bg-red-500/10 text-red-500 border">
                        <XCircle class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ userStats.inactiveUsers }}</div>
                    <p class="text-xs text-muted-foreground">Disabled accounts</p>
                </CardContent>
            </Card>
        </div>

        <!-- Department and Access Level Stats -->
        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Users by Department</CardTitle>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-2">
                        <li v-for="dept in userStats.departments" :key="dept.id" class="flex justify-between">
                            <span>{{ dept.department_name }}</span>
                            <span class="font-medium">{{ dept.users_count }}</span>
                        </li>
                    </ul>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Users by Access Level</CardTitle>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-2">
                        <li v-for="access in userStats.accessLevels" :key="access.id" class="flex justify-between">
                            <span>{{ access.program_name }} ({{ access.access_level }})</span>
                            <span class="font-medium">{{ access.users_count }}</span>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <!-- User Management Table -->
        <Card>
            <CardHeader>
                <div class="flex justify-between items-center">
                    <CardTitle>User Management</CardTitle>
                    <Button>
                        <Plus class="mr-2 h-4 w-4" />
                        Add User
                    </Button>
                </div>
                <CardDescription>
                    Manage all system users and their permissions
                </CardDescription>
            </CardHeader>
            <CardContent>
                <!-- Filters -->
                <div class="flex items-center gap-4 mb-4">
                    <div class="relative w-full max-w-md">
                        <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <input
                            v-model="searchQuery"
                            placeholder="Search users..."
                            class="pl-9 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        />
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <Filter class="h-4 w-4 text-muted-foreground" />
                        <select
                            v-model="selectedDepartment"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="all">All Departments</option>
                            <option 
                                v-for="dept in userStats.departments" 
                                :key="dept.id" 
                                :value="dept.department_name"
                            >
                                {{ dept.department_name }}
                            </option>
                        </select>
                        
                        <select
                            v-model="selectedStatus"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                        >
                            <option value="all">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <!-- Users Table -->
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Username</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Email</TableHead>
                            <TableHead>Department</TableHead>
                            <TableHead>Access Level</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead>Role</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in filteredUsers" :key="user.id">
                            <TableCell>{{ user.username }}</TableCell>
                            <TableCell>{{ user.first_name }} {{ user.last_name }}</TableCell>
                            <TableCell>{{ user.email }}</TableCell>
                            <TableCell>{{ user.department.department_name }}</TableCell>
                            <TableCell>{{ user.access.program_name }} ({{ user.access.access_level }})</TableCell>
                            <TableCell>
                                <span 
                                    :class="{
                                        'text-green-600': user.status === 'active',
                                        'text-red-600': user.status === 'inactive'
                                    }"
                                >
                                    {{ user.status }}
                                </span>
                            </TableCell>
                            <TableCell>{{ user.role }}</TableCell>
                            <TableCell>
                                <div class="flex gap-2">
                                    <Button variant="outline" size="sm" @click="openEditDialog(user)">
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                    <Button variant="destructive" size="sm" @click="openDeleteDialog(user)">
                                        <Trash class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </CardContent>
        </Card>
    </div>
</template>