<script setup lang="ts">
import { router } from '@inertiajs/vue3'

defineProps<{
  recentPurchaseOrders: Array<any>;
}>();

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: '2-digit',
  });
}

function goToPO(id: number) {
  router.visit(`/purchase-orders/${id}`)
}
</script>

<template>
  <div class="rounded-xl border mt-4">
    <h2 class="p-4 text-base font-semibold">Recent Purchase Orders</h2>
    <div class="relative w-full overflow-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b">
            <th class="p-4 text-left">PO No</th>
            <th class="p-4 text-left">Date</th>
            <th class="p-4 text-left">Payee</th>
            <th class="p-4 text-left">Amount</th>
            <th class="p-4 text-left">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="po in recentPurchaseOrders" :key="po.id" class="border-b">
            <td class="p-4 align-middle font-medium cursor-pointer hover:underline hover:text-purple-700 text-purple-800" @click="goToPO(po.id)">{{ po.po_no }}</td>
            <td class="p-4">{{ formatDate(po.date) }}</td>
            <td class="p-4">{{ po.payee }}</td>
            <td class="p-4">₱{{ po.amount.toLocaleString() }}</td>
            <td class="p-4">{{ po.status }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
