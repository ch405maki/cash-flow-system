<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { FileText } from 'lucide-vue-next';
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
const hasItem = Object.keys(props.pettyCash).length;

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
            <Button v-if="hasItem" @click="goToCreate">Create New Petty Cash</Button>
          </div>
          <Table v-if="hasItem">
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
                <TableCell class="capitalize">{{ item.status }}</TableCell>
                <TableCell class="flex justify-end space-x-2">
                  <Button v-if="item.status != 'draft'" @click="router.get(route('bursar.petty-cash.view', item.id))">
                    View Status
                  </Button>
                  <Button v-if="item.status == 'draft'" @click="router.get(route('petty-cash.edit', item.id))">
                    Review
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
            <FileText class="h-8 w-8 text-muted-foreground" />
            <p class="mt-2 text-sm text-muted-foreground">No petty cash created found</p>
            <p class="text-xs text-muted-foreground mb-4">List of your petty cash will appear here</p>
            <Button @click="goToCreate">Create New Petty Cash</Button>
          </div>
        </div>
    </AppLayout>
</template>
