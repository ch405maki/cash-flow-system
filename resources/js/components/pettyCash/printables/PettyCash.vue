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

// compute change amount (taking into account Reimbursement)
const changeDetails = computed(() => {
  const cashAdvance = totalsByType.value['Cash Advance'] || 0
  const liquidation = totalsByType.value['Liquidation'] || 0
  const reimbursement = totalsByType.value['Reimbursement'] || 0
  
  // Calculate net amount
  // For Reimbursement: company pays the employee
  // For Liquidation: employee returns excess cash advance
  // For Cash Advance: company gives money to employee
  
  // If there's a cash advance, we're in a CA/Liquidation scenario
  if (cashAdvance > 0) {
    const diff = liquidation - cashAdvance
    if (diff > 0) {
      // liquidation > cash advance → company owes employee
      return { label: 'Amount Due', amount: diff }
    } else if (diff < 0) {
      // liquidation < cash advance → employee owes company
      return { label: 'Return', amount: Math.abs(diff) }
    } else {
      return { label: 'Settled', amount: 0 }
    }
  } 
  // If there's reimbursement but no cash advance
  else if (reimbursement > 0) {
    // Reimbursement is payment to employee
    return { label: 'Amount', amount: reimbursement }
  }
  
  return { label: '', amount: 0 }
})

// Check if this is a reimbursement-only voucher
const isReimbursementOnly = computed(() => {
  return totalsByType.value['Cash Advance'] === 0 && 
         totalsByType.value['Reimbursement'] > 0
})

// Get reimbursement total for display
const reimbursementTotal = computed(() => {
  return totalsByType.value['Reimbursement'] || 0
})

const cashAdvanceDate = computed(() => {
  const caItem = props.pettyCash.items.find(i => i.type === 'Cash Advance')
  return caItem ? formatDate(caItem.date) : ''
})
</script>

