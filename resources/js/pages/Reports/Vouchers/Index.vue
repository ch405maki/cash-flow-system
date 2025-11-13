<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Button } from '@/components/ui/button'
import { Eraser, Printer, Rocket, X, FileDown } from 'lucide-vue-next';
import { formatCurrency } from '@/lib/utils';
import { Label } from '@/components/ui/label'
import PageHeader from '@/components/PageHeader.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

// 📦 Export dependencies
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

const props = defineProps<{
  vouchers: Array<{
    id: number;
    voucher_no: string;
    voucher_date: string;
    payee: string;
    purpose: string;
    check_amount: number;
    type: string;
    details: Array<{
      id: number;
      amount: string;
      charging_tag: string;
      account?: {
        id: number;
        account_title: string;
      };
    }>;
  }>;
}>();

// Filters
const showAlert = ref(true)
const startDate = ref<string>('');
const endDate = ref<string>('');
const selectedType = ref<string>('all');

// Voucher type dropdown
const voucherType = computed(() => {
  const uniqueTypes = new Set<string>();
  props.vouchers.forEach(v => uniqueTypes.add(v.type));
  return Array.from(uniqueTypes).sort();
});

// Filtered vouchers
const filteredVouchers = computed(() => {
  return props.vouchers.filter(v => {
    const vDate = new Date(v.voucher_date);
    const start = startDate.value ? new Date(startDate.value) : null;
    const end = endDate.value ? new Date(endDate.value) : null;
    const dateInRange = (!start || vDate >= start) && (!end || vDate <= end);
    const typeMatch = selectedType.value === 'all' || v.type === selectedType.value;
    return dateInRange && typeMatch;
  });
});

// Export to Excel
function exportExcel() {
  const rows: any[] = [];

  filteredVouchers.value.forEach(v => {
    v.details.forEach((detail, idx) => {
      rows.push({
        "Voucher Date": idx === 0 ? formatDate(v.voucher_date) : "",
        "Voucher No": idx === 0 ? v.voucher_no : "",
        "Check Amount": idx === 0 ? v.check_amount : "",
        "Payee": idx === 0 ? v.payee : "",
        "Purpose": idx === 0 ? v.purpose : "",
        "Account": detail.account?.account_title || "Unspecified",
        "Detail Amount": detail.amount || 0,
        "Charging Tag": detail.charging_tag,
      });
    });
  });

  const worksheet = XLSX.utils.json_to_sheet(rows);
  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "Voucher Summary");
  const excelBuffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
  const blob = new Blob([excelBuffer], { type: "application/octet-stream" });

  const oldestMonth = getOldestMonth();
  const filename = oldestMonth ? `voucher_summary_${oldestMonth}.xlsx` : "voucher_summary.xlsx";

  saveAs(blob, filename);
}



// Export to PDF
function exportPdf() {
  const doc = new jsPDF({ orientation: "landscape" });
  const tableData: any[] = [];

  filteredVouchers.value.forEach(v => {
    v.details.forEach((detail, idx) => {
      tableData.push([
        idx === 0 ? formatDate(v.voucher_date) : "",
        idx === 0 ? v.voucher_no : "",
        idx === 0 ? formatCurrency(v.check_amount) : "",
        idx === 0 ? v.payee : "",
        idx === 0 ? v.purpose : "",
        detail.account?.account_title || "Unspecified",
        formatCurrency(detail.amount || 0),
        detail.charging_tag,
      ]);
    });
  });

  const oldestMonth = getOldestMonth();
  const title = oldestMonth ? `Voucher Summary - ${oldestMonth}` : "Voucher Summary";

  autoTable(doc, {
    head: [["Voucher Date", "Voucher No",  "Check Amount", "Payee", "Purpose", "Account", "Detail Amount", "Charging Tag"]],
    body: tableData,
    styles: { fontSize: 8 },
    headStyles: { fillColor: [99, 102, 241] },
    didDrawPage: () => {
      doc.setFontSize(12);
      doc.text(title, 14, 10);
    }
  });

  doc.save(`${title.replace(/\s+/g, "_").toLowerCase()}.pdf`);
}


