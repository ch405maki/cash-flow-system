<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const props = defineProps({
  request: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  purpose: props.request.purpose,
  details: props.request.details,
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Requests', href: '/request' },
  { title: `${props.request.request_no}`, href: '' },
];

function submit() {
  form.put(`/requests/${props.request.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Handle success
    },
  });
}
</script>

<template>
  <Head :title="`Edit Request ${request.request_no}`" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-4">
      <h1 class="text-2xl font-bold">Edit Request</h1>

      {{ request }}
      
      <form @submit.prevent="submit">
        <div class="space-y-4">
          <div>
            <Label for="purpose">Purpose</Label>
            <Input id="purpose" v-model="form.purpose" />
          </div>
          
          <div>
            <h2 class="text-lg font-semibold">Items</h2>
            <div v-for="(detail, index) in form.details" :key="detail.id" class="space-y-2">
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <Label :for="`quantity-${index}`">Quantity</Label>
                  <Input :id="`quantity-${index}`" v-model="detail.quantity" type="number" />
                </div>
                <div>
                  <Label :for="`unit-${index}`">Unit</Label>
                  <Input :id="`unit-${index}`" v-model="detail.unit" />
                </div>
                <div>
                  <Label :for="`description-${index}`">Description</Label>
                  <Input :id="`description-${index}`" v-model="detail.item_description" />
                </div>
              </div>
            </div>
          </div>
          
          <Button type="submit" :disabled="form.processing">
            Update Request
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>