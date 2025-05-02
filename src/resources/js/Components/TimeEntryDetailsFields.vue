<script setup>
import { computed } from 'vue';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Checkbox } from '@/Components/ui/checkbox';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  form: { type: Object, required: true },
  availableTeams: { type: Array, default: () => [] },
  availableTasks: { type: Array, default: () => [] },
  customFieldDefinitions: { type: Array, default: () => [] },
  errors: Object,
});
const relevantCustomFields = computed(() => {
  const currentTeamId = props.form.team_id;
  if (!currentTeamId || !props.customFieldDefinitions) return {};
  const fields = {};
  props.customFieldDefinitions
    .filter((def) => def.team_id === null || def.team_id === currentTeamId)
    .forEach((def) => {
      fields[def.code] = def;
      if (props.form.custom_fields[def.code] === undefined) {
        props.form.custom_fields[def.code] = def.type === 'boolean' ? false : null;
      }
    });
  return fields;
});
</script>

<template>
  <div class="pt-4 space-y-4">
    <div>
      <Label for="details-team" required>Команда *</Label>
      <Select v-model="form.team_id" required>
        <SelectTrigger id="details-team" class="w-full mt-1">
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
      <InputError class="mt-2" :message="errors?.team_id" />
    </div>
    <div>
      <Label for="details-task">Завдання (опціонально)</Label>
      <Select v-model="form.task_id">
        <SelectTrigger id="details-task" class="w-full mt-1">
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
      <InputError class="mt-2" :message="errors?.task_id" />
    </div>

    <div
      v-if="form.team_id && Object.keys(relevantCustomFields).length > 0"
      class="mt-4 pt-4 border-t border-border col-span-full"
    >
      <h4 class="text-sm font-medium text-foreground mb-3">
        Параметри команди "{{ availableTeams?.find((t) => t.id === form.team_id)?.name }}":
      </h4>
      <div class="grid grid-cols-1 gap-4">
        <div v-for="(fieldDef, fieldCode) in relevantCustomFields" :key="fieldCode">
          <Label :for="'details_' + fieldCode" :required="fieldDef.is_required">{{
            fieldDef.name + (fieldDef.is_required ? ' *' : '')
          }}</Label>
          <Input
            v-if="['text', 'number', 'date'].includes(fieldDef.type)"
            :id="'details_' + fieldCode"
            v-model="form.custom_fields[fieldCode]"
            :type="fieldDef.type"
            class="mt-1 block w-full"
            :required="fieldDef.is_required"
          />
          <Select
            v-else-if="fieldDef.type === 'select'"
            v-model="form.custom_fields[fieldCode]"
            :required="fieldDef.is_required"
          >
            <SelectTrigger :id="'details_' + fieldCode" class="w-full mt-1">
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
            <Checkbox :id="'details_' + fieldCode" v-model:checked="form.custom_fields[fieldCode]" class="mr-2" />
            <Label :for="'details_' + fieldCode" class="text-sm !font-normal">Так</Label>
          </div>
          <InputError class="mt-2" :message="errors ? errors['custom_fields.' + fieldCode] : ''" />
        </div>
      </div>
    </div>
  </div>
</template>
