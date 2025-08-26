<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref } from 'vue'
import { Loader2, Hash, Calendar } from 'lucide-vue-next'

const props = defineProps({
  voucherId: {
    type: Number,
    required: true
  },
  currentCheckNo: {
    type: String,
    default: ''
  },
  currentCheckDate: {
    type: String,
    default: ''
  },
  authUser: {
    type: Object,
    required: true
  }
})

const toast = useToast()
const isOpen = ref(false)
const isLoading = ref(false)

const form = ref({
  check_no: props.currentCheckNo,
  check_date: props.currentCheckDate
})

const emit = defineEmits(['saved'])

const addCheckDetails = async () => {
  if (!form.value.check_no || !form.value.check_date) {
    toast.error('Please complete all check fields');
    return;
  }

  isLoading.value = true;

  try {
    const response = await axios.patch(`/api/vouchers/${props.voucherId}/check`, {
      check_no: form.value.check_no,
      check_date: form.value.check_date,
      user_id: props.authUser.id
    });

    emit('saved', response.data.data);
    isOpen.value = false;

  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach((msg) => toast.error(msg[0]));
    } else {
      toast.error(error.response?.data?.message || 'Failed to add check details');
    }
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button variant="default" class="gap-2" size="sm">
        <Hash class="h-4 w-4" />
        Add Check Number
      </Button>
    </DialogTrigger>
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Add Check Details</DialogTitle>
      </DialogHeader>
      
      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="check_no">Check Number</Label>
          <div class="relative">
            <Input
              id="check_no"
              v-model="form.check_no"
              class="w-full pl-9"
              placeholder="Enter check number"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <Hash class="h-4 w-4 text-muted-foreground" />
            </div>
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="check_date">Check Date</Label>
          <div class="relative">
            <Input
              id="check_date"
              type="date"
              v-model="form.check_date"
              class="w-full pl-9"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <Calendar class="h-4 w-4 text-muted-foreground" />
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <Button variant="outline" @click="isOpen = false">Cancel</Button>
        <Button @click="addCheckDetails" :disabled="isLoading">
          <Loader2 v-if="isLoading" class="h-4 w-4 mr-2 animate-spin" />
          <span>{{ isLoading ? 'Saving...' : 'Save' }}</span>
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>