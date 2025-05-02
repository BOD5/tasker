import { ref, computed } from 'vue';

export function useTimeEntryModal() {
  const entryInModal = ref(null);
  const isModalOpen = computed({
    get: () => entryInModal.value !== null,
    set: (value) => {
      if (!value) {
        entryInModal.value = null;
      }
    },
  });

  const openCreateModal = () => {
    entryInModal.value = {};
  };

  const openEditModal = (entry) => {
    entryInModal.value = entry;
  };

  const closeModal = () => {
    entryInModal.value = null;
  };

  return {
    entryInModal,
    isModalOpen,
    openCreateModal,
    openEditModal,
    closeModal,
  };
}
