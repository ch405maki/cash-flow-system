<template>
  <Dialog>
    <DialogTrigger as-child>
      <slot name="trigger"></slot>
    </DialogTrigger>
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>Verify Your Identity</DialogTitle>
        <DialogDescription>
          Please enter your password to confirm this action.
        </DialogDescription>
      </DialogHeader>
      <div class="grid gap-4 py-4">
        <div class="grid grid-cols-4 items-center gap-4">
          <Label for="password" class="text-right">
            Password
          </Label>
          <Input
            id="password"
            v-model="password"
            type="password"
            class="col-span-3"
            @keyup.enter="verify"
          />
        </div>
      </div>
      <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
      <DialogFooter>
        <Button type="button" variant="outline" @click="close">
          Cancel
        </Button>
        <Button type="button" @click="verify" :disabled="loading">
          <span v-if="loading">Verifying...</span>
          <span v-else>Verify</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const toast = useToast();
const password = ref('');
const error = ref('');
const loading = ref(false);

const props = defineProps({
  voucherId: {
    type: Number,
    required: true,
  },
});

const verify = async () => {
  try {
    loading.value = true;
    error.value = '';
    
    await router.patch(`/vouchers/${props.voucherId}/approve`, {
      password: password.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Voucher approved successfully!');
      },
      onError: (err) => {
        if (err.password) {
          error.value = err.password;
        }
        if (err.status) {
          toast.error(err.status);
        }
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  } catch (e) {
    console.error(e);
    loading.value = false;
  }
};

const close = () => {
  password.value = '';
  error.value = '';
};
</script>