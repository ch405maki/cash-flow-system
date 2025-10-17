<script setup lang="ts">
import { formatDate } from '@/lib/utils'
import { computed } from 'vue';
import { amountToWords } from '@/lib/utils'

const props = defineProps<{
  pettyCash: any
}>()

// compute total by type
const totalsByType = computed(() => {
  return props.pettyCash.items.reduce((totals: Record<string, number>, item: any) => {
    const type = item.type || 'Unknown'
    totals[type] = (totals[type] || 0) + Number(item.amount || 0)
    return totals
  }, {})
})

// compute change amount (Cash Advance - Liquidation)
const changeAmount = computed(() => {
  const cashAdvance = totalsByType.value['Cash Advance'] || 0
  const liquidation = totalsByType.value['Liquidation'] || 0
  const diff = cashAdvance - liquidation

  // only show positive values
  return diff > 0 ? diff : null
})
</script>


<template>
  <div class="p-4 text-black bg-white w-[900px] mx-auto text-xs font-sans">
    <!-- HEADER -->
    <div class="text-center border-t border-x border-black p-2">
      <h2 class="font-bold uppercase">ARELLANO LAW FOUNDATION INC.</h2>
      <p>Taft Avenue Corner Menlo Street, Pasay City</p>
    </div>
    <!-- Paid to + Date / PCV No -->
    <div class="grid grid-cols-5 border-x border-t border-black">
      <div class="col-span-3 border-r border-black flex justify-center p-1">
        <h1 class="font-bold uppercase">PETTY CASH VOUCHER</h1>
      </div>
      <div class="col-span-2">
          <div class="grid grid-cols-2">
            <p class="p-1 border-r border-black">
              Date: <span>{{ new Date(props.pettyCash.date).toLocaleDateString() }}</span>
            </p>
            <p class="p-1">
              PCV No.: <span>{{ props.pettyCash.pcv_no }}</span>
            </p>
          </div>
      </div>
    </div>
    <div class="grid grid-cols-5 border-x border-t border-black">
      <div class="col-span-3 border-r border-black">
          <div class="grid grid-cols-3 p-1">
              <h1>Paid to : <span class="font-semibold capitalize">{{ props.pettyCash.paid_to }}</span></h1>
          </div>
      </div>
      <div class="col-span-2 p-1 flex justify-center">
          <h1>Distribution of Expense</h1>
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
        <div class="col-span-2">
            <div class="grid grid-cols-2 h-10">
                <label class="flex items-center justify-center space-x-1 p-1">
                    ACCOUNT NAME
                </label>
                <label class="flex items-center justify-center border-l border-black space-x-1 p-1">
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
                    <td class="border border-black text-center p-1">{{ formatDate(item.date) }}</td>
                    <td class="border border-black p-1">{{ item.particulars }}</td>
                    <td class="border border-black text-right p-1">₱ {{ Number(item.amount).toFixed(2) }}</td>
                    </tr>
                    <!-- Filler rows -->
                    <tr v-for="n in 6" :key="'empty-'+n">
                    <td class="border border-black p-3"></td>
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                    </tr>
                </tbody>
            </table>
            <div class="grid grid-cols-1  border-x border-black">
              <div class="border-b border-black">
                <!-- Received from -->
                <div class="p-1">
                  <p>Received from the amount of <span class="underline px-2 font-">{{ amountToWords(changeAmount) }}</span></p>
                  <p><span class="underline px-2">(₱ {{ changeAmount.toLocaleString() }})</span> in full/partial payment of the items listed above.</p>
                </div>
              </div>
              <div class="border-b border-black">
                <!-- Notes -->
                <div class="p-1 text-[11px] leading-tight">
                  <p><b>NOTE:</b> CASH ADVANCE SHALL BE LIQUIDATED WITHIN 72 HOURS; OTHERWISE, I HEREBY AUTHORIZE THE COMPANY TO DEDUCT THE FULL AMOUNT FROM MY NEAREST SUCCEEDING PAYROLL.</p>
                </div>
              </div>
              <div class="border-b border-black">
                <div class="grid grid-cols-2">
                  <p class="border-r border-black p-1">Signature: ____________________</p>
                  <p class="p-1">Date: ____________________</p>
                </div>
              </div>
            </div>
        </div>
        
        <div class="col-span-2">
          <table class="w-full table-fixed border-collapse">
            <tbody>
              <tr v-for="item in props.pettyCash.items" :key="item.id">
                <td class="border-t border-black text-center p-3"></td>
                <td class="border border-black p-1"></td>
              </tr>
              <!-- Filler rows -->
              <tr v-for="n in 5" :key="'empty-'+n">
                <td class="border-y border-black p-3"></td>
                <td class="border border-black"></td>
              </tr>
            </tbody>
          </table>
          
          <div class="grid grid-cols-1 grid-rows-4 border-r border-black h-[167px]">
              <div class="border-b border-black py-3 p-1">
                <p>Prepared by: ____________________</p>
              </div>
              <div class="border-b border-black p-1">
                  <p>Approved by: ____________________</p>
                  <p class="text-[11px]">(Signature over printed name)</p>
              </div>
              <div class="border-b border-black p-1">
                <p>Audited by: ____________________</p>
              </div>
              <div class="border-b border-black p-1">
                <p>Paid by: ____________________</p>
              </div>
          </div>
        </div>
    </div>
  </div>
</template>
