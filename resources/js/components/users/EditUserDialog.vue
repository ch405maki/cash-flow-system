<script setup lang="ts">
import { ref } from "vue";
import {
  Sheet,
  SheetTrigger,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetDescription,
} from "@/components/ui/sheet";
import { Button } from "@/components/ui/button";
import { UserRoundPen } from "lucide-vue-next";
import axios from "axios";
import { useToast } from "vue-toastification";
import { Checkbox } from '@/components/ui/checkbox';

// Props
const props = defineProps<{
  user: {
    id: number;
    username: string;
    first_name: string;
    middle_name: string;
    last_name: string;
    email: string;
    role: string;
    status: string;
    is_petty_cash: boolean;
    is_cash_advance: boolean;
    department_id?: number;
    access_id?: number;
    department?: {
      id: number;
      department_name: string;
    };
    access?: {
      id: number;
      program_name: string;
    };
  };
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

// User Data
const userData = ref({
  id: props.user.id,
  username: props.user.username,
  first_name: props.user.first_name,
  middle_name: props.user.middle_name,
  last_name: props.user.last_name,
  email: props.user.email,
  role: props.user.role,
  status: props.user.status,
  is_petty_cash: props.user.is_petty_cash,
  is_cash_advance: props.user.is_cash_advance,
  department_id: props.user.department_id || null,
  access_id: props.user.access_id || null,
});

// Open Dialog
const openDialog = () => {
  userData.value = {
    id: props.user.id,
    username: props.user.username,
    first_name: props.user.first_name,
    middle_name: props.user.middle_name,
    last_name: props.user.last_name,
    email: props.user.email,
    role: props.user.role,
    status: props.user.status,
    is_petty_cash: props.user.is_petty_cash,
    is_cash_advance: props.user.is_cash_advance,
    department_id: props.user.department_id || null,
    access_id: props.user.access_id || null,
  };
};

// Update User
const updateUser = async () => {
  try {
    const response = await axios.put(`/api/users/${userData.value.id}`, userData.value);
    toast.success("User updated successfully!");
    setTimeout(() => {
      location.reload();
    }, 1500);
  } catch (error) {
    toast.error(error.response?.data?.message || "Failed to update user.");
  }
};
</script>

<template>
  <Sheet>
    <!-- Sheet Trigger -->
    <SheetTrigger as-child>
      <Button
        variant="outline"
        size="icon"
        @click="openDialog"
      >
        <UserRoundPen />
      </Button>
    </SheetTrigger>

    <!-- Sheet Content -->
    <SheetContent class="text-gray-900 dark:text-gray-200 overflow-y-auto">
      <SheetHeader>
        <SheetTitle>Edit User</SheetTitle>
        <SheetDescription>Modify user details and save changes.</SheetDescription>
      </SheetHeader>

      <!-- Edit User Form -->
      <form @submit.prevent="updateUser" class="mt-6">
        <div class="space-y-4">
          <!-- Name Fields -->
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Username</span>
            <input
              v-model="userData.username"
              type="text"
              class="input dark:bg-gray-700 dark:text-white"
              required
            />
          </label>
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">First Name</span>
            <input
              v-model="userData.first_name"
              type="text"
              class="input dark:bg-gray-700 dark:text-white"
              required
            />
          </label>
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Middle Name</span>
            <input
              v-model="userData.middle_name"
              type="text"
              class="input dark:bg-gray-700 dark:text-white"
            />
          </label>
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Last Name</span>
            <input
              v-model="userData.last_name"
              type="text"
              class="input dark:bg-gray-700 dark:text-white"
              required
            />
          </label>

          <!-- Email Field -->
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Email</span>
            <input
              v-model="userData.email"
              type="email"
              class="input dark:bg-gray-700 dark:text-white"
              required
            />
          </label>

          <!-- Department Field -->
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Department</span>
            <select
              v-model="userData.department_id"
              class="input dark:bg-gray-700 dark:text-white"
            >
              <option :value="null">No Department</option>
              <option 
                v-for="department in departments" 
                :key="department.id" 
                :value="department.id"
              >
                {{ department.department_name }}
              </option>
            </select>
          </label>

          <!-- Access Level Field -->
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Access Level</span>
            <select
              v-model="userData.access_id"
              class="input dark:bg-gray-700 dark:text-white"
            >
              <option :value="null">No Access Level</option>
              <option 
                v-for="access in accessLevels" 
                :key="access.id" 
                :value="access.id"
              >
                {{ access.program_name }} ({{ access.access_level }})
              </option>
            </select>
          </label>

          <!-- Role Field -->
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Role</span>
            <select
              v-model="userData.role"
              class="input dark:bg-gray-700 dark:text-white"
            >
              <option value="admin">Admin</option>
              <option value="executive_director">Executive Director</option>
              <option value="department_head">Department Head</option>
              <option value="accounting">Accounting</option>
              <option value="audit">Audit</option>
              <option value="bursar">Bursar</option>
              <option value="property_custodian">Property Custodian</option>
              <option value="purchasing">Purchasing</option>
              <option value="staff">Staff</option>
            </select>
          </label>

          <!-- Boolean Checkboxes -->
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <Checkbox id="petty-cash" v-model:checked="userData.is_petty_cash" />
              <label for="petty-cash" class="text-sm font-medium">Reimbursement</label>
            </div>
            <div class="flex items-center space-x-2">
              <Checkbox id="cash-advance" v-model:checked="userData.is_cash_advance" />
              <label for="cash-advance" class="text-sm font-medium">Cash Advance / Liquidation</label>
            </div>
          </div>

          <!-- Status Field -->
          <label class="block">
            <span class="text-gray-700 dark:text-gray-300">Status</span>
            <select
              v-model="userData.status"
              class="input dark:bg-gray-700 dark:text-white"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </label>

          <!-- Save Changes Button -->
          <Button type="submit" class="w-full">Save Changes</Button>
        </div>
      </form>
    </SheetContent>
  </Sheet>
</template>

<style scoped>
.input {
  @apply w-full p-2 border rounded-md bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200;
}
</style>