<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectLabel,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const props = defineProps({
    vouchers: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">

         <Card>
        <CardHeader>
            <CardTitle>Create Voucher</CardTitle>
        </CardHeader>
        <CardContent>
            <form>
            <div className="grid gap-4">
                <div className="grid grid-cols-3 items-center gap-4">
                <Label htmlFor="type" className="text-right">
                    Voucher Type
                </Label>
                <Select>
                    <SelectTrigger id="type" className="col-span-2">
                    <SelectValue placeholder="Select" />
                    </SelectTrigger>
                    <SelectContent position="popper">
                    {/* Add your options here */}
                    </SelectContent>
                </Select>
                </div>

                <div className="grid grid-cols-3 items-center gap-4">
                <Label htmlFor="check_amount" className="text-right">
                    Check Amount
                </Label>
                <Input id="check_amount" className="col-span-2" />
                </div>

                <div className="grid grid-cols-3 items-center gap-4">
                <Label htmlFor="payee" className="text-right">
                    Payee
                </Label>
                <Input id="payee" className="col-span-2" />
                </div>

                <div className="grid grid-cols-3 items-center gap-4">
                <Label htmlFor="check_pay_to" className="text-right">
                    Check Pay To
                </Label>
                <Input id="check_pay_to" className="col-span-2" />
                </div>

                <div className="grid grid-cols-3 items-center gap-4">
                <Label htmlFor="purpose" className="text-right">
                    Purpose
                </Label>
                <Input id="purpose" className="col-span-2" />
                </div>
            </div>
            </form>
        </CardContent>
        <CardFooter className="flex justify-between px-6 pb-6">
            <Button variant="outline">Cancel</Button>
            <Button>Deploy</Button>
        </CardFooter>
        </Card>

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
            {{ vouchers }}
            </div>
        </div>

       
    </AppLayout>
</template>
