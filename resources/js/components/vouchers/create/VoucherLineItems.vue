<script setup lang="ts">
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Plus, Trash2, Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { useToast } from 'vue-toastification';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

interface VoucherItem {
  amount: number;
  charging_tag: string | null;
  hours: number | null;
  rate: number | null;
  account_id: string;
  editing?: boolean;
  original?: VoucherItem;
}

const props = defineProps<{
  form: {
    items: VoucherItem[];
    type: string;
    check_amount: number;
  };
  accounts: Array<{ id: number; account_title: string }>;
  isCashVoucher: boolean;
}>();

const toast = useToast();
const accountSearchQuery = ref('');

const filteredAccounts = computed(() => {
  if (!accountSearchQuery.value) return props.accounts || [];
  return (props.accounts || []).filter(account =>
    account.account_title.toLowerCase().includes(accountSearchQuery.value.toLowerCase())
  );
});

const newItem = ref<VoucherItem>({
  amount: 0,
  charging_tag: null,
  hours: null,
  rate: null,
  account_id: '',
});

const addItem = () => {
  if (!newItem.value.account_id) {
    toast.error('Account selection is required');
    return;
  }

  if (newItem.value.amount <= 0) {
    toast.error('Amount must be greater than 0');
    return;
  }

  props.form.items.push({ ...newItem.value });
  calculateTotalAmount();
  resetNewItem();
};

const removeItem = (index: number) => {
  props.form.items.splice(index, 1);
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
  props.form.check_amount = props.form.items.reduce((sum, item) => {
    if (item.charging_tag === 'C') {
      return sum + item.amount;
    } else if (item.charging_tag === 'D') {
      return sum - item.amount;
    }
    return sum;
  }, 0);
};

const calculateItemAmount = () => {
  if (props.isCashVoucher) return;
  if (newItem.value.hours && newItem.value.rate) {
    newItem.value.amount = newItem.value.hours * newItem.value.rate;
  }
};

const calculateTotalByTag = (tag: string) => {
  return props.form.items.reduce((sum, item) => {
    if (item.charging_tag === tag) {
      return sum + item.amount;
    }
    return sum;
  }, 0);
};

const netTotal = computed(() => {
  return props.form.items.reduce((sum, item) => {
    if (item.charging_tag === 'C') return sum + item.amount;
    if (item.charging_tag === 'D') return sum - item.amount;
    return sum;
  }, 0);
});

const editItem = (index: number) => {
  props.form.items.forEach((item, i) => {
    if (i !== index && item.editing) {
      cancelEdit(i);
    }
  });

  const originalItem = JSON.parse(JSON.stringify(props.form.items[index]));

  props.form.items[index] = {
    ...props.form.items[index],
    editing: true,
    original: originalItem
  };
};

const saveEdit = (index: number) => {
  if (props.form.items[index].editing) {
    props.form.items[index] = {
      ...props.form.items[index],
      editing: false
    };
    delete props.form.items[index].original;
    calculateTotalAmount();
    toast.success('Item updated');
  }
};

const cancelEdit = (index: number) => {
  if (props.form.items[index].original) {
    props.form.items[index] = {
      ...props.form.items[index].original,
      editing: false
    };
    delete props.form.items[index].original;
  }
};

const cancelAllEdits = () => {
  props.form.items.forEach((item, index) => {
    if (item.editing) {
      cancelEdit(index);
    }
  });
};

defineExpose({ cancelAllEdits });
</script>

