<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  vouchers: Array<any>;
}>();

const getFullName = (user: any) =>
  `${user.first_name} ${user.middle_name} ${user.last_name}`;

  function goToEditVoucher(id: number) {
    router.get(`/vouchers/${id}/edit`);
  }
</script>

<template>
  <div class="rounded-lg border">
    <table class="w-full table-auto text-left text-sm">
      <thead class="bg-gray-100 dark:bg-gray-800">
        <tr>
          <th class="px-4 py-2">Voucher No</th>
          <th class="px-4 py-2">Type</th>
          <th class="px-4 py-2">Created By</th>
          <th class="px-4 py-2">Purpose</th>
          <th class="px-4 py-2">Check Amount</th>
          <th class="px-4 py-2">Payee</th>
          <th class="px-4 py-2">Check Pay to</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="vouchers in vouchers"
          :key="vouchers.id"
          class="border-t hover:bg-muted/50"
        >
          <td class="px-4 py-2 font-medium">{{ vouchers.voucher_no }}</td>
          <td class="px-4 py-2">{{ vouchers.type }}</td>
          <td class="px-4 py-2">{{ getFullName(vouchers.user) }}</td>
          <td class="px-4 py-2">{{ vouchers.purpose }}</td>
          <td class="px-4 py-2">{{ vouchers.check_amount }}</td>
          <td class="px-4 py-2">{{ vouchers.payee }}</td>
          <td class="px-4 py-2">{{ vouchers.check_payable_to }}</td>
          <td class="px-4 py-2 capitalize">
            <span
              class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
              :class="{
                'bg-yellow-100 text-yellow-800': vouchers.status === 'pending',
                'bg-green-100 text-green-800': vouchers.status === 'approved',
                'bg-red-100 text-red-800': vouchers.status === 'rejected',
              }"
            >
              {{ vouchers.status }}
            </span>
          </td>
          <td class="px-4 py-2 space-x-2">
            <Button
              size="sm"
              variant="outline"
              @click="goToEditVoucher(vouchers.id)"
            >
               View Details
            </Button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
