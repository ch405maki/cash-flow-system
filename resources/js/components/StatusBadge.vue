<script setup lang="ts">
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'
import {
  Clock,
  CheckCircle2,
  ShoppingCart,
  XCircle,
  FileEdit,
  Eye,
  CheckCheck,
  Ban,
  ClipboardCheck,
  PackageCheck,
  PackageOpen,
  FileSearch,
  FolderOpen, 
  Send,
  Receipt,
  FileText,
  Undo2,
  FileUp,
  Search,
  type LucideIcon
} from 'lucide-vue-next'

interface StatusConfig {
  color: string
  borderColor: string
  label: string
  icon: LucideIcon
}

const props = defineProps<{
  status: string
  showIcon?: boolean
  size?: 'sm' | 'md' | 'lg'
  class?: string
}>()

const statusConfig = computed<StatusConfig>(() => {
  const statusMap: Record<string, StatusConfig> = {
    'audited': {
      color: 'bg-green-50 text-green-700 hover:bg-green-100',
      borderColor: 'border-green-300',
      label: 'Audited',
      icon: CheckCircle2
    },
    'foraudit': {
      color: 'bg-amber-50 text-amber-700 hover:bg-amber-100',
      borderColor: 'border-amber-300',
      label: 'For Audit',
      icon: Search
    },
    'for liquidation': {
      color: 'bg-amber-50 text-amber-700 hover:bg-amber-100',
      borderColor: 'border-amber-300',
      label: 'For Liquidation',
      icon: Receipt
    },
    'approved liquidation': {
      color: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
      borderColor: 'border-emerald-300',
      label: 'Approved Liquidation',
      icon: CheckCircle2
    },
    'returned': {
      color: 'bg-orange-600 text-white hover:bg-orange-700',
      borderColor: 'border-orange-700',
      label: 'Returned',
      icon: Undo2
    },
    'return': {
      color: 'bg-orange-600 text-white hover:bg-orange-700',
      borderColor: 'border-orange-700',
      label: 'Returned',
      icon: Undo2
    },
    'requested': {
      color: 'bg-blue-50 text-blue-700 hover:bg-blue-100',
      borderColor: 'border-blue-300',
      label: 'Requested',
      icon: FileUp
    },
    'pocreated': {
      color: 'bg-orange-50 text-orange-700 hover:bg-orange-100',
      borderColor: 'border-orange-300',
      label: 'PO Created',
      icon: FileText
    },
    'forcheck': {
      color: 'bg-lime-50 text-lime-700 hover:bg-lime-100',
      borderColor: 'border-lime-300',
      label: 'For Check',
      icon: ClipboardCheck
    },
    'pending': {
      color: 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100',
      borderColor: 'border-yellow-300',
      label: 'Pending',
      icon: Clock
    },
    'approved': {
      color: 'bg-green-50 text-green-700 hover:bg-green-100',
      borderColor: 'border-green-300',
      label: 'Approved',
      icon: CheckCircle2
    },
    'for_approval': {
      color: 'bg-orange-50 text-orange-700 hover:bg-orange-100',
      borderColor: 'border-orange-300',
      label: 'For Approval',
      icon: FileSearch
    },
    'forapproval': {
      color: 'bg-orange-50 text-orange-700 hover:bg-orange-100',
      borderColor: 'border-orange-300',
      label: 'For Approval',
      icon: FileSearch
    },
    'to_order': {
      color: 'bg-blue-50 text-blue-700 hover:bg-blue-100',
      borderColor: 'border-blue-300',
      label: 'To Order',
      icon: ShoppingCart
    },
    'toorder': { 
      color: 'bg-blue-50 text-blue-700 hover:bg-blue-100',
      borderColor: 'border-blue-300',
      label: 'To Order',
      icon: ShoppingCart
    },
    'rejected': {
      color: 'bg-red-50 text-red-700 hover:bg-red-100',
      borderColor: 'border-red-300',
      label: 'Rejected',
      icon: XCircle
    },
    'draft': {
      color: 'bg-gray-50 text-gray-700 hover:bg-gray-100',
      borderColor: 'border-gray-300',
      label: 'Draft',
      icon: FileEdit
    },
    'in_review': {
      color: 'bg-purple-50 text-purple-700 hover:bg-purple-100',
      borderColor: 'border-purple-300',
      label: 'In Review',
      icon: Eye
    },
    'inreview': { 
      color: 'bg-purple-50 text-purple-700 hover:bg-purple-100',
      borderColor: 'border-purple-300',
      label: 'In Review',
      icon: Eye
    },
    'completed': {
      color: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
      borderColor: 'border-emerald-300',
      label: 'Completed',
      icon: CheckCheck
    },
    'cancelled': {
      color: 'bg-rose-50 text-rose-700 hover:bg-rose-100',
      borderColor: 'border-rose-300',
      label: 'Cancelled',
      icon: Ban
    },
    'propertycustodian': {
      color: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
      borderColor: 'border-indigo-300',
      label: 'Property Custodian',
      icon: ClipboardCheck
    },
    'with_property_custodian': {
      color: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
      borderColor: 'border-indigo-300',
      label: 'With Property Custodian',
      icon: ClipboardCheck
    },
    'withpropertycustodian': { 
      color: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100',
      borderColor: 'border-indigo-300',
      label: 'With Property Custodian',
      icon: ClipboardCheck
    },
    'released': {
      color: 'bg-teal-50 text-teal-700 hover:bg-teal-100',
      borderColor: 'border-teal-300',
      label: 'Released',
      icon: PackageCheck
    },
    'partially_released': { 
      color: 'bg-cyan-50 text-cyan-700 hover:bg-cyan-100',
      borderColor: 'border-cyan-300',
      label: 'Partially Released',
      icon: PackageOpen
    },
    'partiallyreleased': { 
      color: 'bg-cyan-50 text-cyan-700 hover:bg-cyan-100',
      borderColor: 'border-cyan-300',
      label: 'Partially Released',
      icon: PackageOpen
    },
    'foreod': {
      color: 'bg-amber-50 text-amber-700 hover:bg-amber-100',
      borderColor: 'border-amber-300',
      label: 'For EO.D',
      icon: FolderOpen
    },
    'forpo': {
      color: 'bg-violet-50 text-violet-700 hover:bg-violet-100',
      borderColor: 'border-violet-300',
      label: 'For P.O',
      icon: FileEdit
    },
    'submitted': {
      color: 'bg-sky-50 text-sky-700 hover:bg-sky-100',
      borderColor: 'border-sky-300',
      label: 'Submitted',
      icon: Send
    },
    'vouchercreated': {
      color: 'bg-fuchsia-50 text-fuchsia-700 hover:bg-fuchsia-100',
      borderColor: 'border-fuchsia-300',
      label: 'Voucher Created',
      icon: Receipt
    }
  }

  const key = props.status?.toLowerCase() || ''
  return statusMap[key] || {
    color: 'bg-gray-50 text-gray-700 hover:bg-gray-100',
    borderColor: 'border-gray-300',
    label: props.status?.charAt(0).toUpperCase() + props.status?.slice(1) || 'Unknown',
    icon: Clock
  }
})

const sizeClasses = computed(() => {
  switch(props.size) {
    case 'sm':
      return 'text-[9px] px-1 py-[0px] gap-0.5'
    case 'lg':
      return 'text-xs px-2 py-[0px] gap-1'
    default:
      return 'text-[10px] px-1.5 py-[0px] gap-1'
  }
})

const iconSize = computed(() => {
  switch(props.size) {
    case 'sm': return 'h-2 w-2'
    case 'lg': return 'h-3.5 w-3.5'
    default: return 'h-2.5 w-2.5'
  }
})
</script>

<template>
  <Badge 
    variant="outline" 
    :class="[
      statusConfig.color,
      statusConfig.borderColor,
      sizeClasses,
      'inline-flex items-center font-medium capitalize border shadow-sm',
      props.class
    ]"
  >
    <component 
      :is="statusConfig.icon" 
      v-if="showIcon"
      :class="[iconSize, 'shrink-0']"
    />
    <span>{{ statusConfig.label }}</span>
  </Badge>
</template>