<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useInfiniteScroll } from '@vueuse/core';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TimerControlBar from '@/Components/TimerControlBar.vue';
import TimeEntryHistoryTable from '@/Components/TimeEntryHistoryTable.vue';
import TimeEntryForm from '@/Components/TimeEntryForm.vue';
import TimeEntryFilters from '@/Components/TimeEntryFilters.vue';
import { Dialog, DialogContent } from '@/Components/ui/dialog';
import { Button } from '@/Components/ui/button';
import { useTimeEntries } from '@/Composables/useTimeEntries';
import { useTimeEntryModal } from '@/Composables/useTimeEntryModal';
import { useTimeEntryActions } from '@/Composables/useTimeEntryActions';

const props = defineProps({
  activeTimer: Object,
  timeEntries: Object,
  availableTeams: Array,
  availableTasks: Array,
  customFieldDefinitions: Array,
  errors: Object,
  filters: Object,
  lastTeamId: Number | null,
  generalTeamId: Number | null,
});

const { displayedEntries, loadingMore, loadMoreEntries, reloadEntries, hasNextPage } = useTimeEntries();
const { entryInModal, isModalOpen, openCreateModal, openEditModal, closeModal } = useTimeEntryModal();
const { submitEntry, deleteEntry, stopTimeEntry } = useTimeEntryActions(reloadEntries, closeModal);

const scrollContainerRef = ref(document.documentElement);

useInfiniteScroll(scrollContainerRef, loadMoreEntries, {
  distance: 400,
  canLoadMore: () => hasNextPage.value && !loadingMore.value,
});

const applyFilters = (filterData) => {
  reloadEntries({ ...filterData, page: 1 });
};

const applySort = (column) => {
  let direction = 'asc';
  if (props.filters?.sort === column && props.filters?.direction === 'asc') {
    direction = 'desc';
  }
  reloadEntries({ sort: column, direction: direction, page: 1 });
};
</script>

<template>
  <Head title="Time Tracking" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-foreground leading-tight">Відслідковування Часу</h2>
    </template>
    <div class="py-6" ref="scrollContainerRef">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <TimerControlBar
          :activeTimer="activeTimer"
          :availableTeams="availableTeams"
          :availableTasks="availableTasks"
          :customFieldDefinitions="customFieldDefinitions"
          :errors="$page.props.errors"
          :lastTeamId="props.lastTeamId"
          :generalTeamId="props.generalTeamId"
          @start="submitEntry"
          @stop="stopTimeEntry"
          @updateDetails="submitEntry"
        />
        <div class="text-right">
          <Button variant="outline" @click="openCreateModal"> + Додати Запис </Button>
        </div>

        <TimeEntryFilters :filters="filters" @filter="applyFilters" />

        <TimeEntryHistoryTable
          :entries="displayedEntries"
          :active-timer-id="activeTimer?.id"
          :filters="filters"
          @sort="applySort"
          @delete-entry="deleteEntry"
          @edit-entry="openEditModal"
        />

        <div v-if="loadingMore" class="text-center py-4 text-muted-foreground">Завантаження...</div>

        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
          <DialogContent class="sm:max-w-[650px] max-h-[90vh] flex flex-col">
            <TimeEntryForm
              v-if="entryInModal !== null"
              :entry="entryInModal?.id ? entryInModal : null"
              :availableTeams="availableTeams"
              :availableTasks="availableTasks"
              :customFieldDefinitions="customFieldDefinitions"
              :lastTeamId="props.lastTeamId"
              :generalTeamId="props.generalTeamId"
              :isDarkModeEnabled="false"
              @close="closeModal"
              @submit="submitEntry"
            />
          </DialogContent>
        </Dialog>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
