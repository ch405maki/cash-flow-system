<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  request: Object,
});
</script>

<template>
  <Head title="Create Purchase Order" />
  <div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">Create Purchase Order</h1>

    <div>
      <p><strong>Request No:</strong> {{ request.request_no }}</p>
      <p><strong>Purpose:</strong> {{ request.purpose }}</p>
      <p><strong>Department:</strong> {{ request.department.department_name }}</p>
      <p><strong>Requested By:</strong>
        {{ request.user.first_name }} {{ request.user.last_name }}</p>
    </div>

    <div>
      <h2 class="text-lg font-semibold mt-4">Items</h2>
      <ul class="list-disc pl-6">
        <li v-for="detail in request.details" :key="detail.id">
          {{ detail.quantity }} {{ detail.unit }} - {{ detail.item_description }}
        </li>
      </ul>
    </div>

    <!-- TODO: Add PO form fields here -->

  </div>
</template>
