import * as XLSX from "xlsx";
import { saveAs } from "file-saver";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

/* =========================================================
   TYPES
========================================================= */

export interface VoucherDetail {
  id: number;
  amount: number | string;
  charging_tag: string;
  account?: { id: number; account_title: string };
}

export interface Voucher {
  id: number;
  voucher_no: string;
  voucher_date: string;
  payee: string;
  purpose: string;
  check_amount: number;
  type: string;
  details: VoucherDetail[];
}

/* =========================================================
   UTILS
========================================================= */

export function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "2-digit",
  });
}

function formatPhpAmount(value: number | string) {
  return new Intl.NumberFormat("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(Number(value || 0));
}

export function getVoucherDateRange(vouchers: Voucher[]): string {
  if (!vouchers.length) return "";

  const dates = vouchers.map((v) => new Date(v.voucher_date));
  const start = new Date(Math.min(...dates.map((d) => d.getTime())));
  const end = new Date(Math.max(...dates.map((d) => d.getTime())));

  const fmt = (d: Date) => d.toLocaleDateString("en-US", { month: "short", year: "numeric" });

  return fmt(start) === fmt(end) ? fmt(start) : `${fmt(start)} – ${fmt(end)}`;
}

/* =========================================================
   EXCEL EXPORT (MERGED HEADER)
========================================================= */

export function exportVoucherSummaryExcel(vouchers: Voucher[]) {
  const rows: any[] = [];

  vouchers.forEach((v) => {
    v.details.forEach((detail, idx) => {
      rows.push({
        "Voucher Date": idx === 0 ? formatDate(v.voucher_date) : "",
        "Voucher No": idx === 0 ? v.voucher_no : "",
        "Check Amount": idx === 0 ? formatPhpAmount(v.check_amount) : "",
        Payee: idx === 0 ? v.payee : "",
        Purpose: idx === 0 ? v.purpose : "",
        Account: detail.account?.account_title || "Unspecified",
        "Detail Amount": formatPhpAmount(detail.amount || 0),
        "Charging Tag": detail.charging_tag,
      });
    });
  });

  const range = getVoucherDateRange(vouchers);

  // Header rows (add empty cells to merge)
  const headerRows = [
    ["ARELLANO LAW FOUNDATION", "", "", "", "", "", "", ""],
    ["Taft Ave, Cor. Menlo St. Pasay City · Tel. No. 404-3089 to 93", "", "", "", "", "", "", ""],
    [`VOUCHER SUMMARY (${range})`, "", "", "", "", "", "", ""],
    [""],
  ];

  const worksheet = XLSX.utils.json_to_sheet(rows, { origin: "A5" });
  XLSX.utils.sheet_add_aoa(worksheet, headerRows, { origin: "A1" });

  // Merge cells for header rows
  worksheet["!merges"] = [
    { s: { r: 0, c: 0 }, e: { r: 0, c: 7 } },
    { s: { r: 1, c: 0 }, e: { r: 1, c: 7 } },
    { s: { r: 2, c: 0 }, e: { r: 2, c: 7 } },
  ];

  // Optional: row heights
  worksheet["!rows"] = [
    { hpt: 20 },
    { hpt: 16 },
    { hpt: 16 },
    { hpt: 8 },
  ];

  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, "Voucher Summary");

  const buffer = XLSX.write(workbook, { bookType: "xlsx", type: "array" });
  const blob = new Blob([buffer], { type: "application/octet-stream" });

  saveAs(blob, `voucher_summary_${range.replace(/\s+/g, "_")}.xlsx`);
}

/* =========================================================
   PDF EXPORT (UNCHANGED)
========================================================= */

export async function exportVoucherSummaryPdf(vouchers: Voucher[]) {
  const doc = new jsPDF({ orientation: "landscape" });
  const range = getVoucherDateRange(vouchers);

  // Header
  doc.setFont("helvetica", "bold");
  doc.setFontSize(12);
  doc.text("ARELLANO LAW FOUNDATION", 148, 14, { align: "center" });

  doc.setFont("helvetica", "normal");
  doc.setFontSize(9);
  doc.text(
    "Taft Ave, Cor. Menlo St. Pasay City · Tel. No. 404-3089 to 93",
    148,
    19,
    { align: "center" }
  );

  doc.setFont("helvetica", "bold");
  doc.text(`VOUCHER SUMMARY (${range})`, 148, 26, { align: "center" });

  doc.line(14, 29, 282, 29);

  const tableData: any[] = [];

  vouchers.forEach((v) => {
    v.details.forEach((detail, idx) => {
      tableData.push([
        idx === 0 ? formatDate(v.voucher_date) : "",
        idx === 0 ? v.voucher_no : "",
        idx === 0 ? formatPhpAmount(v.check_amount) : "",
        idx === 0 ? v.payee : "",
        idx === 0 ? v.purpose : "",
        detail.account?.account_title || "Unspecified",
        formatPhpAmount(detail.amount),
        detail.charging_tag,
      ]);
    });
  });

  autoTable(doc, {
    startY: 33,
    head: [["Voucher Date", "Voucher No", "Check Amount", "Payee", "Purpose", "Account", "Detail Amount", "Charging Tag"]],
    body: tableData,
    styles: { fontSize: 8 },
    headStyles: { fillColor: [99, 102, 241] },
  });

  doc.save(`voucher_summary_${range.replace(/\s+/g, "_")}.pdf`);
}
