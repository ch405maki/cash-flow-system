<script setup lang="ts">
  import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
  import { Button } from '@/components/ui/button';
  import { Input } from '@/components/ui/input';
  import AppLayout from '@/layouts/AppLayout.vue';
  import ConfigurationLayout from '@/layouts/configuration/Layout.vue';
  import { type BreadcrumbItem } from '@/types';
  import { ref, computed } from "vue";
  import CreateUserDialog from "@/components/users/CreateUserDialog.vue";
  import UsersTable from "@/components/users/UsersTable.vue";
  import { Upload } from "lucide-vue-next";
  import axios from "axios";
  import type { AxiosError } from "axios";
  import { useToast } from "vue-toastification";
  
  // Define the User type
  interface User {
      id: number;
      username: string;
      first_name: string;
      middle_name: string;
      last_name: string;
      email: string;
      role: string;
      status: string;
      department_id: number;
      access_id: number;
    }

    interface Department {
      id: number;
      department_name: string;
    }

    interface AccessLevel {
      id: number;
      access_level: string;
    }
    
    // Define props
    const props = defineProps<{ 
      users: User[],
      departments: Department[],
      accessLevels: AccessLevel[]
    }>();
  
  const breadcrumbs = [{ title: "Users Management", href: "/users" }];

  const fileInput = ref<HTMLInputElement | null>(null);
  const toast = useToast();
  const loading = ref(false); // Loading state
  const searchQuery = ref(""); // Search query
  
  // Filtered Users
  const filteredUsers = computed(() => {
    if (!searchQuery.value) {
      return props.users; // Return all users if no search query
    }
  
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(
      (user) =>
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.role.toLowerCase().includes(query) ||
        user.status.toLowerCase().includes(query)
    );
  });
  
  // Trigger file input
  const triggerFileInput = () => {
    fileInput.value?.click();
  };
  
  // Handle file upload
  const handleFileUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
  
    if (file) {
      const formData = new FormData();
      formData.append("file", file);
  
      try {
        const response = await axios.post("/api/upload-users", formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });
  
        toast.success(response.data.message);
        setTimeout(() => location.reload(), 2000); // Reload to reflect changes
      } catch (err: unknown) {
        const error = err as AxiosError;
        if (error.response && error.response.data) {
          const status: number = error.response.status;
          const errorMessage: string = (error.response.data as any).message || "An error occurred.";
  
          if (status === 422) {
            toast.error(`Validation Error: ${errorMessage}`);
          } else if (status === 500) {
            toast.error("Server Error: Please check your file data.");
          } else {
            toast.error(errorMessage);
          }
        } else {
          toast.error("Network error. Please try again.");
        }
      } finally {
        loading.value = false;
      }
    }
  };
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
      <Head title="Users Management" />
            <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <!-- Search and Buttons -->
                <div class="flex items-center justify-between gap-4">
                <!-- Search Input -->
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users..."
                    class="w-full max-w-xs h-9"
                />
        
                <!-- Buttons -->
                <div class="flex items-center gap-4">
                    <!-- Upload Excel Button -->
                    <input
                    type="file"
                    ref="fileInput"
                    accept=".xlsx, .xls"
                    class="hidden"
                    @change="handleFileUpload"
                    />
                    <Button
                    @click="triggerFileInput"
                    :disabled="loading"
                    class="bg-green-600 hover:bg-green-700 text-white"
                    >
                    <Upload class="w-4 h-4 mr-2" />
                    <span v-if="loading">Uploading...</span>
                    <span v-else>Upload Excel</span>
                    </Button>
        
                    <!-- Create User Button -->
                    <CreateUserDialog 
                      :departments="departments" 
                      :access-levels="accessLevels" 
                    />
                </div>
                </div>
        
                <!-- Users Table -->
                <div class="relative flex-1 rounded-lg border">
                <UsersTable :users="filteredUsers" />
                </div>
            </div>
    </AppLayout>
</template>
