<template>
  <Dialog>
    <DialogTrigger as-child>
      <slot name="trigger"></slot>
    </DialogTrigger>
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Password Confirmation</DialogTitle>
        <DialogDescription>
          Please enter your password and (optional) comment for this action.
        </DialogDescription>
      </DialogHeader>

      <!-- Password Input -->
      <div>
        <Label for="password">Password</Label>
        <Input
          id="password"
          v-model="password"
          type="password"
          class="col-span-3"
          @keyup.enter="verify"
        />
      </div>

      <!-- Comment Input -->
      <div class="mt-3">
        <Label for="comment">Comment</Label>
        <Textarea 
          id="comment"
          v-model="comment"
          placeholder="Add remarks or reason (optional)"
          class="w-full p-2 border rounded-md text-sm"
          rows="3"
        ></Textarea >
      </div>

      <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>

      <DialogFooter>
        <Button type="button" variant="outline" @click="clear">
          Clear
        </Button>
        <Button type="button" @click="verify" :disabled="loading">
          <span v-if="loading">Verifying...</span>
          <span v-else>Confirm</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { Textarea } from "@/components/ui/textarea"
import {
  Dialog, DialogContent, DialogDescription, DialogFooter,
  DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const toast = useToast();
const password = ref('');
const comment = ref(''); // 🆕 New comment field
const error = ref('');
const loading = ref(false);

const props = defineProps({
  voucherId: { type: Number, required: true },
  action: {
    type: String,
    required: true,
    validator: (value: string) => ['approve', 'reject'].includes(value),
  },
});

const emit = defineEmits(['clear', 'success']);

const verify = async () => {
  try {
    loading.value = true;
    error.value = '';
    
    await router.patch(`/vouchers/${props.voucherId}/auditreturn`, {
      password: password.value,
      action: props.action,
      comment: comment.value, // 🆕 Send comment to backend
    }, {
      onSuccess: () => {
        toast.success(`Voucher ${props.action === 'return' ? 'return' : 'rejected'} successfully!`);
        router.visit(`/vouchers/${props.voucherId}/view`);
      },
      onError: (err) => {
        if (err.password) error.value = err.password;
        if (err.status) toast.error(err.status);
      },
      onFinish: () => loading.value = false
    });
  } catch (e) {
    console.error(e);
    loading.value = false;
  }
};

const clear = () => {
  password.value = '';
  comment.value = ''; // 🆕 Clear comment as well
  error.value = '';
  emit('clear');
};
</script>
