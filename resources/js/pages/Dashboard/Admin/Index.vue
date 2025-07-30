<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Users, CheckCircle, XCircle, Plus, Search, Filter } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import BaseChart from '@/components/dashboard/admin/BaseChart.vue';
import CreateUserDialog from "@/components/users/CreateUserDialog.vue";
import {
  Avatar,
  AvatarImage,
  AvatarFallback
} from '@/components/ui/avatar'

const props = defineProps<{
    userRole: string;
    username: string;
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
        users: Array<{
            id: number;
            username: string;
            first_name: string;
            last_name: string;
            email: string;
            status: string;
            role: string;
            department: {
                department_name: string;
            };
            access: {
                program_name: string;
                access_level: string;
            };
        }>;
    };
}>();

const neutralViolet = {
  light: 'rgba(210, 199, 242, 0.8)',
  medium: 'rgba(168, 148, 224, 0.9)',
  dark: 'rgba(109, 88, 167, 1)',
  accent: 'rgba(183, 148, 244, 1)',
  border: 'rgba(139, 92, 246, 0.5)'
};

const departmentChartData = computed(() => {
  return {
    labels: props.userStats.departments.map(dept => dept.department_name),
    datasets: [{
      label: 'Users by Department',
      data: props.userStats.departments.map(dept => dept.users_count),
      backgroundColor: [
        neutralViolet.light,
        neutralViolet.medium,
        neutralViolet.dark,
        neutralViolet.accent,
        neutralViolet.light,
        neutralViolet.medium
      ],
      borderColor: neutralViolet.border,
      borderWidth: 1.5,
      borderRadius: 0, // Changed from 6 to 0
      hoverBackgroundColor: neutralViolet.accent,
      hoverBorderColor: neutralViolet.dark,
      hoverBorderWidth: 2
    }]
  };
});

const departmentChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.9)',
      titleColor: '#e2e8f0',
      bodyColor: '#f1f5f9',
      borderColor: neutralViolet.border,
      borderWidth: 1,
      padding: 12,
      cornerRadius: 0, // Changed from 8 to 0
      displayColors: false,
      callbacks: {
        label: function(context) {
          return ` ${context.parsed.y} users`;
        },
        title: function(context) {
          return context[0].label;
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(226, 232, 240, 0.1)',
        drawTicks: false
      },
      ticks: {
        color: 'rgba(148, 163, 184, 0.8)',
        precision: 0
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        color: 'rgba(148, 163, 184, 0.8)'
      }
    }
  },
  animation: {
    duration: 1000,
    easing: 'easeOutQuart'
  },
  elements: {
    bar: {
      backgroundColor: 'rgba(139, 92, 246, 0.7)',
      borderColor: 'rgba(124, 58, 237, 1)',
      borderWidth: 1.5,
      borderRadius: 0,
      hoverBackgroundColor: 'rgba(167, 139, 250, 0.9)',
      hoverBorderColor: 'rgba(139, 92, 246, 1)',
      hoverBorderWidth: 2
    }
  }
};

const searchQuery = ref('');
const selectedDepartment = ref('all');
const selectedStatus = ref('all');

const filteredUsers = computed(() => {
    return props.userStats.users.filter(user => {
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Admin',
        href: '/dashboard/admin',
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between">
                <h1 class="text-lg font-medium">
                    Admin Dashboard
                    <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
                </h1>
                <CreateUserDialog
                    :departments="userStats.departments"
                    :access-levels="userStats.accessLevels"
                    @user-created="() => {
                    }"
                    />
            </div>
            
            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Users</CardTitle>
                        <div class="p-2 rounded-lg border">
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
                        <div class="p-2 rounded-lg border">
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
                        <div class="p-2 rounded-lg border">
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
            <div class="space-y-4">
                <Card>
                    <CardHeader>
                    <CardTitle class="text-sm font-medium">Users by Department</CardTitle>
                    </CardHeader>
                    <CardContent>
                    <div class="h-[300px]">
                        <BaseChart
                        type="bar"
                        :data="departmentChartData"
                        :options="departmentChartOptions"
                        />
                    </div>
                    </CardContent>
                </Card>

                <div class="border rounded-lg">
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
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="user in filteredUsers" :key="user.id">
                            <TableCell class="font-medium flex items-center gap-2">
                              <Avatar>
                                <AvatarImage
                                  v-if="user.profile_picture"
                                  :src="`/storage/${user.profile_picture.file_path}`"
                                  :alt="user.name"
                                />
                                <AvatarFallback>
                                  {{ user.first_name?.charAt(0).toUpperCase() || 'N' }}{{ user.last_name?.charAt(0).toUpperCase() || 'N' }}
                                </AvatarFallback>
                              </Avatar>
                              <div class="capitalize">
                                {{ user.first_name || 'N' }} {{ user.last_name || 'N' }} <br>
                                <span class="text-xs">
                                  {{ user.username }}
                                </span>
                              </div>
                            </TableCell>
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
                        </TableRow>
                    </TableBody>
                </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>