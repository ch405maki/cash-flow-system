<!-- resources/js/components/signature/SignatureDialog.vue -->
<script setup lang="ts">
import { ref, watch } from 'vue'
import { useSignaturePad, type SignatureResult } from '@/composables/useSignaturePad'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'
import { Loader2, CheckCircle2, XCircle, PenLine, ShieldCheck, Eye } from 'lucide-vue-next'

const props = defineProps<{
  open: boolean
  requestNo: string
  signerName: string
}>()

const emit = defineEmits<{
  (e: 'update:open', val: boolean): void
  (e: 'confirmed', signature: SignatureResult, signerName: string): void
  (e: 'cancelled'): void
}>()

const { isConnected, isPadOpen, isSigning, error, padInfo, connect, startSigning, cancelSigning, disconnect } = useSignaturePad()

type DialogStep = 'connecting' | 'opening' | 'ready' | 'signing' | 'preview' | 'error'
const step = ref<DialogStep>('connecting')
const capturedSignature = ref<SignatureResult | null>(null)
const localError = ref('')
const showPreview = ref(true)
const editableSignerName = ref('')

watch(() => props.open, async (opened) => {
  if (!opened) {
    if (step.value === 'signing') {
      cancelSigning()
    }
    disconnect()
    return
  }
  
  resetState()
  await initPad()
})

const resetState = () => {
  step.value = 'connecting'
  capturedSignature.value = null
  localError.value = ''
  editableSignerName.value = props.signerName
}

const initPad = async () => {
  step.value = 'connecting'
  localError.value = ''
  
  try {
    await connect()
    step.value = 'ready'
  } catch (e: any) {
    console.error('Init failed:', e)
    localError.value = e.message || 'Could not connect to signature pad'
    step.value = 'error'
  }
}

const beginSigning = async () => {
  step.value = 'signing'
  localError.value = ''
  
  try {
    const promptText = `Release request ${props.requestNo}\nSigner: ${editableSignerName.value}`
    const result = await startSigning(promptText)
    capturedSignature.value = result
    step.value = 'preview'
  } catch (e: any) {
    console.error('Signing failed:', e)
    if (e.message === 'Cancelled') {
      step.value = 'ready'
    } else {
      localError.value = e.message || 'Signature capture failed'
      step.value = 'error'
    }
  }
}

const confirmRelease = () => {
  if (!capturedSignature.value) {
    localError.value = 'No signature captured'
    step.value = 'error'
    return
  }
  emit('confirmed', capturedSignature.value, editableSignerName.value)
  closeDialog()
}

const cancel = () => {
  if (step.value === 'signing') {
    cancelSigning()
  }
  closeDialog()
}

const closeDialog = () => {
  disconnect()
  emit('update:open', false)
  emit('cancelled')
}
</script>

