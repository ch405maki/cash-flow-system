<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItemType } from '@/types';
import NavUser from '@/components/NavUser.vue';
import AppearanceTabs from '@/components/AppearanceToggle.vue';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
  <header
    class="flex h-16 shrink-0 items-center border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-[[data-collapsible=icon]]/sidebar-wrapper:h-12 md:px-4"
  >
    <div class="flex justify-between items-center w-full">
      <!-- Left side: trigger + breadcrumbs -->
      <div class="flex items-center gap-2 flex-shrink-0">
        <SidebarTrigger class="-ml-1" />
        <template v-if="breadcrumbs.length > 0">
          <Breadcrumbs :breadcrumbs="breadcrumbs" />
        </template>
      </div>

      <!-- Right side: NavUser, fixed width -->
      <div class="flex-shrink-0 flex items-center gap-2">
        <AppearanceTabs />
        <NavUser />
      </div>
    </div>
  </header>
</template>

