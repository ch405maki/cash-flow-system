import { reactive, computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios'
import { onMounted, onUnmounted } from "vue";
import { formatCurrency } from '@/lib/utils';

export interface VoucherItem {
  amount: number;
  charging_tag: string | null;
  hours: number | null;
  rate: number | null;
  account_id: string;
  editing?: boolean;
  original?: VoucherItem;
}

interface DistributionExpense {
  id: number;
  account_name: string;
  amount: number;
  date: string;
  petty_cash_id: number;
  account_id?: number;
}

interface VoucherFormProps {
  accounts?: Array<{ id: number; account_title: string }>;
  auth: { user: { id: number } };
  voucher_number: string;
  purchase_order?: {
    id: number;
    po_no: string;
    tin_no: string;
    payee?: string;
    check_payable_to?: string;
    amount?: number;
  };
  distribution_expenses?: DistributionExpense[];
  petty_cash?: { id: number; pcv_no: string; paid_to: string; remarks?: string };
  prefilled_data?: { paid_to?: string; total_amount?: number; remarks?: string };
}

export function useVoucherForm(props: VoucherFormProps) {
  const toast = useToast();
  const accountSearchQuery = ref('');

  const voucherNumberError = ref('');
  const checkingVoucher = ref(false);
  let checkTimeout: ReturnType<typeof setTimeout> | null = null;

  const filteredAccounts = computed(() => {
    if (!accountSearchQuery.value) return props.accounts || [];
    return (props.accounts || []).filter(account =>
      account.account_title.toLowerCase().includes(accountSearchQuery.value.toLowerCase())
    );
  });

  const form = reactive({
    po_id: props.purchase_order?.id,
    voucher_no: props.voucher_number,
    tin_no: props.purchase_order?.tin_no,
    issue_date: '',
    payment_date: '',
    delivery_date: '',
    voucher_date: new Date().toISOString().split('T')[0],
    purpose: '',
    payee: '',
    check_no: null as string | null,
    check_payable_to: '',
    check_amount: 0,
    status: 'forAudit',
    type: 'cash',
    user_id: props.auth.user.id,
    items: [] as VoucherItem[],
  });

  if (props.purchase_order) {
    if (props.purchase_order.payee) {
      form.payee = props.purchase_order.payee;
    }
    if (props.purchase_order.check_payable_to) {
      form.check_payable_to = props.purchase_order.check_payable_to;
    }
  }

  const newItem = ref<VoucherItem>({
    amount: 0,
    charging_tag: null,
    hours: null,
    rate: null,
    account_id: '',
  });

  const isCashVoucher = computed(() => form.type === 'cash');

  const addItem = () => {
    if (!newItem.value.account_id) {
      toast.error('Account selection is required');
      return;
    }

    if (newItem.value.amount <= 0) {
      toast.error('Amount must be greater than 0');
      return;
    }

    form.items.push({ ...newItem.value });
    calculateTotalAmount();
    resetNewItem();
  };

  const removeItem = (index: number) => {
    form.items.splice(index, 1);
    calculateTotalAmount();
  };

  const resetNewItem = () => {
    newItem.value = {
      amount: 0,
      charging_tag: null,
      hours: null,
      rate: null,
      account_id: '',
    };
  };

  const calculateTotalAmount = () => {
    form.check_amount = form.items.reduce((sum, item) => {
      if (item.charging_tag === 'C') {
        return sum + item.amount;
      } else if (item.charging_tag === 'D') {
        return sum - item.amount;
      }
      return sum;
    }, 0);
  };

  const calculateItemAmount = () => {
    if (isCashVoucher.value) {
      return;
    }
    if (newItem.value.hours && newItem.value.rate) {
      newItem.value.amount = newItem.value.hours * newItem.value.rate;
    }
  };

  const calculateTotal = (tag: string) => {
    return form.items.reduce((sum, item) => {
      if (item.charging_tag === tag) {
        return sum + item.amount;
      }
      return sum;
    }, 0);
  };

  const checkVoucherNumber = (voucherNo: string) => {
    if (checkTimeout) {
      clearTimeout(checkTimeout);
    }

    if (!voucherNo || voucherNo.trim() === '') {
      voucherNumberError.value = 'Voucher number is required';
      return;
    }

    checkTimeout = setTimeout(async () => {
      checkingVoucher.value = true;

      try {
        const params = new URLSearchParams({
          voucher_no: voucherNo,
        });

        const response = await axios.get(`/api/vouchers/check-number?${params}`);

        if (!response.data.available) {
          voucherNumberError.value = 'Voucher number already exists';
          toast.error('Voucher number already exists in the system', {
            timeout: 3000,
            position: 'top-right'
          });
        } else {
          voucherNumberError.value = '';
        }
      } catch (error) {
        console.error('Error checking voucher number:', error);
        toast.error('Unable to validate voucher number', {
          timeout: 3000,
          position: 'top-right'
        });
      } finally {
        checkingVoucher.value = false;
      }
    }, 500);
  };

  watch(() => form.voucher_no, (newVal) => {
    checkVoucherNumber(newVal);
  });

  const suggestVoucherNumber = async () => {
    try {
      const response = await axios.get('/api/vouchers/generate-number');
      if (response.data.success) {
        form.voucher_no = response.data.voucher_number;
      }
    } catch (error) {
      console.error('Error generating voucher number:', error);
    }
  };

  const editItem = (index: number) => {
    form.items.forEach((item, i) => {
      if (i !== index && item.editing) {
        cancelEdit(i);
      }
    });

    const originalItem = JSON.parse(JSON.stringify(form.items[index]));

    form.items[index] = {
      ...form.items[index],
      editing: true,
      original: originalItem
    };
  };

  const saveEdit = (index: number) => {
    if (form.items[index].editing) {
      form.items[index] = {
        ...form.items[index],
        editing: false
      };
      delete form.items[index].original;
      calculateTotalAmount();
      toast.success('Item updated');
    }
  };

  const cancelEdit = (index: number) => {
    if (form.items[index].original) {
      form.items[index] = {
        ...form.items[index].original,
        editing: false
      };
      delete form.items[index].original;
    }
  };

  const cancelAllEdits = () => {
    form.items.forEach((item, index) => {
      if (item.editing) {
        cancelEdit(index);
      }
    });
  };

  const findAccountIdByName = (name: string) => {
    const acc = props.accounts?.find(a => a.account_title === name);
    return acc ? acc.id.toString() : null;
  };

  const submitVoucher = async () => {
    try {
      if (!form.voucher_no || form.voucher_no.trim() === '') {
        toast.error('Voucher number is required');
        return;
      }

      if (voucherNumberError.value) {
        toast.error(voucherNumberError.value);
        return;
      }

      cancelAllEdits();

      if (form.items.length === 0 && !isCashVoucher.value) {
        toast.error('Please add at least one item for non-cash vouchers');
        return;
      }

      const payload = {
        ...form,
        check: form.items,
      };

      const response = await axios.post('/api/vouchers', payload);
      toast.success(`Voucher created successfully! Number: ${response.data.voucher_no}`);
      router.visit('/vouchers');
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.data?.errors) {
        Object.values(error.response.data.errors).forEach((msg) => {
          toast.error(msg[0]);
        });
      } else {
        toast.error('Failed to create Voucher');
      }
    }
  };

  onMounted(() => {
    if (props.distribution_expenses && props.distribution_expenses.length > 0) {
      props.distribution_expenses.forEach(d => {
        form.items.push({
          account_id: findAccountIdByName(d.account_name),
          charging_tag: "C",
          amount: Number(d.amount),
          hours: null,
          rate: null,
          editing: false
        });
      });

      calculateTotalAmount();
    }
  });

  onUnmounted(() => {
    if (checkTimeout) {
      clearTimeout(checkTimeout);
    }
  });

  return {
    form,
    newItem,
    accountSearchQuery,
    voucherNumberError,
    checkingVoucher,
    filteredAccounts,
    isCashVoucher,
    addItem,
    removeItem,
    resetNewItem,
    calculateTotalAmount,
    calculateItemAmount,
    calculateTotal,
    checkVoucherNumber,
    suggestVoucherNumber,
    submitVoucher,
    editItem,
    saveEdit,
    cancelEdit,
    cancelAllEdits,
    findAccountIdByName,
  };
}
