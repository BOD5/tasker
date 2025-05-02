<script setup>
import { computed } from 'vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Button } from '@/Components/ui/button';
import { Trash2, Pencil, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';
import { useDateTimeFormatters } from '@/Composables/useDateTimeFormatters';

const props = defineProps({
  entries: {
    type: Array,
    default: () => [],
  },
  activeTimerId: Number | null,
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['sort', 'deleteEntry', 'editEntry']);

const { formatDateTime, calculateDuration } = useDateTimeFormatters();

const editEntry = (entry) => {
  emit('editEntry', entry);
};
const deleteEntry = (entryId) => {
  if (confirm('Ви впевнені?')) {
    emit('deleteEntry', entryId);
  }
};
const sortBy = (column) => {
  emit('sort', column);
};

const sortColumn = computed(() => props.filters?.sort);
const sortDirection = computed(() => props.filters?.direction);
</script>

<template>
  <Card>
    <CardHeader class="pb-4">
      <CardTitle>Історія записів</CardTitle>
    </CardHeader>
    <CardContent>
      <div class="border border-border rounded-lg overflow-hidden">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-[30%]">
                <Button
                  variant="ghost"
                  @click="sortBy('description')"
                  class="px-0 h-auto hover:bg-transparent flex items-center space-x-1 text-xs font-medium text-muted-foreground uppercase tracking-wider hover:text-foreground"
                >
                  <span>Опис</span>
                  <ArrowUpDown v-if="sortColumn !== 'description'" class="h-3 w-3" />
                  <ArrowUp
                    v-if="sortColumn === 'description' && sortDirection === 'asc'"
                    class="h-4 w-4 text-foreground"
                  />
                  <ArrowDown
                    v-if="sortColumn === 'description' && sortDirection === 'desc'"
                    class="h-4 w-4 text-foreground"
                  />
                </Button>
              </TableHead>
              <TableHead>Команда</TableHead>
              <TableHead>Завдання</TableHead>
              <TableHead>
                <Button
                  variant="ghost"
                  @click="sortBy('started_at')"
                  class="px-0 h-auto hover:bg-transparent flex items-center space-x-1 text-xs font-medium text-muted-foreground uppercase tracking-wider hover:text-foreground"
                >
                  <span>Початок</span>
                  <ArrowUpDown v-if="sortColumn !== 'started_at'" class="h-3 w-3" />
                  <ArrowUp
                    v-if="sortColumn === 'started_at' && sortDirection === 'asc'"
                    class="h-4 w-4 text-foreground"
                  />
                  <ArrowDown
                    v-if="sortColumn === 'started_at' && sortDirection === 'desc'"
                    class="h-4 w-4 text-foreground"
                  />
                </Button>
              </TableHead>
              <TableHead>
                <Button
                  variant="ghost"
                  @click="sortBy('ended_at')"
                  class="px-0 h-auto hover:bg-transparent flex items-center space-x-1 text-xs font-medium text-muted-foreground uppercase tracking-wider hover:text-foreground"
                >
                  <span>Кінець</span>
                  <ArrowUpDown v-if="sortColumn !== 'ended_at'" class="h-3 w-3" />
                  <ArrowUp
                    v-if="sortColumn === 'ended_at' && sortDirection === 'asc'"
                    class="h-4 w-4 text-foreground"
                  />
                  <ArrowDown
                    v-if="sortColumn === 'ended_at' && sortDirection === 'desc'"
                    class="h-4 w-4 text-foreground"
                  />
                </Button>
              </TableHead>
              <TableHead>Трив.</TableHead>
              <TableHead class="text-right">Дії</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="!entries?.length">
              <TableCell colspan="7" class="text-center h-24 text-muted-foreground"
                >Немає записів за обраний період.</TableCell
              >
            </TableRow>
            <TableRow v-for="entry in entries" :key="entry.id" class="hover:bg-muted/50">
              <TableCell class="font-medium text-foreground whitespace-pre-wrap">{{ entry.description }}</TableCell>
              <TableCell class="text-muted-foreground">{{ entry.team?.name ?? 'N/A' }}</TableCell>
              <TableCell class="text-muted-foreground">{{ entry.task?.title ?? '-' }}</TableCell>
              <TableCell class="text-muted-foreground">{{ formatDateTime(entry.started_at) }}</TableCell>
              <TableCell class="text-muted-foreground">{{ formatDateTime(entry.ended_at) }}</TableCell>
              <TableCell class="font-semibold text-foreground tabular-nums">{{
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
