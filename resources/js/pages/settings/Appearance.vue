<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import { useWatermark } from '@/composables/useWatermark';
import CustomSwitch from '@/components/ui/switch/CustomSwitch.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];

const { isWatermarkEnabled, toggleWatermark } = useWatermark();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />
                
                <!-- Appearance Tabs (Theme selector) -->
                <AppearanceTabs />
                
                <!-- Watermark Toggle Section -->
                <div class="space-y-4 pt-4 border-t">
                    <HeadingSmall 
                        title="Watermark Settings" 
                        description="Configure the background watermark logo" 
                    />
                    
                    <div class="flex items-center justify-between rounded-lg border p-4">
                        <div class="space-y-0.5">
                            <h3 class="text-base font-medium">Show Watermark Logo</h3>
                            <p class="text-sm text-muted-foreground">
                                Display the logo as a watermark in the background of the application
                            </p>
                        </div>
                        <CustomSwitch
                            :model-value="isWatermarkEnabled"
                            @update:model-value="toggleWatermark"
                        />
                    </div>
                    
                    <!-- Preview of watermark (optional) -->
                    <div v-if="isWatermarkEnabled" class="mt-4 rounded-lg border p-4 bg-muted/20">
                        <p class="text-sm text-muted-foreground mb-2">Preview:</p>
                        <div class="relative h-32 w-full rounded-md bg-background overflow-hidden border">
                            <div class="absolute inset-0 flex items-center justify-center opacity-20">
                                <img
                                    src="/images/logo/logo.png"
                                    alt="Watermark Preview"
                                    class="h-16 w-auto object-contain"
                                />
                            </div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xs text-muted-foreground">Content area with watermark</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>