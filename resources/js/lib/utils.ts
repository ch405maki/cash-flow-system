import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

export function formatDateTime(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  });
}


export function formatMonth(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
  })
}

export const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(amount);
};

export const amountToWords = (amount: number): string => {
    const whole = Math.floor(amount);
    const fraction = Math.round((amount - whole) * 100);

    const words = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];

    const tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
        'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];

    const convertLessThanOneThousand = (num: number): string => {
        if (num < 20) return words[num];
        if (num < 100) {
            return tens[Math.floor(num / 10)] +
                (num % 10 ? ' ' + words[num % 10] : '');
        }

        const hundreds = Math.floor(num / 100);
        const remainder = num % 100;

        return words[hundreds] + ' Hundred' +
            (remainder ? ' ' + convertLessThanOneThousand(remainder) : '');
    };

    let result = 'Zero';
    if (whole > 0) {
        if (whole < 1000) {
            result = convertLessThanOneThousand(whole);
        } else if (whole < 1000000) {
            const thousands = Math.floor(whole / 1000);
            const remainder = whole % 1000;

            result = convertLessThanOneThousand(thousands) + ' Thousand' +
                (remainder ? ' ' + convertLessThanOneThousand(remainder) : '');
        } else {
            return 'Amount exceeds conversion limit';
        }
    }

    result = result.trim() + ' Pesos';

    if (fraction > 0) {
        result += ' and ' + convertLessThanOneThousand(fraction) + ' Centavos';
    }

    return result;
};