function getOldestMonth(): string {
  if (!filteredVouchers.value.length) return "";

  const oldest = filteredVouchers.value.reduce((min, v) => {
    const vDate = new Date(v.voucher_date);
    return vDate < min ? vDate : min;
  }, new Date(filteredVouchers.value[0].voucher_date));

  return oldest.toLocaleDateString("en-US", { month: "short", year: "numeric" });
}


function formatDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' },
  { title: 'Voucher Summary', href: '/' },
];

function goToVoucher(id: number) {
  router.visit(`/vouchers/${id}`)
}
</script>

<template>
  <Head title="Voucher Summary" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <PageHeader 
          title="Voucher Summary" 
          subtitle="This is a collection of all Vouchers. Export data or click a voucher to view its details."
        />
        
        <div class="flex gap-2">
          <Button size="sm" variant="default" @click="exportExcel"><FileDown class="mr-1 h-4 w-4"/>Excel</Button>
          <Button size="sm" variant="secondary" @click="exportPdf"><FileDown class="mr-1 h-4 w-4"/>PDF</Button>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="col-span-3">
          <Label class="block text-sm font-medium mb-1">Start Date</Label>
          <Input type="date" v-model="startDate" class="h-8" />
        </div>
        <div class="col-span-3">
          <Label class="block text-sm font-medium mb-1">End Date</Label>
          <Input type="date" v-model="endDate" class="h-8" />
        </div>
        <div class="col-span-4">
          <Label class="block text-sm font-medium mb-1">Voucher Type</Label>
          <Select v-model="selectedType" class="h-8">
            <SelectTrigger class="h-8 w-full">
              <SelectValue placeholder="All Voucher Types" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Voucher Types</SelectItem>
              <SelectItem v-for="type in voucherType" :key="type" :value="type">{{ type }}</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="col-span-2 flex items-end">
          <Button variant="destructive" class="h-8" @click="() => { startDate=''; endDate=''; selectedType='all'; }">
            <Eraser class="mr-1 h-4 w-4"/>Clear
          </Button>
        </div>
      </div>

      <!-- Table -->
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-32">Voucher Date</TableHead>
            <TableHead class="w-32">Voucher No</TableHead>
            <TableHead class="w-32 text-right">Check Amount</TableHead>
            <TableHead class="w-48">Payee</TableHead>
            <TableHead class="w-64">Purpose</TableHead>
            <TableHead class="w-48">Account</TableHead>
            <TableHead class="w-32 text-right">Amount (₱)</TableHead>
            <TableHead class="w-40">Charging Tag</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-for="v in filteredVouchers" :key="v.id">
            <TableRow class="bg-muted/50 hover:bg-muted/70">
              <TableCell>{{ formatDate(v.voucher_date) }}</TableCell>
              <TableCell>
                <button class="hover:text-purple-700 hover:underline font-medium" @click.stop="goToVoucher(v.id)">
                  {{ v.voucher_no }}
                </button>
              </TableCell>
              <TableCell class="text-right font-medium">{{ formatCurrency(v.check_amount) }}</TableCell>
              <TableCell class="truncate" :title="v.payee">{{ v.payee }}</TableCell>
              <TableCell class="truncate" :title="v.purpose">{{ v.purpose }}</TableCell>
              <TableCell colspan="3" class="text-muted-foreground text-sm">
                {{ v.details.length }} account{{ v.details.length > 1 ? 's' : '' }}
              </TableCell>
            </TableRow>
            <TableRow v-for="detail in v.details" :key="detail.id" class="bg-muted/20">
              <TableCell></TableCell>
              <TableCell colspan="4"></TableCell>
              <TableCell class="text-sm">{{ detail.account?.account_title || 'Unspecified' }}</TableCell>
              <TableCell class="text-right text-sm font-medium">{{ formatCurrency(detail.amount || 0) }}</TableCell>
              <TableCell class="text-sm">{{ detail.charging_tag }}</TableCell>
            </TableRow>
          </template>
        </TableBody>
      </Table>

      <!-- Empty -->
      <div v-if="filteredVouchers.length === 0" class="text-center py-12 text-muted-foreground">
        <p>No vouchers found matching your criteria.</p>
        <Button variant="outline" class="mt-4" @click="() => { startDate=''; endDate=''; selectedType='all'; }">Clear filters</Button>
      </div>
    </div>
  </AppLayout>
</template>
