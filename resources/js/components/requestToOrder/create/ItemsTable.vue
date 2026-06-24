<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  SelectGroup,
} from '@/components/ui/select'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

interface OrderItem {
  quantity: number
  unit: string
  item_description: string
  editing?: boolean
  original?: OrderItem
}

const props = defineProps<{
  items: OrderItem[]
  units: Array<{ id: number; name: string }>
}>()

const emit = defineEmits<{
  edit: [index: number]
  save: [index: number]
  cancel: [index: number]
  remove: [index: number]
  keydown: [event: KeyboardEvent, index: number]
}>()

const hasEditing = () => props.items.some(item => item.editing)
</script>

<template>
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>Qty</TableHead>
        <TableHead>Unit</TableHead>
        <TableHead>Description</TableHead>
        <TableHead v-if="hasEditing()" class="w-[180px]">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow v-if="items.length === 0">
        <TableCell :colspan="hasEditing() ? 4 : 3" class="text-center py-8 text-muted-foreground">
          No items added yet. Add items using the form above.
        </TableCell>
      </TableRow>
      <TableRow
        v-for="(item, index) in items"
        :key="index"
        @click="emit('edit', index)"
        :class="{ 'hover:bg-gray-50 cursor-pointer': true, 'bg-blue-50': item.editing }"
      >
        <TableCell>
          <div v-if="!item.editing">{{ item.quantity }}</div>
          <Input v-else type="number" v-model.number="item.quantity" min="1" @click.stop @keydown="emit('keydown', $event, index)" class="w-full" />
        </TableCell>
        <TableCell>
          <div v-if="!item.editing">{{ item.unit || '-' }}</div>
          <Select v-else v-model="item.unit" @click.stop>
            <SelectTrigger class="w-full">
              <SelectValue placeholder="Select unit" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="u in units" :key="u.id" :value="u.name">{{ u.name }}</SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </TableCell>
        <TableCell class="max-w-[200px]">
          <div v-if="!item.editing" class="truncate">{{ item.item_description }}</div>
          <Input v-else v-model="item.item_description" @click.stop @keydown="emit('keydown', $event, index)" class="w-full" />
        </TableCell>
        <TableCell v-if="hasEditing()">
          <div v-if="item.editing" class="flex space-x-2">
            <Button variant="outline" size="sm" @click.stop="emit('save', index)">Save</Button>
            <Button variant="outline" size="sm" @click.stop="emit('cancel', index)">Cancel</Button>
            <Button variant="destructive" size="sm" @click.stop="emit('remove', index)">Remove</Button>
          </div>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>
