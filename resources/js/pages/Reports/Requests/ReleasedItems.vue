<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// destructure directly
const { releases } = defineProps({
  releases: Array
})

const groupedReleases = computed(() => {
  const groups = {}

  for (const release of releases) {
    const reqNo = release.request.request_no
    if (!groups[reqNo]) {
      groups[reqNo] = {
        request_no: reqNo,
        department: release.request.department?.name,
        release_date: release.release_date,
        details: []
      }
    }
    groups[reqNo].details.push(...release.details)
  }

  return Object.values(groups)
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' },
  { title: 'Request Summary', href: '/' },
];
</script>

<template>
  <Head title="Released Items Report" />

    <Head title="Request Summary" />

    <AppLayout :breadcrumbs="breadcrumbs">

    <div class="p-6">
        <h1 class="text-xl font-bold mb-4">Released Items Report</h1>

        <table class="table-auto border-collapse border w-full text-sm">
        <thead>
            <tr class="bg-gray-100">
            <th class="border px-2 py-1">Release Date</th>
            <th class="border px-2 py-1">Request No</th>
            <th class="border px-2 py-1">Department</th>
            <th class="border px-2 py-1">Item</th>
            <th class="border px-2 py-1">Qty</th>
            <th class="border px-2 py-1">Unit</th>
            </tr>
        </thead>
        <tbody>
            <template v-for="group in groupedReleases" :key="group.request_no">
            <tr v-for="(detail, index) in group.details" :key="detail.id">
                <!-- Only show parent info on the first row -->
                <td v-if="index === 0" :rowspan="group.details.length" class="border px-2 py-1">
                {{ group.release_date }}
                </td>
                <td v-if="index === 0" :rowspan="group.details.length" class="border px-2 py-1">
                {{ group.request_no }}
                </td>
                <td v-if="index === 0" :rowspan="group.details.length" class="border px-2 py-1">
                {{ group.department }}
                </td>

                <!-- Details -->
                <td class="border px-2 py-1">{{ detail.request_detail.item_description }}</td>
                <td class="border px-2 py-1">{{ detail.quantity }}</td>
                <td class="border px-2 py-1">{{ detail.request_detail.unit }}</td>
            </tr>
            </template>
        </tbody>
        </table>
    </div>
    </AppLayout>
</template>
