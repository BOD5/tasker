<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import InputError from '@/Components/InputError.vue';
import { DialogDescription, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/Components/ui/dialog';
import TimeEntryDetailsFields from '@/Components/TimeEntryDetailsFields.vue';

const props = defineProps({
  entry: { type: Object, default: null },
  availableTeams: { type: Array, default: () => [] },
  availableTasks: { type: Array, default: () => [] },
  customFieldDefinitions: { type: Array, default: () => [] },
  lastTeamId: Number | null,
  generalTeamId: Number | null,
});

const emit = defineEmits(['close', 'submit']);

const isEditing = computed(() => !!props.entry);
const formatForInput = (dateTimeString) => {
  if (!dateTimeString) return '';
  try {
    const date = new Date(dateTimeString);
    if (isNaN(date.getTime())) {
      return '';
    }
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  } catch (e) {
    console.error('Format for input error:', dateTimeString, e);
    return '';
  }
};

const form = useForm({
  id: null,
  description: '',
  team_id: null,
  task_id: null,
  started_at: '',
  ended_at: '',
  custom_fields: {},
});

watch(
  () => props.entry,
  (newEntry) => {
    if (newEntry) {
      const initialCustomFields = {};
      const relevantFields =
        props.customFieldDefinitions?.filter((def) => def.team_id === null || def.team_id === newEntry.team_id) ?? [];
      relevantFields.forEach((def) => {
        const existingValue = newEntry.custom_field_values?.find(
          (cfv) => cfv.custom_field_definition_id === def.id,
        )?.value;
        let formValue = existingValue ?? null;
        if (def.type === 'boolean') {
          formValue = existingValue === '1';
        }
        initialCustomFields[def.code] = formValue;
      });

      form
        .defaults({
          id: newEntry.id,
          description: newEntry.description,
          team_id: newEntry.team_id,
          task_id: newEntry.task_id,
          started_at: formatForInput(newEntry.started_at),
          ended_at: formatForInput(newEntry.ended_at),
          custom_fields: initialCustomFields,
        })
        .reset();
    } else {
      form
        .defaults({
          id: null,
          description: '',
          team_id: props.lastTeamId ?? props.generalTeamId ?? props.availableTeams?.[0]?.id ?? null,
          task_id: null,
          started_at: '',
          ended_at: '',
          custom_fields: {},
        })
        .reset();
    }
  },
  { immediate: true, deep: true },
);

watch(
  () => form.team_id,
  (newTeamId, oldTeamId) => {
    if (newTeamId !== oldTeamId) {
      const newFields = {};
      props.customFieldDefinitions
        ?.filter((def) => def.team_id === null || def.team_id === newTeamId)
        .forEach((def) => {
          newFields[def.code] = def.type === 'boolean' ? false : null;
        });
      form.custom_fields = newFields;
    }
  },
  { deep: true },
);

const triggerSubmit = () => {
  const transformedData = { ...form.data() };
  emit('submit', {
    ...form,
    data: () => transformedData,
  });
};
</script>

<template>
  <DialogHeader>
    <DialogTitle>{{ isEditing ? 'Редагувати Запис Часу' : 'Додати Запис Часу Вручну' }}</DialogTitle>
    <DialogDescription>
      {{ isEditing ? 'Внесіть зміни до запису.' : 'Вкажіть деталі для запису часу, що вже минув.' }}
    </DialogDescription>
  </DialogHeader>
  <form @submit.prevent="triggerSubmit">
    <div class="grid gap-4 py-4 max-h-[70vh] overflow-y-auto px-6">
      <div>
        <Label for="form-description" required>Опис *</Label>
        <Input id="form-description" v-model="form.description" type="text" class="mt-1 block w-full" required />
        <InputError class="mt-2" :message="form.errors.description" />
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label for="form-started_at" required>Початок *</Label>
          <Input
            id="form-started_at"
            v-model="form.started_at"
            type="datetime-local"
            class="mt-1 block w-full"
            required
          />
          <InputError class="mt-2" :message="form.errors.started_at" />
        </div>
        <div>
          <Label for="form-ended_at">Кінець</Label>
          <Input id="form-ended_at" v-model="form.ended_at" type="datetime-local" class="mt-1 block w-full" />
          <InputError class="mt-2" :message="form.errors.ended_at" />
        </div>
      </div>

      <TimeEntryDetailsFields
        :form="form"
        :availableTeams="availableTeams"
        :availableTasks="availableTasks"
        :customFieldDefinitions="customFieldDefinitions"
        :errors="form.errors"
      />
    </div>
    <DialogFooter class="px-6 pb-4 pt-2">
      <DialogClose as-child>
        <Button type="button" variant="secondary" @click="$emit('close')"> Скасувати </Button>
      </DialogClose>
      <Button type="submit" :disabled="form.processing">
        {{ isEditing ? 'Зберегти Зміни' : 'Додати Запис' }}
      </Button>
    </DialogFooter>
  </form>
</template>
