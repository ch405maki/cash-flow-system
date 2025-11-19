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
import { UserRoundPen, Eye, EyeOff } from "lucide-vue-next";
import axios from "axios";
import { useToast } from "vue-toastification";
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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

// Password Data
const passwordData = ref({
  password: '',
  password_confirmation: '',
});

// Password visibility
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

// Active tab
const activeTab = ref('profile');

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
  
  // Reset password fields
  passwordData.value = {
    password: '',
    password_confirmation: '',
  };
  
  // Reset to profile tab
  activeTab.value = 'profile';
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

// Update Password
const updatePassword = async () => {
  if (passwordData.value.password !== passwordData.value.password_confirmation) {
    toast.error("Passwords do not match!");
    return;
  }
  
  if (passwordData.value.password.length < 8) {
    toast.error("Password must be at least 8 characters long!");
    return;
  }

  try {
    const response = await axios.put(`/api/users/${userData.value.id}/password`, {
      password: passwordData.value.password,
      password_confirmation: passwordData.value.password_confirmation,
    });
    toast.success("Password updated successfully!");
    passwordData.value = {
      password: '',
      password_confirmation: '',
    };
  } catch (error) {
    toast.error(error.response?.data?.message || "Failed to update password.");
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
        <SheetDescription>Modify user details and password.</SheetDescription>
      </SheetHeader>

      <!-- Tabs -->
      <div class="mt-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'profile'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'profile'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Profile
          </button>
          <button
            @click="activeTab = 'password'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'password'
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
            ]"
          >
            Password
          </button>
        </nav>
      </div>

      <!-- Profile Tab -->
      <div v-if="activeTab === 'profile'" class="mt-6">
        <form @submit.prevent="updateUser" class="space-y-4">
          <!-- Name Fields -->
          <div class="space-y-2">
            <Label for="username">Username</Label>
            <Input
              v-model="userData.username"
              id="username"
              type="text"
              required
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
              <Label for="first_name">First Name</Label>
              <Input
                v-model="userData.first_name"
                id="first_name"
                type="text"
                required
              />
            </div>
            <div class="space-y-2">
              <Label for="middle_name">Middle Name</Label>
              <Input
                v-model="userData.middle_name"
                id="middle_name"
                type="text"
              />
            </div>
            <div class="space-y-2">
              <Label for="last_name">Last Name</Label>
              <Input
                v-model="userData.last_name"
                id="last_name"
                type="text"
                required
              />
            </div>
          </div>

          <!-- Email Field -->
          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input
              v-model="userData.email"
              id="email"
              type="email"
              required
            />
          </div>

          <!-- Department Field -->
          <div class="space-y-2">
            <Label for="department">Department</Label>
            <select
              v-model="userData.department_id"
              id="department"
              class="w-full p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200"
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
          </div>

          <!-- Access Level Field -->
          <div class="space-y-2">
            <Label for="access">Access Level</Label>
            <select
              v-model="userData.access_id"
              id="access"
              class="w-full p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200"
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
          </div>

          <!-- Role Field -->
          <div class="space-y-2">
            <Label for="role">Role</Label>
            <select
              v-model="userData.role"
              id="role"
              class="w-full p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200"
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
          </div>

          <!-- Boolean Checkboxes -->
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <Checkbox id="petty-cash" v-model:checked="userData.is_petty_cash" />
              <Label for="petty-cash" class="text-sm font-medium">Reimbursement</Label>
            </div>
            <div class="flex items-center space-x-2">
              <Checkbox id="cash-advance" v-model:checked="userData.is_cash_advance" />
              <Label for="cash-advance" class="text-sm font-medium">Cash Advance / Liquidation</Label>
            </div>
          </div>

          <!-- Status Field -->
          <div class="space-y-2">
            <Label for="status">Status</Label>
            <select
              v-model="userData.status"
              id="status"
              class="w-full p-2 border rounded-md bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

          <!-- Save Changes Button -->
          <Button type="submit" class="w-full">Save Profile Changes</Button>
        </form>
      </div>

      <!-- Password Tab -->
      <div v-if="activeTab === 'password'" class="mt-6">
        <form @submit.prevent="updatePassword" class="space-y-4">
          <div class="space-y-2">
            <Label for="password">New Password</Label>
            <div class="relative">
              <Input
                v-model="passwordData.password"
                id="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter new password"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
              >
                <Eye v-if="!showPassword" class="h-4 w-4" />
                <EyeOff v-else class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div class="space-y-2">
            <Label for="password_confirmation">Confirm New Password</Label>
            <div class="relative">
              <Input
                v-model="passwordData.password_confirmation"
                id="password_confirmation"
                :type="showPasswordConfirmation ? 'text' : 'password'"
                placeholder="Confirm new password"
                required
              />
              <button
                type="button"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
              >
                <Eye v-if="!showPasswordConfirmation" class="h-4 w-4" />
                <EyeOff v-else class="h-4 w-4" />
              </button>
            </div>
          </div>

          <div class="text-sm text-gray-500 dark:text-gray-400">
            <p>• Password must be at least 8 characters long</p>
            <p>• Make sure both passwords match</p>
          </div>

          <Button type="submit" class="w-full">Update Password</Button>
        </form>
      </div>
    </SheetContent>
  </Sheet>
</template>