<template>
  <Dialog :open="open" @update:open="cancel">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <ShieldCheck class="w-5 h-5 text-primary" />
          Authorize Release
        </DialogTitle>
        <DialogDescription>
          Request <span class="font-mono font-medium">{{ requestNo }}</span> —
          signing as <span class="font-medium">{{ signerName }}</span>
        </DialogDescription>
      </DialogHeader>

      <div class="py-6 flex flex-col items-center gap-4">
        <!-- Connecting State -->
        <div v-if="step === 'connecting'" class="flex flex-col items-center gap-3">
          <Loader2 class="w-10 h-10 animate-spin text-muted-foreground" />
          <p class="text-sm text-muted-foreground">Connecting to signature pad...</p>
          <p class="text-xs text-muted-foreground">Make sure signotec service is running</p>
        </div>

        <!-- Ready State -->
        <div v-else-if="step === 'ready'" class="flex flex-col items-center gap-4">
          <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center">
            <PenLine class="w-8 h-8 text-primary" />
          </div>
          <div class="text-center">
            <p class="font-medium">Pad ready</p>
            <p v-if="padInfo" class="text-sm text-muted-foreground mt-1">
              {{ padInfo.padType || 'Sigma USB' }} · Ready
            </p>
          </div>
          <div class="w-full max-w-xs">
            <label class="text-xs text-muted-foreground">Signer Name</label>
            <Input 
              v-model="editableSignerName" 
              class="mt-1"
              placeholder="Enter signer name"
            />
          </div>
          <p class="text-sm text-center text-muted-foreground px-4">
            Click <span class="font-medium">Start Signing</span> then write your signature
            on the physical pad to authorize this release.
          </p>
        </div>

        <!-- Signing State -->
        <div v-else-if="step === 'signing'" class="flex flex-col items-center gap-3">
          <div class="relative">
            <div class="w-16 h-16 rounded-full border-4 border-primary/20 border-t-primary animate-spin" />
            <PenLine class="absolute inset-0 m-auto w-6 h-6 text-primary" />
          </div>
          <p class="font-medium">Waiting for signature…</p>
          <p class="text-sm text-muted-foreground text-center">
            Please sign on the pad and press the confirm button.
          </p>
          <p class="text-xs text-muted-foreground">The signature pad screen will guide you</p>
        </div>

        <!-- Preview State -->
        <div v-else-if="step === 'preview'" class="flex flex-col items-center gap-4 w-full">
          <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400 font-medium">
            <CheckCircle2 class="w-4 h-4" />
            Signature captured
          </div>
          
          <!-- Signature Preview -->
          <div class="w-full border rounded-lg overflow-hidden bg-white dark:bg-zinc-900 p-4">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm font-medium">Signature Preview</span>
              <Button variant="ghost" size="sm" @click="showPreview = !showPreview">
                <Eye class="w-4 h-4 mr-1" />
                {{ showPreview ? 'Hide' : 'Show' }}
              </Button>
            </div>
            <div v-if="showPreview && capturedSignature?.imageData" class="border rounded bg-gray-50 p-2">
              <img
                :src="`data:image/png;base64,${capturedSignature.imageData}`"
                alt="Captured signature"
                class="w-full h-auto max-h-40 object-contain"
              />
            </div>
            <div v-else class="text-center text-sm text-muted-foreground py-4">
              Click "Show" to preview signature
            </div>
            <p class="text-xs text-muted-foreground text-center mt-3">
              Signed by: <span class="font-medium">{{ signerName }}</span>
            </p>
            <p class="text-xs text-muted-foreground text-center">
              Date & Time: {{ new Date().toLocaleString() }}
            </p>
          </div>
          
          <p class="text-xs text-muted-foreground text-center">
            Review your signature above. Click <span class="font-medium">Confirm & Release</span> to proceed
            or <span class="font-medium">Re-sign</span> to redo it.
          </p>
        </div>

        <!-- Error State -->
        <div v-else-if="step === 'error'" class="flex flex-col items-center gap-3">
          <XCircle class="w-10 h-10 text-destructive" />
          <p class="font-medium text-destructive">Connection failed</p>
          <p class="text-sm text-muted-foreground text-center px-4">{{ localError || error }}</p>
          <div class="text-xs text-muted-foreground bg-muted rounded-md p-3 w-full space-y-1">
            <p class="font-medium mb-1">Troubleshooting:</p>
            <p>• Make sure signotec service is running</p>
            <p>• Check pad is connected via USB</p>
            <p>• Verify drivers are installed</p>
          </div>
        </div>
      </div>

      <DialogFooter class="gap-2 sm:gap-2">
        <template v-if="step === 'error'">
          <Button variant="outline" @click="closeDialog">Cancel</Button>
          <Button @click="initPad">Retry Connection</Button>
        </template>

        <template v-else-if="step === 'ready'">
          <Button variant="outline" @click="closeDialog">Cancel</Button>
          <Button @click="beginSigning">
            <PenLine class="w-4 h-4 mr-2" />
            Start Signing
          </Button>
        </template>

        <template v-else-if="step === 'signing'">
          <Button variant="outline" @click="cancelSigning(); step = 'ready'">
            Cancel Signing
          </Button>
        </template>

        <template v-else-if="step === 'preview'">
          <Button variant="outline" @click="step = 'ready'">Re-sign</Button>
          <Button @click="confirmRelease">
            <CheckCircle2 class="w-4 h-4 mr-2" />
            Confirm &amp; Release
          </Button>
        </template>

        <template v-else>
          <Button variant="outline" @click="closeDialog">Cancel</Button>
        </template>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>