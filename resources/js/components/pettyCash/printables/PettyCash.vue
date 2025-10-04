<script setup lang="ts">
const props = defineProps<{
  pettyCash: any
}>()
</script>

<template>
  <div class="p-4 text-black bg-white w-[900px] mx-auto text-xs font-sans">
    <!-- HEADER -->
    <div class="text-center border-t border-x border-black">
      <h2 class="font-bold uppercase">ARELLANO LAW FOUNDATION INC.</h2>
      <p>Taft Avenue Corner Menlo Street, Pasay City</p>
    </div>

    <!-- Paid to + Date / PCV No -->
    <div class="grid grid-cols-5 border-x border-t border-black">
      <div class="col-span-3 border-r border-black">
          <div class="grid grid-cols-3 gap-2 ">
              <h1 class="font-bold uppercase mt-1">PETTY CASH VOUCHER</h1>
          </div>
      </div>
      <div class="col-span-2">
          <div class="grid grid-cols-2 gap-2">
            <p class="pr-2 border-r border-black">
              Date: <span>{{ new Date(props.pettyCash.date).toLocaleDateString() }}</span>
            </p>
            <p class="pl-2">
              PCV No.: <span>{{ props.pettyCash.pcv_no }}</span>
            </p>
          </div>
      </div>
    </div>
    <div class="grid grid-cols-5 border-x border-t border-black">
      <div class="col-span-3 border-r border-black">
          <div class="grid grid-cols-3 gap-2 ">
              <h1>Paid to : <span class="font-semibold underline">{{ props.pettyCash.paid_to }}</span></h1>
          </div>
      </div>
      <div class="col-span-2 ">
          <div class="grid grid-cols-2 gap-2">
              <h1>Distribution of Expense</h1>
          </div>
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
            <div class="grid grid-cols-1 grid-rows-3 gap-0 border-x border-black">
              <div class="border-b border-black">
                <!-- Received from -->
                <div class="mt-2">
                  <p>Received from the amount of ______________________________</p>
                  <p>(P __________ ) in full/partial payment of the items listed above.</p>
                </div>
              </div>
              <div class="border-b border-black">
                <!-- Notes -->
                <div class="mt-2 text-[11px] leading-tight">
                  <p><b>NOTE:</b> CASH ADVANCE SHALL BE LIQUIDATED WITHIN 72 HOURS; OTHERWISE, I HEREBY AUTHORIZE THE COMPANY TO DEDUCT THE FULL AMOUNT FROM MY NEAREST SUCCEEDING PAYROLL.</p>
                </div>
              </div>
              <div class="border-b border-black">
                <!-- Bottom -->
                <div class="flex justify-between">
                  <p>Signature: ____________________</p>
                  <p>Date: ____________________</p>
                </div>
              </div>
          </div>
        </div>
        
        <div class="col-span-2">
          <table class="w-full table-fixed border-collapse">
            <tbody>
              <tr v-for="item in props.pettyCash.items" :key="item.id">
                <td class="w-1/2 border-t border-black text-center p-3"></td>
                <td class="w-1/2 border border-black p-1"></td>
              </tr>
              <!-- Filler rows -->
              <tr v-for="n in 5" :key="'empty-'+n">
                <td class="w-1/2 border-y border-black p-3"></td>
                <td class="w-1/2 border border-black"></td>
              </tr>
            </tbody>
          </table>
          
          <div class="grid grid-cols-1 grid-rows-4 gap-0 border-r border-black ">
              <div class="border-b border-black">
                <p>Prepared by: ____________________</p>
              </div>
              <div class="border-b border-black">
                  <p>Approved by: ____________________</p>
                  <p class="text-[11px]">(Signature over printed name)</p>
              </div>
              <div class="border-b border-black">
                <p>Audited by: ____________________</p>
              </div>
              <div class="border-b border-black">
                <p>Paid by: ____________________</p>
              </div>
          </div>
        </div>
    </div>
  </div>
</template>
