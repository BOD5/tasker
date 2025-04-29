<script setup>
import { computed, watch, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Checkbox } from '@/Components/ui/checkbox';
import InputError from '@/Components/InputError.vue';
import { DialogDescription, DialogHeader, DialogTitle, DialogFooter, DialogClose } from '@/Components/ui/dialog';

const props = defineProps({
  entry: {
    type: Object,
    required: true,
  },
  availableTeams: {
    type: Array,
    default: () => [],
  },
  availableTasks: {
    type: Array,
    default: () => [],
  },
  customFieldDefinitions: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['close']);

const editForm = useForm({
  id: null,
  description: '',
  team_id: null,
  task_id: null,
  started_at: '',
  ended_at: '',
  custom_fields: {},
});

const formatForInput = (dateTimeString) => {
  if (!dateTimeString) return '';
  try {
    const date = new Date(dateTimeString);
    const offset = date.getTimezoneOffset() * 60000;
    const localDate = new Date(date.getTime() - offset);
    return localDate.toISOString().slice(0, 19);
  } catch (e) {
    console.error('Date formatting error:', e);
    return '';
  }
};

watch(
  () => props.entry,
  (newEntry) => {
    if (newEntry) {
      editForm.reset();
      editForm.id = newEntry.id;
      editForm.description = newEntry.description;
      editForm.team_id = newEntry.team_id;
      editForm.task_id = newEntry.task_id;
      editForm.started_at = formatForInput(newEntry.started_at);
      editForm.ended_at = formatForInput(newEntry.ended_at);

      const initialCustomFields = {};
      const relevantFields = props.customFieldDefinitions.filter(
        (def) => def.team_id === null || def.team_id === newEntry.team_id,
      );
      relevantFields.forEach((def) => {
        const existingValue = newEntry.custom_field_values?.find(
          (cfv) => cfv.custom_field_definition_id === def.id,
        )?.value;
        initialCustomFields[def.code] = existingValue ?? (def.type === 'boolean' ? false : null);
      });
      editForm.custom_fields = initialCustomFields;
    }
  },
  { immediate: true },
);

const relevantCustomFieldsForEdit = computed(() => {
  const currentTeamId = editForm.team_id;
  if (!currentTeamId) return {};
  const fields = {};
  props.customFieldDefinitions
    .filter((def) => def.team_id === null || def.team_id === currentTeamId)
    .forEach((def) => {
      fields[def.code] = def;
      if (editForm.custom_fields[def.code] === undefined) {
        editForm.custom_fields[def.code] = def.type === 'boolean' ? false : null;
      }
    });
  return fields;
});
const dateToUTC = (date) => {
  try {
    if (date) {
      const startDate = new Date(date);
      if (!isNaN(startDate.getTime())) {
        return startDate.toISOString();
      }
    }
  } catch (e) {
    console.error('Error parsing date:', date, e);
  }
  return date;
};
const submit = () => {
  editForm
    .transform((data) => ({ ...data, started_at: dateToUTC(data.started_at), ended_at: dateToUTC(data.ended_at) }))
    .put(route('app.time-entries.update', props.entry.id), {
      preserveScroll: true,
      onSuccess: () => {
        emit('close');
      },
    });
};
</script>

<template>
  <DialogHeader>
    <DialogTitle>Редагувати Запис Часу</DialogTitle>
    <DialogDescription> Внесіть зміни до запису та збережіть їх. </DialogDescription>
  </DialogHeader>
  <form @submit.prevent="submit">
    <div class="grid gap-4 py-4">
      <div>
        <Label for="edit-description" required>Опис *</Label>
        <Input id="edit-description" v-model="editForm.description" type="text" class="mt-1 block w-full" required />
        <InputError class="mt-2" :message="editForm.errors.description" />
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label for="edit-started_at" required>Початок *</Label>
          <Input
            id="edit-started_at"
            v-model="editForm.started_at"
            type="datetime-local"
            step="1"
            class="mt-1 block w-full"
            required
          />
          <InputError class="mt-2" :message="editForm.errors.started_at" />
        </div>
        <div>
          <Label for="edit-ended_at">Кінець</Label>
          <Input
            id="edit-ended_at"
            v-model="editForm.ended_at"
            type="datetime-local"
            step="1"
            class="mt-1 block w-full"
          />
          <InputError class="mt-2" :message="editForm.errors.ended_at" />
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <Label for="edit-team" required>Команда *</Label>
          <Select v-model="editForm.team_id" required>
            <SelectTrigger id="edit-team" class="w-full mt-1">
              <SelectValue placeholder="-- Оберіть команду --" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem v-for="team in availableTeams" :key="team.id" :value="team.id">
                  {{ team.name }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <InputError class="mt-2" :message="editForm.errors.team_id" />
        </div>
        <div>
          <Label for="edit-task">Завдання</Label>
          <Select v-model="editForm.task_id">
            <SelectTrigger id="edit-task" class="w-full mt-1">
              <SelectValue placeholder="-- Без завдання --" />
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <SelectItem :value="null">-- Без завдання --</SelectItem>
                <SelectItem v-for="task in availableTasks" :key="task.id" :value="task.id">
                  {{ task.title }}
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
          <InputError class="mt-2" :message="editForm.errors.task_id" />
        </div>
      </div>

      <div
        v-if="editForm.team_id && Object.keys(relevantCustomFieldsForEdit).length > 0"
        class="mt-4 pt-4 border-t border-border col-span-full"
      >
        <h4 class="text-sm font-medium text-foreground mb-3">Додаткові параметри:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div v-for="(fieldDef, fieldCode) in relevantCustomFieldsForEdit" :key="fieldCode">
            <Label :for="'edit_' + fieldCode" :required="fieldDef.is_required">{{
              fieldDef.name + (fieldDef.is_required ? ' *' : '')
            }}</Label>
            <Input
              v-if="['text', 'number', 'date'].includes(fieldDef.type)"
              :id="'edit_' + fieldCode"
              v-model="editForm.custom_fields[fieldCode]"
              :type="fieldDef.type"
              class="mt-1 block w-full"
              :required="fieldDef.is_required"
            />
            <Select
              v-else-if="fieldDef.type === 'select'"
              v-model="editForm.custom_fields[fieldCode]"
              :required="fieldDef.is_required"
            >
              <SelectTrigger :id="'edit_' + fieldCode" class="w-full mt-1">
                <SelectValue placeholder="-- Оберіть --" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem v-for="option in fieldDef.options" :key="option" :value="option">
                    {{ option }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
            <div v-else-if="fieldDef.type === 'boolean'" class="mt-1 flex items-center h-10">
              <Checkbox :id="'edit_' + fieldCode" v-model:checked="editForm.custom_fields[fieldCode]" class="mr-2" />
              <Label :for="'edit_' + fieldCode" class="text-sm !font-normal">Так</Label>
            </div>
            <InputError class="mt-2" :message="editForm.errors['custom_fields.' + fieldCode]" />
          </div>
        </div>
      </div>
    </div>
    <DialogFooter>
      <DialogClose as-child>
        <Button type="button" variant="secondary" @click="$emit('close')"> Скасувати </Button>
      </DialogClose>
      <Button type="submit" :disabled="editForm.processing"> Зберегти Зміни </Button>
    </DialogFooter>
  </form>
</template>
