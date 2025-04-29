<script setup>
import { ref, computed } from 'vue'; // Додаємо ref
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TimerControlBar from '@/Components/TimerControlBar.vue';
import TimeEntryHistoryTable from '@/Components/TimeEntryHistoryTable.vue';
import EditTimeEntryForm from '@/Components/EditTimeEntryForm.vue'; // <-- Імпортуємо форму редагування
import { Dialog, DialogContent } from '@/Components/ui/dialog'; // <-- Імпортуємо Dialog

const props = defineProps({
  activeTimer: Object,
  timeEntries: Object,
  availableTeams: Array,
  availableTasks: Array,
  customFieldDefinitions: Array,
  errors: Object,
  filters: Object,
});

const entryToEdit = ref(null);
const isEditDialogOpen = computed({
  get: () => !!entryToEdit.value,
  set: (value) => {
    if (!value) {
      entryToEdit.value = null;
    }
  },
});

const handleChangePeriod = (period) => {
  router.get(
    route('app.time-tracking.index'),
    { period: period },
    {
      preserveState: true, // Зберігаємо стан форми в TimerControlBar
      replace: true,
    },
  );
};

const handleDeleteEntry = (entryId) => {
  if (confirm('Ви впевнені, що хочете видалити цей запис часу?')) {
    router.delete(route('app.time-entries.destroy', entryId), {
      preserveScroll: true,
    });
  }
};

// Обробник події редагування - встановлює запис і відкриває модалку
const handleEditEntry = (entry) => {
  console.log('Editing entry:', entry); // Для дебагу
  entryToEdit.value = entry; // Встановлюємо запис, що призведе до isEditDialogOpen = true
};

// Обробник події закриття модалки від дочірнього компонента
const closeEditModal = () => {
  entryToEdit.value = null; // Скидаємо запис, що призведе до isEditDialogOpen = false
};
</script>

<template>
  <Head title="Time Tracking" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-foreground leading-tight">Відслідковування Часу</h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <TimerControlBar
          :activeTimer="activeTimer"
          :availableTeams="availableTeams"
          :availableTasks="availableTasks"
          :customFieldDefinitions="customFieldDefinitions"
          :errors="errors"
        />

        <TimeEntryHistoryTable
          :time-entries="timeEntries"
          :active-timer-id="activeTimer?.id"
          :filters="filters"
          @change-period="handleChangePeriod"
          @delete-entry="handleDeleteEntry"
          @edit-entry="handleEditEntry"
        />

        <Dialog :open="isEditDialogOpen" @update:open="isEditDialogOpen = $event">
          <DialogContent class="sm:max-w-[650px]">
            <EditTimeEntryForm
              v-if="entryToEdit"
              :entry="entryToEdit"
              :availableTeams="availableTeams"
              :availableTasks="availableTasks"
              :customFieldDefinitions="customFieldDefinitions"
              @close="closeEditModal"
            />
          </DialogContent>
        </Dialog>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
