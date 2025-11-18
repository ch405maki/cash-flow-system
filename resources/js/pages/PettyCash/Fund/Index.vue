<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { Select, SelectTrigger, SelectContent, SelectItem, SelectValue } from '@/components/ui/select';
import { useToast } from 'vue-toastification';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const toast = useToast();

const props = defineProps<{
  pettyCashFunds: any[],
  users: { 
        id: number; 
        first_name: string 
        last_name: string
    }[],
  departments: { id: number; department_name: string }[],
}>();

const form = useForm({
  user_id: '',
  department_id: '',
  fund_amount: '',
});

const handleSubmit = () => {
  form.post(route('petty-cash.fund.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Petty Cash Fund assigned successfully!');
      form.reset();
    },
    onError: () => {
      toast.error('Failed to assign petty cash fund.');
    },
  });
};

// Display success flash messages from Laravel redirect
watch(() => form.recentlySuccessful, (val) => {
  if (val) toast.success('Petty Cash Fund added successfully.');
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash', href: '#' },
];
</script>

<template>
  <Head title="Petty Cash Fund" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-xl font-bold">Petty Cash Fund Management</h1>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- LEFT COLUMN: Table -->
        <div class="border rounded-xl p-4 shadow-sm">
          <h2 class="font-semibold mb-3">Existing Funds</h2>
          <Table>
            <TableHeader class="bg-muted">
              <TableRow>
                <TableHead class="border-r">User</TableHead>
                <TableHead class="border-r">Department</TableHead>
                <TableHead class="border-r text-right">Fund Amount</TableHead>
                <TableHead class="text-right">Fund Balance</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="fund in props.pettyCashFunds"
                :key="fund.id"
                class="hover:bg-muted/50 cursor-pointer border-b"
              >
                <TableCell class="border-r py-3">
                  {{ fund.user?.first_name || '' }} {{ fund.user?.last_name || '' }}
                </TableCell>
                <TableCell class="border-r py-3">
                  {{ fund.department?.department_name || '—' }}
                </TableCell>
                <TableCell class="border-r text-right font-medium py-3">
                  ₱{{ fund.fund_amount.toLocaleString() }}
                </TableCell>
                <TableCell class="text-right font-medium py-3">
                  ₱{{ fund.fund_balance.toLocaleString() }}
                </TableCell>
              </TableRow>
              <TableRow v-if="!props.pettyCashFunds.length">
                <TableCell colspan="4" class="text-center py-6 text-muted-foreground">
                  No petty cash funds found.
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- RIGHT COLUMN: Form -->
        <div class="border rounded-xl p-4">
          <h2 class="font-semibold mb-3">Assign New Fund</h2>
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- User -->
            <div>
              <Label for="user">User</Label>
              <Select v-model="form.user_id">
                <SelectTrigger>
                  <SelectValue placeholder="Select User" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="user in props.users"
                    :key="user.id"
                    :value="user.id"
                  >
                    {{ user.first_name }} {{ user.last_name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.user_id" class="text-red-500 text-xs mt-1">{{ form.errors.user_id }}</div>
            </div>

            <!-- Department -->
            <div>
              <Label for="department">Department</Label>
              <Select v-model="form.department_id">
                <SelectTrigger>
                  <SelectValue placeholder="Select department" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="dept in props.departments"
                    :key="dept.id"
                    :value="dept.id"
                  >
                    {{ dept.department_name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <div v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</div>
            </div>

            <!-- Amount -->
            <div>
              <Label for="fund_amount">Fund Amount</Label>
              <Input type="number" v-model="form.fund_amount" placeholder="Enter amount" />
              <div v-if="form.errors.fund_amount" class="text-red-500 text-xs mt-1">{{ form.errors.fund_amount }}</div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
              <Button type="submit" :disabled="form.processing || !form.fund_amount">
                {{ form.processing ? 'Saving...' : 'Save Fund' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
