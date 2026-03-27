<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { useWatermark } from '@/composables/useWatermark';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { isWatermarkEnabled } = useWatermark();
</script>

<template>
  <AppShell variant="sidebar">
    <AppSidebar />

    <AppContent variant="sidebar">
      <div class="relative min-h-screen">
        <!-- Watermark - conditionally shown with transition -->
        <Transition
          enter-active-class="transition-opacity duration-300"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition-opacity duration-300"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-if="isWatermarkEnabled"
            class="fixed left-[var(--sidebar-width)] right-0 top-0 bottom-0 flex items-center justify-center opacity-10 pointer-events-none z-0"
          >
            <img
              src="/images/logo/logo.png"
              alt="Background Logo"
              class="object-contain max-w-[90%] max-h-[90%] w-auto h-auto select-none"
              :style="{
                width: 'clamp(280px, 40vw, 480px)',
                height: 'auto'
              }"
            />
          </div>
        </Transition>

        <!-- Foreground Content -->
        <div class="relative z-10">
          <AppSidebarHeader :breadcrumbs="breadcrumbs" />
          <slot />
        </div>
      </div>
    </AppContent>
  </AppShell>
</template>