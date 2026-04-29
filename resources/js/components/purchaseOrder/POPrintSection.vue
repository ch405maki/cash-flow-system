<script setup lang="ts">
import FormHeader from '@/components/reports/header/formHeder.vue'

defineProps<{
  purchaseOrder: {
    po_no: string
    payee: string
    check_payable_to?: string
    tin_no?: string
    date: string
    status: string
    amount: number
    purpose?: string
    details: Array<{
      id: number
      quantity: number
      unit: string
      item_description: string
      unit_price: number
      amount: number
    }>
  }
  authUser: {
    name: string
  }
  executiveDirector?: {
    full_name: string
    position: string
  } | null
  formatDate: (d: string) => string
  formatCurrency: (v: number) => string
}>()
</script>

<template>
  <div id="print-section" class="hidden print:block">
    <div>
      <FormHeader text="Purchase Order" :bordered="false" />
    </div>

    <!-- Info Table -->
    <div class="grid grid-cols-1 md:grid-cols-1">
      <table class="w-full text-sm border border-border rounded-lg mb-2">
        <tbody>
          <tr class="border-b">
            <td class="p-2 font-medium text-muted-foreground border-r w-[200px]">COMPANY NAME:</td>
            <td class="p-2 uppercase border-r w-2xl">{{ purchaseOrder.payee }}</td>
            <td class="p-2 font-medium text-muted-foreground border-r w-40">P.O. NUMBER:</td>
            <td class="p-2 w-40 border-r">{{ purchaseOrder.po_no }}</td>
            <td class="p-2 w-40 font-medium text-muted-foreground border-r">P.O. DATE:</td>
            <td class="p-2 w-40">{{ formatDate(purchaseOrder.date) }}</td>
          </tr>
          <tr class="border-b">
            <td class="p-2 font-medium text-muted-foreground border-r">CHECK PAYABLE TO:</td>
            <td class="p-2 uppercase border-r w-2xl">{{ purchaseOrder.check_payable_to }}</td>
            <td class="p-2 font-medium text-muted-foreground border-r">TIN:</td>
            <td class="p-2">{{ purchaseOrder.tin_no }}</td>
          </tr>
        </tbody>
      </table>
      <p class="text-xs italic hidden print:block">
        *Please deliver the following items immediately subject to the agreed terms and condition.
      </p>
    </div>

    <!-- Items Table -->
    <table class="border border-gray-200 w-full">
      <thead>
        <tr class="border-b border-gray-200">
          <th class="text-left p-2 font-medium">Quantity</th>
          <th class="text-left p-2 font-medium">Unit/S</th>
          <th class="text-left p-2 font-medium">Description</th>
          <th class="text-right p-2 font-medium">Unit Price</th>
          <th class="text-right p-2 font-medium">Amount</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in purchaseOrder.details"
          :key="item.id"
          class="border-b border-gray-100"
        >
          <td class="p-2">{{ item.quantity }}</td>
          <td class="p-2">{{ item.unit }}</td>
          <td class="p-2">{{ item.item_description }}</td>
          <td class="p-2 text-right">{{ formatCurrency(item.unit_price) }}</td>
          <td class="p-2 text-right font-medium">{{ formatCurrency(item.amount) }}</td>
        </tr>
        <tr class="border-t border-gray-200">
          <td colspan="4" class="p-2 text-right font-medium">Total</td>
          <td class="p-2 text-right font-bold">{{ formatCurrency(purchaseOrder.amount) }}</td>
        </tr>
      </tbody>
    </table>

    <caption>
      <div class="flex items-center w-full mt-2 px-4">
        <span class="flex-grow border-t border-dashed border-gray-300"></span>
        <span class="mx-3 text-xs font-medium">Nothing Follows</span>
        <span class="flex-grow border-t border-dashed border-gray-300"></span>
      </div>
    </caption>

    <!-- Footer -->
    <div>
      <div class="flex justify-between mt-12 items-center">
        <div class="text-left w-1/2">
          <div class="flex items-center text-sm space-x-[55px]">
            <p>For: {{ purchaseOrder.purpose || 'No purpose' }}</p>
          </div>
        </div>
      </div>

      <div class="flex justify-between mt-12 items-center">
        <div class="text-left w-1/2">
          <div class="text-sm">
            <p>PLEASE NOTE: All orders for equipment and supplies are made only on this form and signed by Executive Director.</p>
            <p>Orders made on other forms and Signed by other person's shall not be honored.</p>
          </div>
        </div>
        <div class="text-right w-1/2">
          <div class="inline-block text-sm border-black uppercase font-semibold">Arellano Law Foundation</div>
        </div>
      </div>

      <div class="flex justify-between mt-12 items-center">
        <div class="text-left w-1/2">
          <div class="flex items-center text-sm uppercase space-x-[55px]">
            <h1>DEPARTMENT:</h1>
          </div>
          <div class="flex items-center text-sm uppercase space-x-[10px]">
            <h1>ACCOUNT CHARGES:</h1>
          </div>
        </div>

        <div class="text-right w-1/2">
          <p class="text-xs mb-10 mr-[60px]">Approved By</p>

          <div v-if="executiveDirector" class="relative inline-block text-sm uppercase">
            <img
              v-if="purchaseOrder.status === 'approved'"
              src="/images/signatures/oed_signature.png"
              alt="Signature"
              class="w-[100px] absolute -top-6 left-1/2 -translate-x-1/2 pointer-events-none"
            />
            <div class="border-b border-black px-2">{{ executiveDirector.full_name }}</div>
            <p class="text-xs mt-1 mr-[30px]">{{ executiveDirector.position }}</p>
          </div>

          <div v-else class="text-xs text-gray-500">No Executive Director assigned.</div>
        </div>
      </div>

      <p class="italic text-zinc-400 text-sm">Prepared By: {{ authUser.name }}</p>
    </div>
  </div>
</template>

<style scoped>
table tr {
  padding: 0 !important;
  line-height: 1.25 !important;
}
table td {
  padding: 0.5rem !important;
}
</style>