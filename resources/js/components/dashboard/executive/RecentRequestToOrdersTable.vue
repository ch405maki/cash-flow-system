<script setup lang="ts">
import { router } from '@inertiajs/vue3'

defineProps<{
  recentRequestToOrders: Array<any>;
}>();

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: '2-digit',
  });
}

function viewRequest(id: number) {
  router.visit(`/request-to-order/${id}`) 
}
</script>

<template>
  <div class="rounded-xl border mt-4">
    <h2 class="p-4 text-base font-semibold">Recent Request to Orders</h2>
    <div class="relative w-full overflow-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b">
            <th class="p-4 text-left">Order No</th>
            <th class="p-4 text-right">Date</th>
            <th class="p-4 text-right">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in recentRequestToOrders" :key="order.id" class="border-b">
            <td class="p-4 align-middle font-medium cursor-pointer hover:underline hover:text-purple-700 text-purple-800"  @click="viewRequest(order.id)">{{ order.order_no }}</td>
            <td class="p-4 text-right">{{ formatDate(order.order_date) }}</td>
            <td class="p-4 text-right">{{ order.status }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
