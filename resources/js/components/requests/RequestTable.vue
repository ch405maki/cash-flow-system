<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { defineProps } from 'vue';

const props = defineProps<{
  requests: Array<any>;
}>();

const getFullName = (user: any) =>
  `${user.first_name} ${user.middle_name} ${user.last_name}`;
</script>

<template>
  <div class="rounded-lg border">
    <table class="w-full table-auto text-left text-sm">
      <thead class="bg-gray-100 dark:bg-gray-800">
        <tr>
          <th class="px-4 py-2">Request No</th>
          <th class="px-4 py-2">Date</th>
          <th class="px-4 py-2">Purpose</th>
          <th class="px-4 py-2">Department</th>
          <th class="px-4 py-2">Requested By</th>
          <th class="px-4 py-2">Status</th>
          <th class="px-4 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="request in requests"
          :key="request.id"
          class="border-t hover:bg-muted/50"
        >
          <td class="px-4 py-2 font-medium">{{ request.request_no }}</td>
          <td class="px-4 py-2">{{ request.request_date }}</td>
          <td class="px-4 py-2">{{ request.purpose }}</td>
          <td class="px-4 py-2">{{ request.department?.department_name }}</td>
          <td class="px-4 py-2">{{ getFullName(request.user) }}</td>
          <td class="px-4 py-2 capitalize">
            <span
              class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
              :class="{
                'bg-yellow-100 text-yellow-800': request.status === 'pending',
                'bg-green-100 text-green-800': request.status === 'approved',
                'bg-red-100 text-red-800': request.status === 'rejected',
              }"
            >
              {{ request.status }}
            </span>
          </td>
          <td class="px-4 py-2 space-x-2">
            <Button size="sm" variant="outline">Create PO</Button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
