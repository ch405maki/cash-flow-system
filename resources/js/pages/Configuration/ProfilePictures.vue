<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Camera, Upload, Loader2, Trash2, X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { useToast } from "vue-toastification";
import axios from "axios";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const toast = useToast();

const breadcrumbs = [
  { title: "Dashboard", href: "/dashboard" },
  { title: "Users Management", href: "/users" }
];

defineProps({
  profilePictures: Array,
})

const form = useForm({
  file: null,
})

const previewUrl = ref('')
const fileInput = ref(null)
const showDeleteDialog = ref(false)
const pictureToDelete = ref<number|null>(null)
const isDeleting = ref(false)

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.file = file
    previewUrl.value = URL.createObjectURL(file)
  }
}

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleUpload = () => {
  if (!form.file) {
    toast.error('Please select an image to upload', {
      timeout: 3000
    });
    return
  }

  form.post(route('profile-pictures.store'), {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      previewUrl.value = ''
      toast.success('Your profile picture has been uploaded', {
        timeout: 3000
      });
    },
    onError: () => {
      toast.error(form.errors.file || 'An error occurred during upload', {
        timeout: 3000
      });
    }
  })
}

const confirmDelete = (id: number) => {
  pictureToDelete.value = id
  showDeleteDialog.value = true
}

const deletePicture = async () => {
  if (!pictureToDelete.value) return
  
  isDeleting.value = true
  try {
    await axios.delete(`/api/profile-pictures/${pictureToDelete.value}`)
    toast.success('Profile picture deleted successfully')
    window.location.reload() // Refresh to update the list
  } catch (error) {
    toast.error('Failed to delete profile picture')
    console.error('Delete error:', error)
  } finally {
    isDeleting.value = false
    showDeleteDialog.value = false
  }
}
</script>


<template>
  <Head title="Users Management" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-lg font-medium text-gray-900 dark:text-white">Profile Pictures</h1>
      </div>
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">Upload New Picture</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-6">
            <!-- Upload Area -->
            <div 
              @click="triggerFileInput"
              @dragover.prevent
              @drop.prevent="handleFileChange($event)"
              class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center cursor-pointer hover:border-primary transition-colors"
            >
              <input
                ref="fileInput"
                type="file"
                @change="handleFileChange"
                accept="image/*"
                class="hidden"
              />
              
              <div class="flex flex-col items-center justify-center space-y-3">
                <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                  <Upload class="h-6 w-6 text-gray-500 dark:text-gray-300" />
                </div>
                
                <div v-if="!previewUrl">
                  <p class="font-medium text-gray-700 dark:text-gray-200">
                    Drag & drop an image here or click to browse
                  </p>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    JPG, PNG, or GIF (Max 2MB)
                  </p>
                </div>
                
                <div v-else class="mt-4">
                  <img 
                    :src="previewUrl" 
                    alt="Preview" 
                    class="mx-auto h-32 w-32 rounded-full object-cover border border-gray-200 dark:border-gray-600"
                  />
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                    {{ form.file?.name }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Upload Button -->
            <div class="flex justify-end gap-3">
              <Button 
                v-if="previewUrl"
                variant="outline"
                @click="previewUrl = ''; form.file = null"
              >
                <Trash2 class="h-4 w-4 mr-2" />
                Cancel
              </Button>
              
              <Button 
                type="button"
                @click="handleUpload"
                :disabled="form.processing || !form.file"
              >
                <Loader2 v-if="form.processing" class="h-4 w-4 mr-2 animate-spin" />
                <Upload v-else class="h-4 w-4 mr-2" />
                {{ form.processing ? 'Uploading...' : 'Upload Picture' }}
              </Button>
            </div>

            <!-- Error Message -->
            <div v-if="form.errors.file" class="text-sm text-red-500">
              {{ form.errors.file }}
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Gallery Section -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg">Your Uploaded Pictures</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="profilePictures.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            <div
              v-for="pic in profilePictures"
              :key="pic.id"
              class="group relative transition-shadow"
            >
              <div class="relative h-32 w-32 mx-auto">
                <img
                  :src="`/storage/${pic.file_path}`"
                  alt="Profile"
                  class="h-full w-full rounded-full object-cover border border-gray-200 dark:border-gray-600"
                />
                <button
                  @click.stop="confirmDelete(pic.id)"
                  class="absolute -top-1 -right-1 p-1 bg-red-500 rounded-full opacity-0 group-hover:opacity-100 transition-opacity z-10"
                >
                  <X class="h-4 w-4 text-white" />
                </button>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3 rounded-full">
                  <p class="text-white text-sm truncate">{{ pic.file_name }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-12 text-gray-500 dark:text-gray-400">
            <Camera class="mx-auto h-8 w-8 mb-2" />
            <p>No profile pictures uploaded yet</p>
          </div>
        </CardContent>
      </Card>

      <!-- Delete Confirmation Dialog -->
      <Dialog v-model:open="showDeleteDialog">
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Delete Profile Picture</DialogTitle>
            <DialogDescription>
              Are you sure you want to delete this profile picture? This action cannot be undone.
            </DialogDescription>
          </DialogHeader>
          <DialogFooter>
            <Button variant="outline" @click="showDeleteDialog = false">Cancel</Button>
            <Button variant="destructive" @click="deletePicture" :disabled="isDeleting">
              <Loader2 v-if="isDeleting" class="h-4 w-4 mr-2 animate-spin" />
              <Trash2 v-else class="h-4 w-4 mr-2" />
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </AppLayout>
</template>