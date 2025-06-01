
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3'; // Make sure to import router
import PlaceholderPattern from '@/components/PlaceholderPattern.vue';
import { FolderOpen } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' },
];
</script>

<template>
  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <!-- Clickable Folder Card -->
        <div 
          class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border cursor-pointer hover:bg-muted/50 transition-colors"
          @click="router.visit('/reports/po-summary')"
        >
          <div class="flex flex-col items-center justify-center h-full p-4">
            <FolderOpen class="w-12 h-12 text-primary mb-2" />
            <span class="text-sm font-medium">Purchase Order Summary</span>
          </div>
        </div>
        <div 
          class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border cursor-pointer hover:bg-muted/50 transition-colors"
          @click="router.visit('/reports/vouchers')"
        >
          <div class="flex flex-col items-center justify-center h-full p-4">
            <FolderOpen class="w-12 h-12 text-primary mb-2" />
            <span class="text-sm font-medium">Voucher Summary</span>
          </div>
        </div>
        <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
          <PlaceholderPattern />
        </div>
      </div>
      
      <!-- Rest of your content -->
      <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
        <PlaceholderPattern />
      </div>
    </div>
  </AppLayout>
</template>