<script setup>
import { ref, computed, watch, toRef } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import TimeEntryDetailsForm from '@/Components/TimeEntryDetailsForm.vue';
import TimeEntryStartTimeForm from '@/Components/TimeEntryStartTimeForm.vue';
import { Play, Square, Settings2 } from 'lucide-vue-next';
import { useDateTimeFormatters } from '@/Composables/useDateTimeFormatters';
import { useRunningTimer } from '@/Composables/useRunningTimer';

const props = defineProps({
  activeTimer: Object,
  availableTeams: Array,
  availableTasks: Array,
  customFieldDefinitions: Array,
  lastTeamId: Number | null,
  generalTeamId: Number | null,
  errors: Object,
});

const emit = defineEmits(['start', 'stop', 'updateDetails']);

const { formatForInput, dateToUTC } = useDateTimeFormatters();
const { runningDuration } = useRunningTimer(toRef(props, 'activeTimer'));

const form = useForm({
  description: '',
  team_id: props.lastTeamId ?? props.generalTeamId ?? props.availableTeams?.[0]?.id ?? null,
  task_id: null,
  custom_fields: {},
  started_at: '',
});

const isDetailsPopoverOpen = ref(false);
const isTimePopoverOpen = ref(false);

watch(
  () => props.activeTimer,
  (timer) => {
    if (timer) {
      const initialCustomFields = {};
      const relevantFields =
        props.customFieldDefinitions?.filter((def) => def.team_id === null || def.team_id === timer.team_id) ?? [];
      relevantFields.forEach((def) => {
        const existingValue = timer.custom_field_values?.find(
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
          description: timer.description,
          team_id: timer.team_id,
          task_id: timer.task_id,
          started_at: formatForInput(timer.started_at),
          custom_fields: initialCustomFields,
        })
        .reset();
    } else {
      form.reset();
      form.started_at = '';
      form.team_id = props.lastTeamId ?? props.generalTeamId ?? props.availableTeams?.[0]?.id ?? null;
    }
  },
  { immediate: true, deep: true },
);

const isTimerRunning = computed(() => !!props.activeTimer);

const triggerStartTimer = () => {
  emit('start', form);
};

const triggerStopTimer = () => {
  if (!props.activeTimer) return;
  emit('stop', props.activeTimer.id);
};

const triggerUpdateDetails = () => {
  if (!isTimerRunning.value || !form.isDirty) {
    if (isDetailsPopoverOpen.value && !form.isDirty) isDetailsPopoverOpen.value = false;
    if (isTimePopoverOpen.value && !form.isDirty) isTimePopoverOpen.value = false;
    return;
  }
  const transformedData = {
    description: form.description,
    team_id: form.team_id,
    task_id: form.task_id,
    custom_fields: form.custom_fields,
    started_at: dateToUTC(form.started_at),
  };
  emit('updateDetails', { id: props.activeTimer.id, data: transformedData });
  isDetailsPopoverOpen.value = false;
  isTimePopoverOpen.value = false;
};

watch(isDetailsPopoverOpen, (newValue, oldValue) => {
  if (oldValue === true && newValue === false && isTimerRunning.value) {
    triggerUpdateDetails();
  }
});

watch(isTimePopoverOpen, (newValue, oldValue) => {
  if (oldValue === true && newValue === false && isTimerRunning.value) {
    const originalFormatted = formatForInput(props.activeTimer?.started_at);
    if (form.started_at !== originalFormatted) {
      triggerUpdateDetails();
    }
  }
});
</script>

<template>
  <div class="p-4 sm:p-6 bg-card text-card-foreground shadow sm:rounded-lg">
    <div class="flex items-center justify-between gap-4">
      <div class="flex-1">
        <Input
          id="description"
          type="text"
          class="w-full text-base"
          placeholder="Що ви робите?"
          v-model="form.description"
          :disabled="form.processing"
          @blur="isTimerRunning ? triggerUpdateDetails() : null"
          @keyup.enter="isTimerRunning ? triggerUpdateDetails() : triggerStartTimer()"
        />
        <p v-if="errors?.description || form.errors.description" class="text-sm text-destructive mt-1">
          {{ errors?.description ?? form.errors.description }}
        </p>
      </div>

      <div class="flex items-center gap-3 flex-shrink-0">
        <Popover v-model:open="isDetailsPopoverOpen">
          <PopoverTrigger as-child>
            <Button variant="outline" size="sm" :disabled="form.processing">
              <Settings2 class="h-4 w-4 mr-0 sm:mr-2" />
              <span class="hidden sm:inline">Деталі</span>
            </Button>
          </PopoverTrigger>
          <PopoverContent class="w-screen max-w-xs sm:max-w-md p-0">
            <TimeEntryDetailsForm
              :form="form"
              :availableTeams="availableTeams"
              :availableTasks="availableTasks"
              :customFieldDefinitions="customFieldDefinitions"
              :errors="errors ?? form.errors"
            />
          </PopoverContent>
        </Popover>

        <Popover v-model:open="isTimePopoverOpen">
          <PopoverTrigger as-child :disabled="!isTimerRunning || form.processing">
            <span
              class="w-28 text-xl font-semibold font-mono text-foreground tabular-nums text-center cursor-pointer hover:text-primary"
              title="Змінити час початку"
            >
              {{ runningDuration }}
            </span>
          </PopoverTrigger>
          <PopoverContent class="w-auto p-0">
            <TimeEntryStartTimeForm :form="form" />
            <div class="p-3 border-t border-border flex justify-end">
              <Button type="button" size="sm" @click="triggerUpdateDetails" :disabled="form.processing"
                >Зберегти час</Button
              >
            </div>
          </PopoverContent>
        </Popover>

        <Button
          :variant="isTimerRunning ? 'destructive' : 'default'"
          size="lg"
          @click="isTimerRunning ? triggerStopTimer() : triggerStartTimer()"
          :disabled="form.processing || (!isTimerRunning && (!form.description || !form.team_id))"
          class="w-28"
        >
          <Square v-if="isTimerRunning" class="h-5 w-5 mr-2" />
          <Play v-else class="h-5 w-5 mr-2" />
          {{ isTimerRunning ? 'Стоп' : 'Старт' }}
        </Button>
      </div>
    </div>
  </div>
</template>
