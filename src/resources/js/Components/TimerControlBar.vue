<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import InputError from '@/Components/InputError.vue';
import { useToast } from '@/Components/ui/toast/use-toast';
import { Play, Square, Settings2 } from 'lucide-vue-next';
import TimeEntryDetailsForm from '@/Components/TimeEntryDetailsForm.vue';
import TimeEntryStartTimeForm from '@/Components/TimeEntryStartTimeForm.vue';

const props = defineProps({
  activeTimer: Object,
  availableTeams: Array,
  availableTasks: Array,
  customFieldDefinitions: Array,
  lastTeamId: Number | null,
  generalTeamId: Number | null,
  errors: Object,
});

const { toast } = useToast();

const form = useForm({
  description: '',
  team_id: props.lastTeamId ?? props.generalTeamId ?? props.availableTeams?.[0]?.id ?? null,
  task_id: null,
  custom_fields: {},
  started_at: '',
});

const isDetailsPopoverOpen = ref(false);
const isTimePopoverOpen = ref(false);

const formatForInput = (dateTimeString) => {
  if (!dateTimeString) return '';
  try {
    const date = new Date(dateTimeString);
    const offset = date.getTimezoneOffset() * 60000;
    const localDate = new Date(date.getTime() - offset);
    return localDate.toISOString().slice(0, 19);
  } catch (e) {
    return '';
  }
};

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

const runningDuration = ref('00:00:00');
let intervalId = null;
const updateRunningDuration = () => {
  if (props.activeTimer?.started_at) {
    try {
      const start = new Date(props.activeTimer.started_at);
      if (isNaN(start.getTime())) {
        runningDuration.value = '--:--:--';
        return;
      }
      const now = new Date();
      const diffInSeconds = Math.max(0, Math.round((now - start) / 1000));
      const hours = Math.floor(diffInSeconds / 3600);
      const minutes = Math.floor((diffInSeconds % 3600) / 60);
      const seconds = diffInSeconds % 60;
      runningDuration.value = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    } catch (e) {
      runningDuration.value = 'Error';
    }
  } else {
    runningDuration.value = '00:00:00';
  }
};
watch(
  () => props.activeTimer,
  (newTimer) => {
    if (newTimer && newTimer.id && newTimer.started_at) {
      if (!intervalId) {
        updateRunningDuration();
        intervalId = setInterval(updateRunningDuration, 1000);
      }
    } else if (intervalId) {
      clearInterval(intervalId);
      intervalId = null;
      runningDuration.value = '00:00:00';
    }
  },
  { immediate: true, deep: true },
);
onUnmounted(() => {
  if (intervalId) clearInterval(intervalId);
});

const startTimer = () => {
  form
    .transform((data) => ({
      description: data.description,
      team_id: data.team_id,
      task_id: data.task_id,
      custom_fields: data.custom_fields,
    }))
    .post(route('app.time-entries.store'), {
      preserveScroll: true,
      onSuccess: () => {},
    });
};

const stopTimer = () => {
  if (!props.activeTimer) return;
  router.put(
    route('app.time-entries.stop', props.activeTimer.id),
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        form.reset();
      },
    },
  );
};

const updateTimerDetails = () => {
  if (!isTimerRunning.value || !form.isDirty) {
    return;
  }
  form
    .transform((data) => {
      let startUTC = null;
      try {
        if (data.started_at) {
          startUTC = new Date(data.started_at).toISOString();
        }
      } catch (e) {
        console.error('Error parsing start date for save:', data.started_at, e);
      }
      return {
        description: data.description,
        team_id: data.team_id,
        task_id: data.task_id,
        custom_fields: data.custom_fields,
        started_at: startUTC ?? props.activeTimer?.started_at,
      };
    })
    .put(route('app.time-entries.update', props.activeTimer.id), {
      preserveScroll: true,
      preserveState: 'errors',
      onSuccess: () => {
        toast({ title: 'Деталі оновлено' });
        form.defaults().reset();
      },
      onError: (errors) => {
        toast({ title: 'Помилка оновлення', description: Object.values(errors).join(' '), variant: 'destructive' });
        form.reset();
      },
    });
};

watch(isDetailsPopoverOpen, (newValue, oldValue) => {
  if (oldValue === true && newValue === false && isTimerRunning.value) {
    updateTimerDetails();
  }
});

watch(isTimePopoverOpen, (newValue, oldValue) => {
  if (oldValue === true && newValue === false && isTimerRunning.value) {
    const originalFormatted = formatForInput(props.activeTimer?.started_at);
    if (form.started_at !== originalFormatted) {
      updateTimerDetails();
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
          @blur="isTimerRunning ? updateTimerDetails() : null"
          @keyup.enter="isTimerRunning ? updateTimerDetails() : startTimer()"
        />
        <InputError class="mt-1 text-xs" :message="form.errors.description || errors?.description" />
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
              :errors="errors"
            />
          </PopoverContent>
        </Popover>

        <Popover v-model:open="isTimePopoverOpen">
          <PopoverTrigger as-child>
            <span
              class="w-28 text-xl font-semibold font-mono text-foreground tabular-nums text-center transition-colors duration-150"
              :class="{
                'cursor-pointer hover:text-primary': isTimerRunning && !form.processing,
                'cursor-not-allowed opacity-50 pointer-events-none': !isTimerRunning || form.processing,
              }"
              title="Змінити час початку"
            >
              {{ runningDuration }}
            </span>
          </PopoverTrigger>
          <PopoverContent class="w-auto p-0">
            <TimeEntryStartTimeForm :form="form" />
          </PopoverContent>
        </Popover>

        <Button
          :variant="isTimerRunning ? 'destructive' : 'default'"
          size="lg"
          @click="isTimerRunning ? stopTimer() : startTimer()"
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