<template>
  <div class="p-4 text-black bg-white w-[900px] mx-auto text-xs font-sans">
    <div class="border border-black">
    <!-- HEADER -->
    <div class="text-center p-2">
      <h2 class="font-bold uppercase">ARELLANO LAW FOUNDATION INC.</h2>
      <p>Taft Avenue Corner Menlo Street, Pasay City</p>
    </div>
    <!-- Paid to + Date / PCV No -->
    <div class="grid grid-cols-5 border-t border-black">
      <div class="col-span-3 border-r border-black flex justify-center p-1">
        <h1 class="font-bold uppercase">PETTY CASH VOUCHER</h1>
      </div>
      <div class="col-span-2">
          <div class="grid grid-cols-2">
            <p class="p-1 border-r border-black">
              Date: <span>{{ formatDate(props.pettyCash.date) }}</span>
            </p>
            <p class="p-1">
              PCV No.: <span>{{ props.pettyCash.pcv_no }}</span>
            </p>
          </div>
      </div>
    </div>
    <div class="grid grid-cols-5 border-t border-black">
      <div class="col-span-3 border-r border-black">
          <div class="grid grid-cols-3 p-1">
              <h1>Paid to : <span class="font-semibold capitalize">{{ props.pettyCash.paid_to }}</span></h1>
          </div>
      </div>
      <div class="col-span-2 p-1 flex justify-center">
          <h1>Distribution of Expense</h1>
      </div>
    </div>

    <div class="grid grid-cols-5 border-t border-black">
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
                  <span>
                    LIQUIDATION OF CA DATED: 
                    <span v-if="cashAdvanceDate" class="underline px-1 uppercase font-medium">{{ cashAdvanceDate }}</span>
                    <span v-else>________</span>
                  </span>
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
            <table class="w-full border-t border-black border-collapse">
                <thead>
                    <tr>
                    <th class="border-b border-black w-[100px] p-1">DATE</th>
                    <th class="border border-black p-1">PARTICULARS</th>
                    <th class="border border-black w-[100px] p-1">AMOUNT</th>
                    </tr>
                </thead>
                <tbody>
                  <!-- Actual item rows -->
                  <tr v-for="item in props.pettyCash.items.slice(0, 8)" :key="item.id">
                    <td class="border-b border-black text-center p-1 text-xs">{{ formatDate(item.date) }}</td>
                    <td class="border border-black p-1 capitalize">{{ item.particulars }} <span class="text-xs italic">- ({{ item.type }})</span></td>
                    <td class="border border-black text-right p-1">₱ {{ Number(item.amount).toFixed(2) }}</td>
                  </tr>

                  <!-- Filler rows (if less than 9 items) -->
                  <tr
                    v-for="n in Math.max(0, 8 - props.pettyCash.items.length)"
                    :key="'empty-' + n"
                  >
                    <td class="border-b border-black p-3"></td>
                    <td class="border border-black"></td>
                    <td class="border border-black"></td>
                  </tr>
                </tbody>
                <tbody>
                  <!-- Summary row -->
                  <tr>
                      <td class="border-b border-black text-center p-1"></td>
                      <td class="border border-black p-1 capitalize text-right font-bold">
                        {{ changeDetails.label }}
                      </td>
                      <td class="border border-black text-right p-1 font-bold">
                        ₱ {{ changeDetails.amount ? changeDetails.amount.toLocaleString() : '0.00' }}
                      </td>
                  </tr>
                </tbody>
            </table>
            <div class="grid grid-cols-1  border-r border-black">
              <div class="border-b border-black">
                <!-- Received from - Updated for Reimbursement -->
                <div class="p-1">
                  <p class="flex flex-wrap items-baseline gap-1">
                    <span>Received from the amount of</span>
                    <span class="underline font-medium tracking-wider break-words max-w-full">
                      {{ changeDetails.amount ? amountToWords(changeDetails.amount) : '' }}
                    </span>
                    <span v-if="!props.pettyCash.items.some(i => i.type === 'Reimbursement')" class="underline font-medium tracking-wider whitespace-nowrap">
                      (₱ {{ changeDetails.amount ? changeDetails.amount.toLocaleString() : '' }})
                    </span>
                    <span v-if="isReimbursementOnly">
                      as reimbursement for expenses.
                    </span>
                    <span v-else-if="changeDetails.label === 'Amount Due'">
                      as amount due.
                    </span>
                    <span v-else-if="changeDetails.label === 'Return'">
                      to be returned.
                    </span>
                    <span v-else-if="changeDetails.label === 'Settled'">
                      as settled.
                    </span>
                  </p>
                </div>
              </div>
              <div class="border-b border-black">
                <!-- Notes -->
                <div class="p-1 text-[11px] leading-tight">
                  <p><b>NOTE:</b> CASH ADVANCE SHALL BE LIQUIDATED WITHIN 72 HOURS; OTHERWISE, I HEREBY AUTHORIZE THE COMPANY TO DEDUCT THE FULL AMOUNT FROM MY NEAREST SUCCEEDING PAYROLL.</p>
                </div>
              </div>
              <div class="py-[.5px]">
                <div class="grid grid-cols-2">
                  <p class="border-r border-black p-1">Signature: ____________________</p>
                  <p class="p-1">Date: ____________________</p>
                </div>
              </div>
            </div>
        </div>
        
        <div class="col-span-2 flex flex-col h-full">
          <!-- Table section -->
          <table class="w-full table-fixed border-collapse flex-grow">
            <tbody>
              <!-- Actual item rows (limit to 8 max) -->
              <tr v-for="item in props.pettyCash.items.slice(0, 8)" :key="item.id">
                <td class="border-t border-black text-center p-3"></td>
                <td class="border-l border-t border-b border-black p-1"></td>
              </tr>

              <!-- Filler rows -->
              <tr
                v-for="n in Math.max(0, 8 - props.pettyCash.items.length)"
                :key="'empty-'+n"
              >
                <td class="border-y border-black p-3"></td>
                <td class="border-b border-l border-black"></td>
              </tr>
            </tbody>
          </table>

          <!-- Signature boxes (except Paid by) -->
          <div class="grid grid-cols-1 grid-rows-3">
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
          </div>

          <!-- Paid by at the bottom -->
          <div class="p-1">
            <p>Paid by: ____________________</p>
          </div>
        </div>

    </div>
    </div>
  </div>
</template>