<template>
  <div class="border rounded-lg p-4">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-medium capitalize">Account Details</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 pb-4 border-b">
      <div class="grid gap-2" :class="isCashVoucher ? 'md:col-span-4' : 'md:col-span-3'">
        <Label>Account *</Label>
        <Combobox v-model="newItem.account_id" by="id">
          <ComboboxAnchor as-child>
            <ComboboxTrigger as-child>
              <Button variant="outline" class="w-full justify-between h-10">
                <span class="truncate">
                  {{ accounts?.find(a => a.id === parseInt(newItem.account_id))?.account_title || 'Select account' }}
                </span>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
              </Button>
            </ComboboxTrigger>
          </ComboboxAnchor>

          <ComboboxList class="max-h-[300px] overflow-y-auto shadow-lg rounded-md border">
            <div class="relative w-full items-center sticky top-0 bg-white z-10">
              <ComboboxInput
                class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 text-base"
                placeholder="Search accounts..."
                v-model="accountSearchQuery"
              />
              <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                <Search class="size-4 text-muted-foreground" />
              </span>
            </div>

            <ComboboxEmpty v-if="filteredAccounts.length === 0" class="py-4 text-center text-sm text-gray-500">
              No accounts found.
            </ComboboxEmpty>

            <ComboboxGroup>
              <div class="max-h-[250px] overflow-y-auto">
                <ComboboxItem
                  v-for="account in filteredAccounts"
                  :key="account.id"
                  :value="account.id.toString()"
                  class="h-2flex items-center px-4 text-xs text-sm hover:bg-gray-50 cursor-pointer"
                >
                  {{ account.account_title }}
                  <ComboboxItemIndicator class="ml-auto">
                    <Check class="h-4 w-4 text-primary" />
                  </ComboboxItemIndicator>
                </ComboboxItem>
              </div>
            </ComboboxGroup>
          </ComboboxList>
        </Combobox>
      </div>

      <div class="grid gap-2" :class="isCashVoucher ? 'md:col-span-3' : 'md:col-span-2'">
        <Label>Charging Tag</Label>
        <Select v-model="newItem.charging_tag">
          <SelectTrigger>
            <SelectValue placeholder="Select tag" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="C">C</SelectItem>
            <SelectItem value="D">D</SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="grid gap-2 md:col-span-2" v-if="!isCashVoucher">
        <Label>Hours</Label>
        <Input type="number" step="0.01" v-model.number="newItem.hours" @change="calculateItemAmount" />
      </div>

      <div class="grid gap-2 md:col-span-2" v-if="!isCashVoucher">
        <Label>Rate</Label>
        <Input type="number" step="0.01" v-model.number="newItem.rate" @change="calculateItemAmount" />
      </div>

      <div class="grid gap-2" :class="isCashVoucher ? 'md:col-span-5' : 'md:col-span-3'">
        <Label>Amount *</Label>
        <div class="flex gap-2">
          <Input type="number" step="0.01" v-model.number="newItem.amount" required class="flex-1" />
          <Button type="button" variant="default" @click="addItem" :disabled="!newItem.charging_tag || !newItem.amount">
            <Plus class="h-4 w-4" /> Accept
          </Button>
        </div>
      </div>
    </div>

    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="text-left">Account</TableHead>
          <TableHead class="text-left">Tag</TableHead>
          <TableHead class="text-left">Amount</TableHead>
          <TableHead v-if="form.items.some(item => item.editing)" class="text-right">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="(item, index) in form.items"
          :key="index"
          @click="!item.editing && editItem(index)"
          :class="{ 'cursor-pointer': true }"
        >
          <TableCell class="whitespace-nowrap">
            <div v-if="!item.editing">
              {{ accounts?.find(a => a.id === parseInt(item.account_id))?.account_title || 'N/A' }}
            </div>
            <Combobox v-else v-model="item.account_id" by="id" @click.stop>
              <ComboboxAnchor as-child>
                <ComboboxTrigger as-child>
                  <Button variant="outline" class="w-full justify-between h-10">
                    <span class="truncate">
                      {{ accounts?.find(a => a.id === parseInt(item.account_id))?.account_title || 'Select account' }}
                    </span>
                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                  </Button>
                </ComboboxTrigger>
              </ComboboxAnchor>
              <ComboboxList class="max-h-[300px] overflow-y-auto shadow-lg rounded-md border">
                <div class="relative w-full items-center sticky top-0 bg-white z-10">
                  <ComboboxInput class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 text-base" placeholder="Search accounts..." v-model="accountSearchQuery" />
                  <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                    <Search class="size-4 text-muted-foreground" />
                  </span>
                </div>
                <ComboboxEmpty v-if="filteredAccounts.length === 0" class="py-4 text-center text-sm text-gray-500">
                  No accounts found.
                </ComboboxEmpty>
                <ComboboxGroup>
                  <div class="max-h-[250px] overflow-y-auto">
                    <ComboboxItem v-for="account in filteredAccounts" :key="account.id" :value="account.id.toString()" class="h-2flex items-center px-4 text-xs text-sm hover:bg-gray-50 cursor-pointer">
                      {{ account.account_title }}
                      <ComboboxItemIndicator class="ml-auto">
                        <Check class="h-4 w-4 text-primary" />
                      </ComboboxItemIndicator>
                    </ComboboxItem>
                  </div>
                </ComboboxGroup>
              </ComboboxList>
            </Combobox>
          </TableCell>

          <TableCell class="whitespace-nowrap">
            <div v-if="!item.editing">{{ item.charging_tag || 'N/A' }}</div>
            <Select v-else v-model="item.charging_tag" @click.stop>
              <SelectTrigger>
                <SelectValue placeholder="Select tag" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="C">C</SelectItem>
                <SelectItem value="D">D</SelectItem>
              </SelectContent>
            </Select>
          </TableCell>

          <TableCell class="whitespace-nowrap">
            <div v-if="!item.editing">{{ item.amount.toFixed(2) }}</div>
            <Input v-else type="number" step="0.01" v-model.number="item.amount" @click.stop class="w-full" />
          </TableCell>

          <TableCell v-if="form.items.some(i => i.editing)" class="whitespace-nowrap text-right">
            <div class="flex justify-end space-x-2">
              <template v-if="item.editing">
                <Button variant="default" size="sm" @click.stop="saveEdit(index)">Save</Button>
                <Button variant="outline" size="sm" @click.stop="cancelEdit(index)">Cancel</Button>
                <Button variant="destructive" size="sm" @click.stop="removeItem(index)">
                  <Trash2 class="h-4 w-4" />
                </Button>
              </template>
              <Button v-else variant="destructive" size="sm" @click.stop="removeItem(index)">
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </TableCell>
        </TableRow>
        <TableRow v-if="form.items.length === 0">
          <TableCell
            :colspan="isCashVoucher ? (form.items.some(i => i.editing) ? 5 : 4) : (form.items.some(i => i.editing) ? 6 : 5)"
            class="text-center text-sm text-gray-500"
          >
            No items added yet
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <div class="flex justify-between mt-4">
      <p class="text-sm text-muted-foreground ml-2">
        (C: ₱{{ calculateTotalByTag('C').toFixed(2) }} - D: ₱{{ calculateTotalByTag('D').toFixed(2) }})
      </p>
      <div class="text-lg font-semibold text-right">
        Total Amount: ₱{{ netTotal.toFixed(2) }}
      </div>
    </div>
  </div>
</template>
