<script setup>
import { ref, watch } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { X } from 'lucide-vue-next';

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const emit = defineEmits(['filter']);

const startDate = ref(props.filters.start_date ?? '');
const endDate = ref(props.filters.end_date ?? '');

watch(
  () => props.filters,
  (newFilters) => {
    startDate.value = newFilters.start_date ?? '';
    endDate.value = newFilters.end_date ?? '';
  },
  { deep: true },
);

const applyFilters = () => {
  emit('filter', {
    start_date: startDate.value || undefined,
    end_date: endDate.value || undefined,
    sort: props.filters.sort,
    direction: props.filters.direction,
    page: 1,
  });
};

const clearDates = () => {
  startDate.value = '';
  endDate.value = '';
  applyFilters();
};
</script>

<template>
  <form
    @submit.prevent="applyFilters"
    class="flex flex-wrap items-end gap-3 pb-4 border-b border-border mb-4 bg-card text-card-foreground p-4 shadow sm:rounded-lg"
  >
    <div class="flex-grow sm:flex-grow-0">
      <Label for="startDate" class="mb-1 block text-xs font-medium text-muted-foreground">З Дати</Label>
      <Input id="startDate" type="date" v-model="startDate" class="h-9" />
    </div>
    <div class="flex-grow sm:flex-grow-0">
      <Label for="endDate" class="mb-1 block text-xs font-medium text-muted-foreground">По Дату</Label>
      <Input id="endDate" type="date" v-model="endDate" class="h-9" />
    </div>
    <div>
      <Button type="button" variant="ghost" size="sm" @click="clearDates" class="h-9" title="Очистити дати">
        <X class="h-4 w-4" />
      </Button>
    </div>
    <Button type="submit" variant="outline" size="sm" class="h-9 w-full sm:w-auto">Фільтрувати</Button>
  </form>
</template>
