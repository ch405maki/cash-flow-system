<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { Button } from '@/components/ui/button'
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard', },
    { title: 'Petty Cash', href: '#', },
];

const props = defineProps<{
  pettyCash: object
}>()

const goToCreate = () => {
  router.get(route('petty-cash.create'))
}
</script>

<template>
    <Head title="Petty Cash" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
          <div class="flex justify-between">
            <div>
              <h1 class="text-xl font-bold">Petty Cash</h1>
              <p class="text-sm font-medium">List of created petty cash.</p>
            </div>
            <Button @click="goToCreate">Create New Petty Cash</Button>
          </div>
          <Table>
            <TableCaption>A list of your petty cash.</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[300px]">
                  PCV NO
                </TableHead>
                <TableHead class="w-[100px]">Status</TableHead>
                <TableHead class="text-right">
                  Action
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in pettyCash" :key="item.id">
                <TableCell class="font-medium">
                  {{ item.pcv_no }}
                </TableCell>
                <TableCell>Draft</TableCell>
                <TableCell class="text-right">
                  <Button @click="router.get(route('petty-cash.edit', item.id))">
                    Edit
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
    </AppLayout>
</template>
