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
  SquarePen,
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
  Users, 
  ShieldCheck,
  Building2,
  PencilLine,
  Logs 
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
    title: 'Request For Purchase',
    href: '/for-approval',
    icon: FileCheck2,
  },
  {
    title: 'Canvas Approval',
    href: '/canvas',
    icon: ListCollapse,
  },
  {
    title: 'P. O. Approval',
    href: '/purchase-orders',
    icon: ShoppingCart,
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
        title: 'Submitted', 
        href: '/canvas/approval?status=submitted',
      },
      { 
        title: 'Approved', 
        href: '/canvas/approval?status=approved',
      },
      { 
        title: 'P. O. Created', 
        href: '/canvas/approval?status=poCreated',
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
      { title: 'For EOD Approval', href: '/purchase-orders?status=forEOD' },
      { title: 'P. O. Status', href: '/purchase-orders?status=approved' },
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
    title: 'Released Orders',
    href: '/released-order',
    icon: Package,
  },
];

const custodianApprovalItems: NavItem[] = [
  {
    title: 'Create',
    href: '/request-to-order',
    icon: Package,
  },
  {
    title: 'For EOD Approval',
    href: '/for-approval',
    icon: ClipboardCheck,
  },
  {
    title: 'P. O. Status',
    href: '/to-order/on-process',
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
  {
    title: 'Create Request',
    href: '/request/create',
    icon: SquarePen,
  },
];

const staffRequestItems = ref<DropdownNavItem[]>([
    {
        title: 'Request',
        href: '/reports',
        icon: FileText,
        isOpen: false,
        children: [
        { title: 'Create', href: '/request/create'},
        { title: 'Pending', href: '/request'},
        { title: 'On Process', href: '/request/to-receive'},
        { title: 'Completed', href: '/request/released'},
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
    title: 'For Check',
    href: '/approved-voucher',
    icon: ReceiptText,
  },
];

const accountingAuditNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Purchase Canvas',
    href: '/canvas',
    icon: ListCollapse,
  },
  {
    title: 'Vouchers',
    href: '/voucher-approval',
    icon: ReceiptText,
  },
  {
    title: 'Petty Cash',
    href: '/audit/petty-cash',
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
        { title: 'Released Request Summary', href: '/reports/request-released'},
        { title: 'Petty Cash Summary', href: '/reports/petty-cash'},
        { title: 'Purchase Order Summary', href: '/reports/po-summary'},
        { title: 'Voucher Summary', href: '/reports/voucher-summary'},
        ],
    },
  ]);

const bursarReportItems = ref<DropdownNavItem[]>([
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart3,
        isOpen: false,
        children: [
        { title: 'Petty Cash Summary', href: '/reports/petty-cash'},
        ],
    },
  ]);

const purchasingReportItems = ref<DropdownNavItem[]>([
    {
        title: 'Reports',
        href: '/reports',
        icon: BarChart3,
        isOpen: false,
        children: [
        { title: 'Purchase Order Summary', href: '/reports/po-summary'},
        ],
    },
  ]);

const footerNavItems: NavItem[] = [
  {
    title: 'Settings',
    href: '/settings/password',
    icon: Settings,
  },
];

const adminNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Users',
    href: '/configuration/users',
    icon: Users,
  },
  {
    title: 'Profile Pictures',
    href: '/configuration/profile-pictures',
    icon: Users,
  },
  {
    title: 'User Access',
    href: '/configuration/users-access',
    icon: ShieldCheck,
  },
  {
    title: 'Departments',
    href: '/configuration/departments',
    icon: Building2,
  },
  {
    title: 'Signatory',
    href: '/configuration/signatory',
    icon: PencilLine,
  },
  {
    title: 'Accounts',
    href: '/configuration/account',
    icon: FileText,
  },
  {
    title: 'Petty Cash Fund',
    href: '/petty-cash/fund',
    icon: FileText,
  },
  {
    title: 'Logs',
    href: '/logs',
    icon: Logs ,
  },
];

// Pettycash
const pettyCashNavItems: NavItem[] = [
  {
    title: 'Petty Cash',
    href: '/petty-cash',
    icon: SquarePen,
  },
];

const bursarNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Petty Cash',
    href: '/bursar/petty-cash',
    icon: SquarePen,
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
          <div v-if="user?.role === 'admin'">
            <NavMain :items="adminNavItems" group-label="Configuration"/>
          </div>

          <div v-if="user?.role === 'executive_director'">
            <NavMain :items="executiveMainItems" group-label="Navigation"/>
            <NavMain :items="executiveApprovalItems" group-label="For Approval"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain :items="pettyCashNavItems" group-label="Petty Cash"/>
            </div>
            <NavMain :items="reportItems" group-label="Reports" />
          </div>
          
          <div v-if="user?.role === 'accounting'">
            <NavMain :items="accountingNavItems" group-label="Navigation"/>
            <NavMain :items="accountingCheckNavItems" group-label="Check"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain :items="pettyCashNavItems" group-label="Petty Cash"/>
            </div>
            <NavMain :items="staffRequestItems" group-label="Request"/>
            <NavMain :items="reportItems" group-label="Reports" />
          </div>
          
          <div v-if="user?.role === 'audit'">
            <NavMain :items="accountingAuditNavItems" group-label="Audit"/>
            <NavMain :items="reportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'bursar'">
            <NavMain :items="bursarNavItems" group-label="Petty Cash"/>
            <NavMain :items="staffRequestItems" group-label="Request"/>
            <NavMain :items="bursarReportItems" group-label="Reports"/>
          </div>

          <div v-if="user?.role === 'property_custodian'">
            <NavMain :items="custodianNavItems" group-label="Navigation"/>
            <NavMain :items="custodianApprovalItems" group-label="Request for Purchase"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain :items="pettyCashNavItems" group-label="Petty Cash"/>
            </div>
            <NavMain :items="custodianReportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'purchasing'">
            <NavMain :items="purchasingNavItems" group-label="Navigation"/>
            <NavMain :items="purchasingCanvasNavItems" group-label="Transaction"/>
            <NavMain :items="purchasingPONavItems"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain :items="pettyCashNavItems" group-label="Petty Cash"/>
            </div>
            <NavMain :items="purchasingReportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'staff' || user?.role === 'department_head'">
            <NavMain :items="staffNavItems" group-label="Navigation"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain :items="pettyCashNavItems" group-label="Petty Cash"/>
            </div>
            <NavMain :items="staffRequestItems" group-label="Request"/>
          </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems"/>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
