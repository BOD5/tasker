<script setup>
import { computed } from 'vue';
import TimeEntryDetailsFields from './TimeEntryDetailsFields.vue';

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  availableTeams: Array,
  availableTasks: Array,
  customFieldDefinitions: Array,
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
  <form @submit.prevent>
    <div class="p-4 space-y-4 max-h-[70vh] overflow-y-auto">
      <h4 class="font-medium leading-none">Додаткові параметри</h4>
      <p class="text-sm text-muted-foreground">Оберіть команду, завдання та заповніть поля.</p>
      <TimeEntryDetailsFields
        :form="form"
        :availableTeams="availableTeams"
        :availableTasks="availableTasks"
        :customFieldDefinitions="customFieldDefinitions"
        :errors="form.errors"
      />
    </div>
  </form>
</template>
