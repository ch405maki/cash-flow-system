<script setup lang="ts">
const props = defineProps<{
  pettyCash: any
}>()
</script>

<template>
  <div class="p-4 text-black bg-white w-[900px] mx-auto border border-black text-xs font-sans">
    <!-- HEADER -->
    <div class="text-center">
      <h2 class="font-bold uppercase">ARELLANO LAW FOUNDATION INC.</h2>
      <p>Taft Avenue Corner Menlo Street, Pasay City</p>
      <h3 class="font-bold uppercase underline mt-1">PETTY CASH VOUCHER</h3>
    </div>

    <!-- Paid to + Date / PCV No -->
    <div class="flex justify-between items-end mt-2">
      <p>Paid to : <span class="font-semibold underline">{{ props.pettyCash.paid_to }}</span></p>
      <div class="text-right">
        <p>Date: <span class="underline">{{ new Date(props.pettyCash.date).toLocaleDateString() }}</span></p>
        <p>PCV No.: <span class="underline">{{ props.pettyCash.pcv_no }}</span></p>
      </div>
    </div>

    <div class="grid grid-cols-5 border-x border-t border-black">
        <!-- Checkboxes -->
        <div class="col-span-3">
            <div class="grid grid-cols-3 gap-2 ">
                <label class="flex items-center space-x-1 border-r border-black p-1">
                <input type="checkbox" disabled :checked="props.pettyCash.items.some(i => i.type === 'Reimbursement')" />
                    <span>PAYMENT REIMBURSEMENT</span>
                </label>
                <label class="flex items-center space-x-1 border-r border-black p-1">
                    <input type="checkbox" disabled :checked="props.pettyCash.items.some(i => i.type === 'Cash Advance')" />
                    <span>CASH ADVANCE (CA)</span>
                </label>
                <label class="flex items-center space-x-1 border-r border-black p-1">
                    <input type="checkbox" disabled :checked="props.pettyCash.items.some(i => i.type === 'Liquidation')" />
                    <span>LIQUIDATION OF CA DATED ________</span>
                </label>
            </div>
        </div>
        <div class="col-span-2 ">
            <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center space-x-1 border-r border-black p-1">
                    ACCOUNT NAME
                </label>
                <label class="flex items-center space-x-1 p-1">
                    AMOUNT
                </label>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="grid grid-cols-5">
        <div class="col-span-3">
            <table class="w-full border border-black border-collapse">
                <thead>
                    <tr>
                    <th class="border border-black w-[80px] p-1">DATE</th>
                    <th class="border border-black p-1">PARTICULARS</th>
                    <th class="border border-black w-[100px] p-1">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in props.pettyCash.items" :key="item.id">
                    <td class="border border-black text-center p-1">{{ new Date(item.date).toLocaleDateString() }}</td>
                    <td class="border border-black p-1">{{ item.particulars }}</td>
                    <td class="border border-black text-right p-1">{{ Number(item.amount).toFixed(2) }}</td>
                    </tr>
                    <!-- Filler rows -->
                    <tr v-for="n in 6" :key="'empty-'+n">
                    <td class="border border-black p-3"></td>
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-span-2">
            <table class="w-full border-r border-black border-collapse">
                <tbody>
                    <tr v-for="item in props.pettyCash.items" :key="item.id">
                    <td class="border-t border-black text-center p-3"></td>
                    <td class="border border-black p-1"></td>
                    </tr>
                    <!-- Filler rows -->
                    <tr v-for="n in 7" :key="'empty-'+n">
                    <td class="border-y border-black p-3"></td>
                    <td class="border border-black"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    

    <!-- Received from -->
    <div class="mt-2">
      <p>Received from the amount of ______________________________</p>
      <p>(P __________ ) in full/partial payment of the items listed above.</p>
    </div>

    <!-- Notes -->
    <div class="mt-2 text-[11px] leading-tight">
      <p><b>NOTE:</b> CASH ADVANCE SHALL BE LIQUIDATED WITHIN 72 HOURS; OTHERWISE, I HEREBY AUTHORIZE THE COMPANY TO DEDUCT THE FULL AMOUNT FROM MY NEAREST SUCCEEDING PAYROLL.</p>
    </div>

    <!-- Signatures -->
    <div class="grid grid-cols-2 gap-6 mt-4">
      <div>
        <p>Prepared by: ____________________</p>
        <p>Approved by: ____________________</p>
        <p class="text-[11px]">(Signature over printed name)</p>
      </div>
      <div>
        <p>Audited by: ____________________</p>
        <p>Paid by: ____________________</p>
      </div>
    </div>

    <!-- Bottom -->
    <div class="flex justify-between mt-6">
      <p>Signature: ____________________</p>
      <p>Date: ____________________</p>
    </div>
  </div>
</template>
