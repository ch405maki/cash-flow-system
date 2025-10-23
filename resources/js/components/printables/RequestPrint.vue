<script setup lang="ts">
import { formatDate } from '@/lib/utils'
import { Head } from '@inertiajs/vue3'

// Define props interface
interface RequestDetail {
  id: number
  item_description: string
  quantity: number
  released_quantity: number
  unit: string
}

interface Request {
  request_no: string
  request_date: string
  purpose: string
  status: string
  details: RequestDetail[]
}

defineProps<{
  request: Request,
  user: Request
}>()

// Make the print function available to parent component
defineExpose({
  printArea: () => {
    const printContents = document.getElementById('print-section')?.innerHTML
    const originalContents = document.body.innerHTML

    if (printContents) {
      document.body.innerHTML = printContents
      window.print()
      document.body.innerHTML = originalContents
      window.location.reload()
    } else {
      console.error('Print section not found')
    }
  }
})
</script>

<template>
  <div id="print-section">
    <!-- hidden print:block -->
    <div class="hidden print:block">
      <Head :title="`Order ${request.request_no}`" />
      
      <header :class="`flex items-center mb-2 mr-[50px] max-w-full justify-center mx-auto`">
        <figure class="shrink-0 mr-4">
          <img 
            src="/images/logo/logo.png" 
            class="w-10 h-auto" 
            alt="ALF Logo"
            width="96"
            height="96"
          >
        </figure>

        <div class="text-center">
          <h1 class="font-bold text-sm uppercase tracking-widest">
            Arellano Law Foundation
          </h1>
          <address class="text-xs tracking-wide">
            <p>Taft Ave, Cor. Menlo St. Pasay City</p>
          </address>
        </div>
      </header>
      
      <div class="space-y-0">
        <!-- Main Table -->
        <div class="grid grid-cols-[50px_60px_minmax(200px,1fr)_80px_80px] text-xs border-x border-t border-zinc-500 w-full">
          <!-- Header Row -->
          <div class="col-span-3 p-0.5 border-b border-r border-zinc-500 text-xs text-center font-bold uppercase tracking-widest bg-white h-6 leading-tight">
            Requisition Form
          </div>
          <div class="col-span-2 p-0.5 border-b border-zinc-500 text-xs font-semibold bg-white h-6 leading-tight">
            Date: {{ formatDate(request.request_date) }}
          </div>

          <!-- Column Headers -->
          <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Qty</div>
          <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Unit</div>
          <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Articles/Description</div>
          <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Unit Price</div>
          <div class="col-span-1 p-0.5 border-b border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Amount</div>

          <!-- Item Rows -->

          <template v-for="(detail, index) in request.details">
            <div v-if="index < 10" :key="detail.id" class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center hover:bg-zinc-50 h-5 leading-tight">
                {{ Number(detail.quantity) + Number(detail.released_quantity) }}
            </div>
            <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center hover:bg-zinc-50 h-5 leading-tight">
              {{ detail.unit }}
            </div>
            <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-r border-zinc-500 truncate px-1 hover:bg-zinc-50 h-5 leading-tight capitalize">
              {{ detail.item_description }} 
              <span v-if="request.status === 'released'" class="text-green-600">( Released: {{ Number(detail.quantity) + Number(detail.released_quantity) }})</span>
              <span v-else class="text-purple-600">( Released: {{ detail.released_quantity }})</span>
            </div>
            <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-right pr-1 hover:bg-zinc-50 h-5 leading-tight">
              -
            </div>
            <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-zinc-500 text-right pr-1 hover:bg-zinc-50 h-5 leading-tight">
              -
            </div>
          </template>

          <!-- Empty Rows -->
          <template v-for="index in Math.max(0, 10 - request.details.length)">
            <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center h-5 leading-tight">-</div>
            <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center h-5 leading-tight">-</div>
            <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 px-1 h-5 leading-tight">-</div>
            <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-right pr-1 h-5 leading-tight">-</div>
            <div class="col-span-1 p-0.5 border-b border-zinc-500 text-right pr-1 h-5 leading-tight">-</div>
          </template>
        </div>

        <!-- Purpose/Received Section -->
        <div class="grid grid-cols-5 text-xs border-x border-b border-zinc-500">
          <div class="col-span-3 p-2 border-r border-zinc-500 align-top">
            <div class="font-semibold">Purpose:</div>
            <div class="text-sm">{{ request.purpose }}</div>
          </div>
          <div class="col-span-2 p-2 h-20 align-top">
            <div class="font-semibold text-xs">Items Received by:</div>
            <div class="flex-1 flex flex-col justify-end">
              <div class="text-center mt-8 border-t border-zinc-500 mx-8">
                <div class="text-xs text-zinc-600 mt-1">Signature over printed name</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Signatures Section -->
        <div class="grid grid-cols-6 text-xs border-x border-b border-zinc-500">
          <div class="col-span-3 p-2 border-r border-zinc-500 align-top">
            <div class="font-semibold text-xs">Requested By:</div>
            <div class="flex-1 flex flex-col justify-end">
              <div class="relative mt-4 inline-block text-sm">
                <!-- <img
                  v-if="request.status != 'pending'"
                  src="/images/signatures/sample_signature.png"
                  alt="Signature"
                  class="w-[100px] absolute -top-6 left-1/2 -translate-x-1/2 pointer-events-none"
                /> -->
                <div class="text-xs text-center text-zinc-600 mt-1 uppercase" >
                  <p v-if="request.status != 'pending'">
                    {{ request.user.first_name }} {{ request.user.last_name }}
                  </p>
                  <p v-else>
                    n/a
                  </p>
                </div>
                <div class="text-center border-t border-zinc-500 mx-8">
                <div class="text-xs text-zinc-600 mt-1">Department Head</div>
              </div>
              </div>
            </div>
          </div>
          <div class="col-span-3 p-2 h-22 align-top">
            <div class="font-semibold text-xs">Approved:</div>
            <div class="flex-1 flex flex-col justify-end">
              <div class="text-center mt-8 border-t border-zinc-500 mx-8">
                <div class="text-xs text-zinc-600 mt-1 uppercase">Atty. Gabriel P. Dela PeÑa<br>
                  Executive Director
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
