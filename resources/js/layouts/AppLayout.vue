<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ref, onMounted } from 'vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const termsAccepted = ref(false);
const showTermsDialog = ref(false);

onMounted(() => {
    const accepted = localStorage.getItem('termsAccepted');
    if (!accepted) {
        showTermsDialog.value = true;
    }
});

const acceptTerms = () => {
    localStorage.setItem('termsAccepted', 'true');
    showTermsDialog.value = false;
};
</script>

<template>
    <!-- Main content with conditional opacity -->
    <div :class="{'opacity-50 pointer-events-none': showTermsDialog}">
        <AppLayout :breadcrumbs="breadcrumbs">
            <slot />
        </AppLayout>
    </div>

    <!-- Dialog overlay - will appear on top of the semi-transparent content -->
    <Dialog :open="showTermsDialog">
        <DialogContent class="sm:max-w-[625px]">
            <DialogHeader>
                <DialogTitle>Terms and Conditions</DialogTitle>
                <DialogDescription>
                    Please read and accept our terms and conditions to continue.
                </DialogDescription>
            </DialogHeader>

            <div class="max-h-[400px] overflow-y-auto p-4 my-4 border rounded-md">
                <h3 class="font-semibold mb-2">1. Introduction</h3>
                <p class="text-sm mb-4">Paragraph Here.</p>
                
                <h3 class="font-semibold mb-2">2. User Responsibilities</h3>
                <p class="text-sm mb-4">Paragraph Here.</p>
            </div>

            <div class="flex items-center space-x-2 mb-4">
                <Checkbox id="terms" v-model:checked="termsAccepted" />
                <label
                    for="terms"
                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                >
                    I agree to the terms and conditions
                </label>
            </div>

            <Button 
                type="button" 
                class="w-full" 
                :disabled="!termsAccepted"
                @click="acceptTerms"
            >
                Agree and Continue
            </Button>
        </DialogContent>
    </Dialog>
</template>