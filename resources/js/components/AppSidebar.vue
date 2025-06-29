<script setup lang="ts">
import { ref } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  ListCollapse,
  LayoutDashboard,
  FileText,
  ClipboardCheck,
  FileCheck2,
  ShoppingCart,
  ReceiptText,
  BarChart3,
  Settings,
  Package,
  ListTodo,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

interface DropdownNavItem extends NavItem {
    children?: NavItem[];
    isOpen?: boolean;
}

const user = usePage().props.auth.user;

// Executive Nav
const executiveMainItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
];
const executiveApprovalItems: NavItem[] = [
  {
    title: 'Order Request',
    href: '/for-approval',
    icon: FileCheck2,
  },
  {
    title: 'Purchase Canvas',
    href: '/canvas',
    icon: ListCollapse,
  },
  {
    title: 'Purchase Request',
    href: '/purchase-orders',
    icon: ShoppingCart,
  },
  {
    title: 'Vouchers',
    href: '/voucher-approval',
    icon: ReceiptText,
  },
];

// Purchasing Nav
const purchasingNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Approved Request',
    href: '/approved-request',
    icon: FileCheck2,
  },
];
const purchasingCanvasNavItems = ref<DropdownNavItem[]>([
  {
    title: 'Canvas',
    href: '#',
    icon: ShoppingCart,
    isOpen: false,
    children: [
      { title: 'Pending', href: '/canvas' },
      { 
        title: 'For Approval', 
        href: '/canvas/approval?status=forEOD',
      },
      { 
        title: 'Approved', 
        href: '/canvas/approval?status=approved',
      },
      { 
        title: 'P. O. Created', 
        href: '/canvas/approval?status=poCreated',
      },
      { 
        title: 'Rejected', 
        href: '/canvas/approval?status=rejected',
      },
    ],
  },
]);
const purchasingPONavItems = ref<DropdownNavItem[]>([
  {
    title: 'Purchase Order',
    href: '#',
    icon: ShoppingCart,
    isOpen: false,
    children: [
      { title: 'Draft', href: '/purchase-orders?status=draft' },
      { title: 'For Approval', href: '/purchase-orders?status=forEOD' },
      { title: 'Approved', href: '/purchase-orders?status=approved' },
      { title: 'Rejected', href: '/purchase-orders?status=rejected' },
    ],
  },
]);

// Property Custodian
const custodianNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Department Requests',
    href: '/request',
    icon: FileText,
  },
  {
    title: 'Requests To Order',
    href: '/request-to-order',
    icon: Package,
  },
];
const custodianApprovalItems: NavItem[] = [
  {
    title: 'For Approval',
    href: '/for-approval',
    icon: ClipboardCheck,
  },
];
const custodianReportItems = ref<DropdownNavItem[]>([
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart3,
        isOpen: false,
        children: [
          { title: 'Approved Request', href: '/reports/request?status=forPO'},
          { title: 'Completed Request', href: '/reports/request?status=released'},
          { title: 'Rejected Request', href: '/reports/request?status=rejected'},
        ],
    },
  ]);

// Staff
const staffNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
];
const staffRequestItems = ref<DropdownNavItem[]>([
    {
        title: 'Request',
        href: '/reports',
        icon: FileText,
        isOpen: false,
        children: [
        { title: 'Request', href: '/request'},
        { title: 'To Receive', href: '/request/to-receive'},
        { title: 'Completed', href: '#'},
        { title: 'Rejected', href: '/request/rejected'},
        ],
    },
  ]);

// Accounting
const accountingNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'For Voucher',
    href: '/for-voucher',
    icon: ReceiptText,
  },
  {
    title: 'Vouchers',
    href: '/vouchers',
    icon: ReceiptText,
  },
  {
    title: 'Voucher For Approval',
    href: '/voucher-approval',
    icon: ReceiptText,
  },
];
const accountingCheckNavItems: NavItem[] = [
  {
    title: 'For Check Releasing',
    href: '/approved-voucher',
    icon: ReceiptText,
  },
];

const reportItems = ref<DropdownNavItem[]>([
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart3,
        isOpen: false,
        children: [
        { title: 'Request Summary', href: '/reports/request-summary'},
        { title: 'Purchase Order Summary', href: '/reports/po-summary'},
        { title: 'Voucher Summary', href: '/reports/voucher-summary'},
        ],
    },
  ]);

const footerNavItems: NavItem[] = [
  {
    title: 'Configurations',
    href: '/configuration/users',
    icon: Settings,
  },
];
</script>


<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
          <div v-if="user?.role === 'executive_director'">
            <NavMain :items="executiveMainItems" group-label="Navigation"/>
            <NavMain :items="executiveApprovalItems" group-label="For Approval"/>
            <NavMain :items="reportItems" group-label="Reports" />
          </div>
          
          <div v-if="user?.role === 'accounting'">
            <NavMain :items="accountingNavItems" group-label="Navigation"/>
            <NavMain :items="accountingCheckNavItems" group-label="Check"/>
            <NavMain :items="reportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'property_custodian'">
            <NavMain :items="custodianNavItems" group-label="Navigation"/>
            <NavMain :items="custodianApprovalItems" group-label="Order Request"/>
            <NavMain :items="custodianReportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'purchasing'">
            <NavMain :items="purchasingNavItems" group-label="Navigation"/>
            <NavMain :items="purchasingCanvasNavItems"/>
            <NavMain :items="purchasingPONavItems"/>
            <NavMain :items="reportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'staff' || user?.role === 'department_head'">
            <NavMain :items="staffNavItems" group-label="Navigation"/>
            <NavMain :items="staffRequestItems" group-label="Requests"/>
          </div>

        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems"/>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
