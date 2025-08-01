<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const termsAccepted = ref(false);
const showTermsDialog = ref(false);
const page = usePage();

// Check if user needs to accept terms
onMounted(() => {
    const user = page.props.auth.user;
    if (user && !user.terms_accepted_at) {
        showTermsDialog.value = true;
    }
});

const acceptTerms = async () => {
    try {
        const userId = page.props.auth.user?.id;
        if (!userId) {
            throw new Error('User ID not available');
        }

        await axios.post('/api/terms/accept', { 
            accepted: true,
            user_id: userId
        });
        
        // Update the local user object to prevent the dialog from showing again
        if (page.props.auth.user) {
            page.props.auth.user.terms_accepted_at = new Date().toISOString();
        }
        showTermsDialog.value = false;
    } catch (error) {
        console.error('Error accepting terms:', error);
        // Optionally show an error message to the user
    }
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
    <Dialog :open="showTermsDialog" :modal="true">
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