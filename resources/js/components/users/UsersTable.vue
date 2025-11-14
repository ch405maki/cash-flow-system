<script setup lang="ts">
import { ref } from "vue";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import EditUserDialog from "@/components/users/EditUserDialog.vue";
import DeleteUserDialog from "@/components/users/DeleteUserDialog.vue";
import CustomSwitch from '../../components/ui/customswitch/CustomSwitch.vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Button } from '@/components/ui/button'
import {
  Avatar,
  AvatarImage,
  AvatarFallback
} from '@/components/ui/avatar'
import { KeyRound } from 'lucide-vue-next';


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
  }

// Define props
const props = defineProps<{ 
  users: User[];
  departments?: Array<{
    id: number;
    department_name: string;
  }>;
  accessLevels?: Array<{
    id: number;
    program_name: string;
    access_level: string;
  }>;
}>();

// Toast
const toast = useToast();

// Local copy of users for reactivity
const localUsers = ref<User[]>([...props.users]);

// Toggle User Status
const handleToggle = async (user: User, checked: boolean) => {
  try {
    // Determine the new status
    const newStatus = checked ? "active" : "inactive";

    console.log("Sending PATCH request to update status...");
    console.log("User ID:", user.id);
    console.log("New Status:", newStatus);

    // Send update request to the new endpoint
    const response = await axios.patch(`/api/users/${user.id}/status`, {
      status: newStatus,
    });

    console.log("API Response:", response.data);

    // Update the local state
    const userIndex = localUsers.value.findIndex(u => u.id === user.id);
    if (userIndex !== -1) {
      localUsers.value[userIndex].status = newStatus;
    }

    toast.success(`User status updated to ${newStatus}`);
  } catch (error) {
    console.error("Error updating user status:", error);
    toast.error("Failed to update user status.");
  }
};
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>User Name</TableHead>
        <TableHead>Email</TableHead>
        <TableHead>Department</TableHead>
        <TableHead>Role</TableHead>
        <TableHead>Status</TableHead>
        <TableHead class="text-right">Action</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-for="user in users" :key="user.id">
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
        <TableCell>{{ user.email }}</TableCell>
        <TableCell>{{ user.department?.department_name }}</TableCell>
        <TableCell>{{ user.role }}</TableCell>
        <TableCell>
          <CustomSwitch
            :checked="user.status === 'active'"
            @update:checked="(checked) => handleToggle(user, checked)"
          />
        </TableCell>
        <TableCell class="justify-end space-x-2 flex  items-center">
          <Button 
            variant="outline"
            size="icon">
            <KeyRound  />
          </Button>
          <!-- Edit User -->
          <EditUserDialog 
            :user="user" 
            :departments="departments"
            :accessLevels="accessLevels"
          />
          <!-- Delete User -->
          <DeleteUserDialog :user="user" />
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>