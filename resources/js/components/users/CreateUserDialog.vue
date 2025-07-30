<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button @click="openDialog">
        <UserRoundPlus class="w-4 h-4 mr-2" />
        Create User
      </Button>
    </DialogTrigger>
    <DialogContent class="max-h-[90vh] overflow-y-auto ">
      <DialogHeader>
        <DialogTitle>Create New User</DialogTitle>
        <DialogDescription>
          Fill in the details to add a new user.
        </DialogDescription>
      </DialogHeader>
      
      <form @submit.prevent="createUser" class="space-y-4">
        <div class="grid gap-4 py-4">
          <div class="grid gap-4">
            <div class="grid gap-1">
              <label class="text-sm font-medium">Profile Picture</label>
              <div class="grid grid-cols-4">
                <div
                  v-for="pic in profilePictures"
                  :key="pic.id"
                  class="h-16 w-16 flex items-center justify-center border rounded-full cursor-pointer overflow-hidden"
                  :class="{ 'ring-2 ring-blue-500': formData.profile_picture_id === pic.id }"
                  @click="formData.profile_picture_id = pic.id"
                >
                  <img
                    :src="`/storage/${pic.file_path}`"
                    alt="Profile Pic"
                    class="h-full w-full object-cover"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="space-y-2">
            <Label for="username">Username</Label>
            <Input id="username" v-model="formData.username" required />
          </div>

          <div class="grid grid-cols-1 gap-4">
            <div class="space-y-2">
              <Label for="firstName">First Name</Label>
              <Input id="firstName" v-model="formData.first_name" required />
            </div>
            <div class="space-y-2">
              <Label for="middleName">Middle Name</Label>
              <Input id="middleName" v-model="formData.middle_name" />
            </div>
            <div class="space-y-2">
              <Label for="lastName">Last Name</Label>
              <Input id="lastName" v-model="formData.last_name" required />
            </div>
          </div>

          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input id="email" type="email" v-model="formData.email" required />
          </div>

          <div class="space-y-2">
            <Label for="password">Password</Label>
            <Input id="password" type="password" v-model="formData.password" required />
          </div>

          <div class="space-y-2">
            <Label for="role">Role</Label>
            <Select v-model="formData.role" required>
              <SelectTrigger>
                <SelectValue placeholder="Select role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="admin">Administrator</SelectItem>
                <SelectItem value="executive_director">Executive Director</SelectItem>
                <SelectItem value="accounting">Accounting</SelectItem>
                <SelectItem value="department_head">Department Head</SelectItem>
                <SelectItem value="property_custodian">Property Custodian</SelectItem>
                <SelectItem value="purchasing">Purchasing</SelectItem>
                <SelectItem value="staff">Staff</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label for="status">Status</Label>
            <Select v-model="formData.status" required>
              <SelectTrigger>
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label for="department">Department</Label>
            <Select v-model="formData.department_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Select department" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="department in props.departments" 
                  :key="department.id" 
                  :value="department.id"
                >
                  {{ department.department_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label for="access">Access Level</Label>
            <Select v-model="formData.access_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Select access level" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem 
                  v-for="access in props.accessLevels" 
                  :key="access.id" 
                  :value="access.id"
                >
                  {{ access.access_level }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="closeDialog">
            Cancel
          </Button>
          <Button type="submit" :disabled="loading">
            <span v-if="loading" class="flex items-center">
              <Loader2 class="mr-2 h-4 w-4 animate-spin" />
              Creating...
            </span>
            <span v-else>Create User</span>
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { Loader2, UserRoundPlus } from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface Department {
  id: number
  department_name: string
}

interface AccessLevel {
  id: number
  access_level: string
}

const props = defineProps<{
  departments: Department[];
  accessLevels: AccessLevel[];
  profilePictures: { id: number; file_path: string; file_name: string }[];
}>()

const emit = defineEmits(['user-created'])

const toast = useToast()
const isOpen = ref(false)
const loading = ref(false)

const formData = ref({
  username: '',
  first_name: '',
  middle_name: '',
  last_name: '',
  email: '',
  password: '',
  role: 'staff',
  status: 'active',
  department_id: '',
  access_id: '',
  profile_picture_id: null as number | null,
})

const openDialog = () => (isOpen.value = true)
const closeDialog = () => {
  isOpen.value = false
  formData.value = {
    username: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    password: '',
    role: 'staff',
    status: 'active',
    department_id: '',
    access_id: '',
    profile_picture_id: null,
  }
}

const createUser = async () => {
  loading.value = true
  try {
    await axios.post("/api/users", {
      ...formData.value,
      profile_picture_id: formData.value.profile_picture_id
        ? Number(formData.value.profile_picture_id)
        : null,
    });
    toast.success('User created successfully!')
    closeDialog()
    emit('user-created')
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach(err => {
        toast.error(err[0])
      })
    } else {
      toast.error('Failed to create user')
    }
  } finally {
    loading.value = false
  }
}
</script>
