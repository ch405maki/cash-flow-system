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
    title: 'Purchase Request',
    href: '/purchase-orders',
    icon: ShoppingCart,
  },
  {
    title: 'Vouchers',
    href: '/vouchers',
    icon: ReceiptText,
  },
];

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
  {
    title: 'Purchase Approval',
    href: '/purchase-orders',
    icon: ShoppingCart,
  },
];

// Property Custodian
const propertyNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Requests',
    href: '/request',
    icon: FileText,
  },
  {
    title: 'Requests To Order',
    href: '/request-to-order',
    icon: Package,
  },
  {
    title: 'For Approval Request',
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
        { title: 'Completed Request', href: '/reports/request-summary'},
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
        { title: 'To Receive', href: '/'},
        { title: 'Completed', href: '/'},
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
    title: 'Vouchers',
    href: '/vouchers',
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
        { title: 'Voucher Summary', href: '/reports/vouchers'},
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
            <NavMain :items="reportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'property_custodian'">
            <NavMain :items="propertyNavItems" group-label="Navigation"/>
            <NavMain :items="custodianReportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'purchasing'">
            <NavMain :items="purchasingNavItems" group-label="Navigation"/>
            <NavMain :items="reportItems" group-label="Reports" />
          </div>

          <div v-if="user?.role === 'staff' || user?.role === 'department_head'">
            <NavMain :items="staffNavItems" group-label="Navigation"/>
            <NavMain :items="staffRequestItems" group-label="Request"/>
          </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems"/>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
