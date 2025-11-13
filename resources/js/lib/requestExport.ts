// lib/exportHelper.ts
import { utils, writeFile, WorkBook } from 'xlsx';

export interface ExportOptions {
  filename?: string;
  sheetName?: string;
  headers?: string[];
  title?: string;
  includeDate?: boolean;
}

export interface ExportColumn {
  key: string;
  label: string;
  format?: (value: any) => string;
}

export const exportToExcel = (data: any[], columns: ExportColumn[], options: ExportOptions = {}) => {
  const {
    filename = 'export',
    sheetName = 'Data',
    title = 'Export Data',
    includeDate = true
  } = options;

  // Format data with column definitions
  const formattedData = data.map(row => {
    const formattedRow: any = {};
    columns.forEach(column => {
      let value = row[column.key];
      if (column.format) {
        value = column.format(value);
      }
      formattedRow[column.label] = value;
    });
    return formattedRow;
  });

  // Create worksheet
  const ws = utils.json_to_sheet(formattedData);
  
  // Add title row if specified
  if (title) {
    utils.sheet_add_aoa(ws, [[title]], { origin: 'A1' });
    utils.sheet_add_aoa(ws, [columns.map(col => col.label)], { origin: 'A2' });
    utils.sheet_add_json(ws, formattedData, { origin: 'A3', skipHeader: true });
  } else {
    utils.sheet_add_aoa(ws, [columns.map(col => col.label)], { origin: 'A1' });
    utils.sheet_add_json(ws, formattedData, { origin: 'A2', skipHeader: true });
  }

  // Auto-size columns
  const maxWidths = columns.map((col, index) => {
    const maxLength = Math.max(
      col.label.length,
      ...formattedData.map(row => String(row[col.label] || '').length)
    );
    return { wch: Math.min(maxLength + 2, 50) }; // Cap at 50 characters
  });
  
  ws['!cols'] = maxWidths;

  // Create workbook
  const wb = utils.book_new();
  utils.book_append_sheet(wb, ws, sheetName);
  
  // Generate filename with date
  const dateSuffix = includeDate ? `_${new Date().toISOString().split('T')[0]}` : '';
  const finalFilename = `${filename}${dateSuffix}.xlsx`;
  
  // Download file
  writeFile(wb, finalFilename);
};

export const exportToCSV = (data: any[], columns: ExportColumn[], options: Omit<ExportOptions, 'sheetName'> = {}) => {
  const { filename = 'export', includeDate = true } = options;
  
  if (data.length === 0) {
    console.warn('No data to export');
    return;
  }

  // Format data with column definitions
  const formattedData = data.map(row => {
    const formattedRow: any = {};
    columns.forEach(column => {
      let value = row[column.key];
      if (column.format) {
        value = column.format(value);
      }
      formattedRow[column.label] = value;
    });
    return formattedRow;
  });

  // Create CSV content
  const headers = columns.map(col => col.label);
  let csvContent = headers.join(',') + '\n';
  
  formattedData.forEach(row => {
    const rowData = headers.map(header => {
      const value = row[header];
      // Handle values that might contain commas, quotes, or newlines
      if (value == null) return '';
      
      const stringValue = String(value);
      if (stringValue.includes(',') || stringValue.includes('"') || stringValue.includes('\n')) {
        return `"${stringValue.replace(/"/g, '""')}"`;
      }
      return stringValue;
    });
    csvContent += rowData.join(',') + '\n';
  });
  
  // Create and download file
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  
  const dateSuffix = includeDate ? `_${new Date().toISOString().split('T')[0]}` : '';
  const finalFilename = `${filename}${dateSuffix}.csv`;
  
  link.setAttribute('href', url);
  link.setAttribute('download', finalFilename);
  link.style.visibility = 'hidden';
  
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

// Quick export functions for common scenarios
export const quickExport = {
  pettyCash: (data: any[]) => {
    const columns: ExportColumn[] = [
      { key: 'pcv_no', label: 'PCV Number' },
      { key: 'paid_to', label: 'Paid To' },
      { key: 'requested_by', label: 'Requested By' },
      { key: 'date', label: 'Date', format: (value) => formatDate(value) },
      { key: 'status', label: 'Status' },
      { key: 'remarks', label: 'Remarks' },
      { key: 'amount', label: 'Amount', format: (value) => `₱${parseFloat(value).toLocaleString()}` },
    ];
    
    exportToExcel(data, columns, {
      filename: 'petty_cash_report',
      title: 'Petty Cash Report'
    });
  }
};

// Helper function for date formatting (you might want to move this to your utils)
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};