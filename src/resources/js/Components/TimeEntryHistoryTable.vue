<script setup>
import { computed } from 'vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Button } from '@/Components/ui/button';
import { Trash2, Pencil } from 'lucide-vue-next';

const props = defineProps({
  timeEntries: Object,
  activeTimerId: Number | null,
  filters: Object,
});

const emit = defineEmits(['changePeriod', 'deleteEntry', 'editEntry']);
const editEntry = (entry) => {
  emit('editEntry', entry);
};

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return '-';
  try {
    return new Date(dateTimeString).toLocaleString('uk-UA', {
      hour: '2-digit',
      minute: '2-digit',
      day: '2-digit',
      month: 'short',
    });
  } catch (e) {
    return dateTimeString;
  }
};

const calculateDuration = (start, end) => {
  if (!start || !end) return '-';
  try {
    const startDate = new Date(start);
    const endDate = new Date(end);
    const diffInSeconds = Math.max(0, Math.round((endDate - startDate) / 1000));
    const hours = Math.floor(diffInSeconds / 3600);
    const minutes = Math.floor((diffInSeconds % 3600) / 60);
    const sec = Math.floor(diffInSeconds % 60);
    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
  } catch (e) {
    return 'Error';
  }
};

const currentPeriod = computed(() => props.filters?.period ?? 'today');

const changePeriod = (period) => {
  emit('changePeriod', period);
};

const deleteEntry = (entryId) => {
  if (confirm('Ви впевнені, що хочете видалити цей запис часу?')) {
    emit('deleteEntry', entryId);
  }
};
</script>

<template>
  <Card>
    <CardHeader class="flex flex-row items-center justify-between pb-4">
      <CardTitle>Історія записів</CardTitle>
      <div class="text-sm text-muted-foreground space-x-2">
        <span>Період:</span>
        <button
          @click="changePeriod('today')"
          :class="['hover:underline', { 'font-bold underline text-primary': currentPeriod === 'today' }]"
        >
          Сьогодні
        </button>
        <span>|</span>
        <button
          @click="changePeriod('week')"
          :class="['hover:underline', { 'font-bold underline text-primary': currentPeriod === 'week' }]"
        >
          Тиждень
        </button>
        <span>|</span>
        <button
          @click="changePeriod('month')"
          :class="['hover:underline', { 'font-bold underline text-primary': currentPeriod === 'month' }]"
        >
          Місяць
        </button>
      </div>
    </CardHeader>
    <CardContent>
      <div class="border border-border rounded-lg overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[30%]">Опис</TableHead>
              <TableHead>Команда</TableHead>
              <TableHead>Завдання</TableHead>
              <TableHead>Початок</TableHead>
              <TableHead>Кінець</TableHead>
              <TableHead>Трив.</TableHead>
              <TableHead class="text-right">Дії</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="!timeEntries?.data?.length && !timeEntries?.length">
              <TableCell colspan="7" class="text-center h-24 text-muted-foreground"
                >Немає записів за обраний період.</TableCell
              >
            </TableRow>
            <TableRow v-for="entry in timeEntries?.data ?? timeEntries" :key="entry.id" class="hover:bg-muted/50">
              <TableCell class="font-medium text-foreground whitespace-pre-wrap">{{ entry.description }}</TableCell>
              <TableCell class="text-muted-foreground">{{ entry.team?.name ?? 'N/A' }}</TableCell>
              <TableCell class="text-muted-foreground">{{ entry.task?.title ?? '-' }}</TableCell>
              <TableCell class="text-muted-foreground">{{ formatDateTime(entry.started_at) }}</TableCell>
              <TableCell class="text-muted-foreground">{{ formatDateTime(entry.ended_at) }}</TableCell>
              <TableCell class="font-semibold text-foreground">{{
                calculateDuration(entry.started_at, entry.ended_at)
              }}</TableCell>
              <TableCell class="text-right">
                <Button
                  variant="ghost"
                  size="icon"
                  @click="editEntry(entry)"
                  title="Редагувати"
                  class="h-8 w-8 text-muted-foreground hover:text-primary hover:bg-accent"
                >
                  <Pencil class="h-4 w-4" />
                </Button>
                <Button
                  variant="ghost"
                  size="icon"
                  @click="deleteEntry(entry.id)"
                  :disabled="activeTimerId === entry.id"
                  title="Видалити"
                  class="h-8 w-8 text-destructive hover:text-destructive/80 hover:bg-destructive/10"
                >
                  <Trash2 class="h-4 w-4" />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </CardContent>
  </Card>
</template>
