// lib/exportHelper.ts
import { utils, writeFile } from 'xlsx';

export interface ExportColumn {
  key: string;
  label: string;
  format?: (value: any) => string;
  total?: boolean; // Whether to include this column in total calculation
}

export interface ExportOptions {
  filename?: string;
  sheetName?: string;
  title?: string;
  includeTotal?: boolean; // Whether to include total row
  totalLabel?: string; // Custom label for total row
}

/**
 * Export data to Excel format with total row
 */
export const exportToExcel = (data: any[], columns: ExportColumn[], options: ExportOptions = {}) => {
  const {
    filename = 'export',
    sheetName = 'Data',
    title = 'Export Data',
    includeTotal = true,
    totalLabel = 'Total'
  } = options;

  if (data.length === 0) {
    console.warn('No data to export');
    return;
  }

  try {
    // Format data with column definitions
    const formattedData = data.map(row => {
      const formattedRow: any = {};
      columns.forEach(column => {
        let value = row[column.key];
        if (column.format && value !== undefined && value !== null) {
          value = column.format(value);
        }
        formattedRow[column.label] = value;
      });
      return formattedRow;
    });

    // Calculate totals for columns marked with total: true
    const totals: any = {};
    if (includeTotal) {
      columns.forEach(column => {
        if (column.total) {
          const total = data.reduce((sum, row) => {
            const value = row[column.key];
            // Extract numeric value from formatted strings if needed
            let numericValue = Number(value);
            if (isNaN(numericValue) && typeof value === 'string') {
              // Try to extract number from currency strings like "₱1,000.00"
              const cleanValue = value.replace(/[^\d.-]/g, '');
              numericValue = Number(cleanValue) || 0;
            }
            return sum + numericValue;
          }, 0);
          totals[column.label] = column.format ? column.format(total) : total;
        }
      });
    }

    // Create worksheet with proper structure
    const ws = utils.json_to_sheet([], { skipHeader: true });
    
    let currentRow = 1; // Start from row 1 (Excel is 1-based)
    
    // Add title row if specified
    if (title) {
      utils.sheet_add_aoa(ws, [[title]], { origin: `A${currentRow}` });
      currentRow += 2; // Title row + empty row
    }
    
    // Add column headers
    const headers = columns.map(col => col.label);
    utils.sheet_add_aoa(ws, [headers], { origin: `A${currentRow}` });
    
    // Add data rows starting from the next row
    const dataStartRow = currentRow + 1;
    if (formattedData.length > 0) {
      utils.sheet_add_json(ws, formattedData, { 
        origin: `A${dataStartRow}`, 
        skipHeader: true 
      });
    }
    
    // Add total row if requested and there are totals to show
    if (includeTotal && Object.keys(totals).length > 0) {
      // Calculate the correct row for totals (after all data rows)
      const totalRowIndex = dataStartRow + formattedData.length + 1; // +1 for separator
      
      // Add separator row before total (empty row)
      const separatorRow = columns.map(() => '');
      utils.sheet_add_aoa(ws, [separatorRow], { origin: `A${totalRowIndex - 1}` });
      
      // Add total row
      const totalRow = columns.map(col => {
        if (col.total) {
          return totals[col.label];
        } else if (col.label === columns[0].label) {
          return totalLabel;
        }
        return '';
      });
      
      utils.sheet_add_aoa(ws, [totalRow], { origin: `A${totalRowIndex}` });
    }

    // Auto-size columns for better readability
    const maxWidths = columns.map((col, index) => {
      const allValues = formattedData.map(row => String(row[col.label] || ''));
      if (includeTotal && col.total) {
        allValues.push(String(totals[col.label] || ''));
      }
      const maxLength = Math.max(
        col.label.length,
        ...allValues.map(val => val.length)
      );
      return { wch: Math.min(maxLength + 2, 50) };
    });
    
    ws['!cols'] = maxWidths;

    // Create workbook and add worksheet
    const wb = utils.book_new();
    utils.book_append_sheet(wb, ws, sheetName);
    
    // Generate filename with timestamp
    const timestamp = new Date().toISOString().split('T')[0];
    const finalFilename = `${filename}_${timestamp}.xlsx`;
    
    // Download the file
    writeFile(wb, finalFilename);
    
    console.log(`Excel file exported successfully: ${finalFilename}`);
    console.log(`Included ${formattedData.length} data rows with totals`);
    console.log('Totals calculated:', totals);
    
  } catch (error) {
    console.error('Error exporting to Excel:', error);
    throw new Error('Failed to export Excel file');
  }
};

/**
 * Quick export for purchase orders
 */
export const quickExport = {
  /**
   * Export purchase orders to Excel with total amount
   */
  purchaseOrders: (data: any[]) => {
    const totalAmount = data.reduce((sum, po) => sum + (Number(po.amount) || 0), 0);
    
    const columns: ExportColumn[] = [
      { key: 'po_no', label: 'PO Number' },
      { key: 'date', label: 'Date', format: (value) => formatDate(value) },
      { key: 'department', label: 'Department', format: (value) => value?.department_name || 'N/A' },
      { key: 'payee', label: 'Payee' },
      { 
        key: 'amount', 
        label: 'Amount', 
        format: (value) => `₱${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
        total: true
      },
    ];
    
    exportToExcel(data, columns, {
      filename: 'purchase_orders',
      title: 'Purchase Orders Report',
      totalLabel: `Total (${data.length} POs)`
    });
  }
};

/**
 * Simple export without totals for basic use
 */
export const exportSimple = (data: any[], columns: ExportColumn[], options: ExportOptions = {}) => {
  return exportToExcel(data, columns, { ...options, includeTotal: false });
};

/**
 * Format date for export
 */
const formatDate = (dateString: string | Date): string => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};