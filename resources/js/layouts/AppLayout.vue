<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
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

// onMounted(() => {
//     const user = page.props.auth.user;
//     if (user && !user.terms_accepted_at) {
//         showTermsDialog.value = true;
//     }
// });

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

        // Update local user object
        page.props.auth.user.terms_accepted_at = new Date().toISOString();

        showTermsDialog.value = false;

        // ✅ Redirect after success
        router.visit('/settings/password');

    } catch (error) {
        console.error('Error accepting terms:', error);
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
          <DialogTitle>Data Privacy Consent Form</DialogTitle>
          <DialogDescription>
            Please read carefully and provide your consent to continue using our system.
          </DialogDescription>
        </DialogHeader>

        <!-- Scrollable Content -->
        <div class="max-h-[400px] overflow-y-auto p-4 my-4 border rounded-md space-y-6">
          <section>
            <h3 class="font-semibold mb-2">I. Purpose of Data Collection</h3>
            <p class="text-sm mb-2">
              This form is to obtain your consent for the collection and processing of your personal information in
              connection with the implementation of our Department Requests, Purchase Order, and Voucher System. 
              The information collected will be used for:
            </p>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li>Processing of Department Supplies Requests</li>
              <li>Processing of Purchase Orders and Vouchers</li>
              <li>Facilitating payments to suppliers and vendors</li>
              <li>Maintaining accurate financial records and reports</li>
              <li>Ensuring compliance with relevant laws and regulations</li>
              <li>Other legitimate business purposes related to the PO and Voucher system</li>
            </ul>
          </section>

          <section>
            <h3 class="font-semibold mb-2">II. Types of Personal Information Collected</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li>Employee name and account credentials</li>
              <li>Company name, address, contact information (phone, email)</li>
              <li>TIN, business registration details</li>
              <li>Authorized signatories</li>
              <li>Bank account information (for payment purposes)</li>
            </ul>
          </section>

          <section>
            <h3 class="font-semibold mb-2">III. How Your Personal Data Will Be Used</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li>Processing Department Supplies Requests</li>
              <li>Creating and processing Purchase Orders</li>
              <li>Generating Vouchers for payment</li>
              <li>Verifying supplier/vendor information</li>
              <li>Facilitating payments through authorized methods</li>
              <li>Maintaining records of transactions and payments</li>
              <li>Complying with legal and regulatory requirements</li>
            </ul>
          </section>

          <section>
            <h3 class="font-semibold mb-2">IV. Sharing of Personal Data</h3>
            <p class="text-sm mb-2">Your personal data may be shared with:</p>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li>Authorized personnel involved in the PO and Voucher system</li>
              <li>Financial institutions for payment processing</li>
              <li>Relevant government agencies as required by law</li>
              <li>Third-party service providers (e.g., payment processors, accounting software)</li>
              <li>Other parties as authorized or required by law</li>
            </ul>
          </section>

          <section>
            <h3 class="font-semibold mb-2">V. Your Rights Under the Data Privacy Act</h3>
            <ul class="list-disc list-inside text-sm space-y-1">
              <li>Be informed about data collection and processing</li>
              <li>Access your personal information</li>
              <li>Rectify inaccurate or incomplete data</li>
              <li>Object to processing of your personal information</li>
              <li>Suspend or withdraw your consent</li>
              <li>File a complaint with the National Privacy Commission</li>
            </ul>
          </section>

          <section>
            <h3 class="font-semibold mb-2">VI. Consent</h3>
            <p class="text-sm">
              By clicking "Agree and Continue," you acknowledge that you have read and understood this Data Privacy Consent 
              Form and voluntarily give your free, specific, informed, and unconditional consent to the collection 
              and processing of your personal information as described herein.
            </p>
          </section>

          <section>
            <h3 class="font-semibold mb-2">VII. Contact Information</h3>
            <p class="text-sm">
              For any inquiries, clarifications, or complaints regarding the processing of your personal data, please contact:
            </p>
            <ul class="list-none text-sm space-y-1 mt-2">
              <li>[Name of Data Protection Officer/Contact Person]</li>
              <li>[Designation]</li>
              <li>[Email Address]</li>
              <li>[Phone Number]</li>
            </ul>
          </section>
        </div>

        <!-- Checkbox -->
        <div class="flex items-center space-x-2 mb-4">
          <Checkbox id="terms" v-model:checked="termsAccepted" />
          <label
            for="terms"
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
          >
            I agree to the Data Privacy Consent
          </label>
        </div>

        <!-- Button -->
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