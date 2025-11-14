// lib/exportHelper.ts
import { utils, writeFile } from 'xlsx';

export interface ExportOptions {
  filename?: string;
  sheetName?: string;
  headers?: string[];
}

export const exportToExcel = (data: any[], options: ExportOptions = {}) => {
  const {
    filename = 'export',
    sheetName = 'Sheet1',
    headers = []
  } = options;

  // Create worksheet
  const ws = utils.json_to_sheet(data, { header: headers.length ? headers : Object.keys(data[0] || {}) });
  
  // Create workbook
  const wb = utils.book_new();
  utils.book_append_sheet(wb, ws, sheetName);
  
  // Generate and download file
  writeFile(wb, `${filename}_${new Date().toISOString().split('T')[0]}.xlsx`);
};

export const exportToCSV = (data: any[], options: Omit<ExportOptions, 'sheetName'> = {}) => {
  const { filename = 'export', headers = [] } = options;
  
  if (data.length === 0) return;
  
  const actualHeaders = headers.length ? headers : Object.keys(data[0]);
  
  // Create CSV content
  let csvContent = actualHeaders.join(',') + '\n';
  
  data.forEach(row => {
    const rowData = actualHeaders.map(header => {
      const value = row[header];
      // Handle values that might contain commas or quotes
      if (typeof value === 'string' && (value.includes(',') || value.includes('"'))) {
        return `"${value.replace(/"/g, '""')}"`;
      }
      return value;
    });
    csvContent += rowData.join(',') + '\n';
  });
  
  // Create and download file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  link.setAttribute('href', url);
  link.setAttribute('download', `${filename}_${new Date().toISOString().split('T')[0]}.csv`);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};