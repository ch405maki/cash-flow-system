<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { formatDate } from '@/lib/utils'
import { FileText, Wallet, ListChecks, SendHorizontal, Clock } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { computed } from 'vue'

const props = defineProps<{
    isDepartmentUser: boolean;
    userRole: string;
    username: string;
    totalRequests: number;
    totalreleased: number;
    pettyCashRequest: number;
    pettyCash: Record<string, any>[] | null
    pettyCashFund?: {
        fund_amount?: number
        fund_balance?: number
    } | null
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const hasItem = computed(() => props.pettyCash && Object.keys(props.pettyCash).length > 0)

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 space-y-4">
            <div>
                <h1 class="text-lg font-medium mb-2">
                    Bursar's Dashboard
                    <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
                </h1>
            </div>
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Pending Request</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <Clock class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ pettyCashRequest }}</div>
                        <p class="text-xs text-muted-foreground">Items awaiting to be released</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Released</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <SendHorizontal class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalreleased }}</div>
                        <p class="text-xs text-muted-foreground">Successfully released</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Request count</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <ListChecks  class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ totalRequests }}</div>
                        <p class="text-xs text-muted-foreground">Total created requests</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Fund</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <Wallet class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ pettyCashFund.fund_balance }}</div>
                        <p class="text-xs text-muted-foreground">Available fund balance</p>
                    </CardContent>
                </Card>
            </div>
            <Table v-if="hasItem">
                <TableCaption>A list of your petty cash.</TableCaption>
                <TableHeader>
                <TableRow>
                    <TableHead class="w-[150px]">PCV NO</TableHead>
                    <TableHead class="w-[100px]">Paid to</TableHead>
                    <TableHead class="w-[150px]">Requested By</TableHead>
                    <TableHead class="w-[100px]">Date</TableHead>
                    <TableHead class="w-[100px]">Status</TableHead>
                    <TableHead class="w-[200px]">Remarks</TableHead>
                </TableRow>
                </TableHeader>
                <TableBody>
                <TableRow @click="router.get(route('petty-cash.view', item.id))" v-for="item in pettyCash" :key="item.id">
                    <TableCell class="font-medium">
                        {{ item.pcv_no }}
                        </TableCell>
                        <TableCell class="capitalize">
                        {{ item.paid_to }}
                        </TableCell>
                        <TableCell class="capitalize">
                        {{ item.user?.first_name }} {{ item.user?.last_name }}
                        </TableCell>
                        <TableCell class="capitalize">
                        {{ formatDate(item.date) }}
                        </TableCell>
                        <TableCell class="capitalize">
                        {{ item.status }}
                        </TableCell>
                        <TableCell class="capitalize">
                        "{{ item.remarks }}"
                        </TableCell>
                </TableRow>
                </TableBody>
            </Table>

            <div
                v-else
                class="flex h-48 flex-col items-center justify-center rounded-xl border text-center"
            >
                <FileText class="h-8 w-8 text-muted-foreground" />
                <p class="mt-2 text-sm text-muted-foreground">No petty cash found</p>
                <p class="text-xs text-muted-foreground mb-4">
                List of your petty cash will appear here
                </p>
            </div>
            </div>
    </AppLayout>
</template>