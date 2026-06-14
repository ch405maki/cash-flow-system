<script setup lang="ts">
import { BadgeCheck, Send, Rocket, Printer, ArrowLeft, History } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'
import TimestampSheet from './TimestampSheet.vue'
import { ref } from 'vue'

const props = defineProps<{
  order: any
  authUser: any
  processing: boolean
  showApproveModal: boolean
  showForEodModal: boolean
}>()

const emit = defineEmits<{
  approve: [password: string]
  forEod: [password: string]
  receiveItems: []
  print: []
  back: []
  'update:showApproveModal': [value: boolean]
  'update:showForEodModal': [value: boolean]
}>()

const password = ref('')

function submitApproval() {
  if (!password.value) return
  emit('approve', password.value)
  password.value = ''
}

function submitForEOD() {
  if (!password.value) return
  emit('forEod', password.value)
  password.value = ''
}
</script>

<template>
  <div class="space-x-2 flex items-center">
    <div v-if="authUser?.role == 'executive_director' && order.status !== 'forPO'" class="space-x-2 flex items-center">
      <Dialog :open="showApproveModal" @update:open="emit('update:showApproveModal', $event)">
        <DialogTrigger as-child>
          <Button variant="default" size="sm"><BadgeCheck />Approve For Purchasing</Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Password Verification</DialogTitle>
            <DialogDescription>Enter your password to approve this request</DialogDescription>
          </DialogHeader>
          <div class="space-y-2">
            <Label for="approve-password">Password</Label>
            <Input id="approve-password" v-model="password" type="password" placeholder="Enter password" class="w-full" />
          </div>
          <DialogFooter>
            <Button @click="submitApproval" :disabled="!password || processing">
              <span v-if="processing">Processing...</span>
              <span v-else>Confirm Approval</span>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>

    <div v-if="authUser?.role === 'property_custodian' && authUser?.access == 3 && order.status === 'pending'" class="space-x-2 flex items-center">
      <Dialog :open="showForEodModal" @update:open="emit('update:showForEodModal', $event)">
        <DialogTrigger as-child>
          <Button variant="default" size="sm"><Send />For EOD Approval</Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Password Verification</DialogTitle>
            <DialogDescription>Enter your password to approve this request</DialogDescription>
          </DialogHeader>
          <div class="space-y-2">
            <Label for="approve-password">Password</Label>
            <Input id="approve-password" v-model="password" type="password" placeholder="Enter password" class="w-full" />
          </div>
          <DialogFooter>
            <Button @click="submitForEOD" :disabled="!password || processing">
              <span v-if="processing">Processing...</span>
              <span v-else>Confirm</span>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>

    <div v-if="authUser?.role === 'property_custodian' && order.status === 'forPO'">
      <Button size="sm" @click="emit('receiveItems')"><Rocket />Receive Items</Button>
    </div>

    <Sheet>
      <SheetTrigger>
        <Button variant="outline" size="sm"><History />Time Stamp</Button>
      </SheetTrigger>
      <SheetContent>
        <SheetHeader>
          <SheetTitle>Time Stamp</SheetTitle>
          <SheetDescription><TimestampSheet :order="order" /></SheetDescription>
        </SheetHeader>
      </SheetContent>
    </Sheet>

    <Button size="sm" variant="default" @click="emit('print')"> <Printer />Print List</Button>
    <Button size="sm" @click="emit('back')" variant="outline"> <ArrowLeft />Back</Button>
  </div>
</template>
