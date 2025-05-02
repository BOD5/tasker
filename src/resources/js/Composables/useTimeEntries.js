import { ref, watch, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

export function useTimeEntries() {
  const page = usePage();
  const initialTimeEntries = computed(() => page.props.timeEntries);
  const currentFilters = computed(() => page.props.filters);

  const displayedEntries = ref([]);
  const loadingMore = ref(false);
  const nextPageUrl = ref(null);

  const loadMoreEntries = () => {
    if (!nextPageUrl.value || loadingMore.value) {
      return;
    }
    loadingMore.value = true;

    router.get(
      nextPageUrl.value,
      {},
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['timeEntries'],
        onSuccess: (newPage) => {
          displayedEntries.value = [...displayedEntries.value, ...(newPage.props.timeEntries.data ?? [])];
          nextPageUrl.value = newPage.props.timeEntries.next_page_url;
          loadingMore.value = false;
        },
        onError: () => {
          loadingMore.value = false;
        },
        onFinish: () => {
          loadingMore.value = false;
        },
      },
    );
  };

  watch(
    initialTimeEntries,
    (newPaginator) => {
      if (newPaginator?.current_page === 1) {
        displayedEntries.value = newPaginator?.data ?? [];
      } else {
        const existingIds = new Set(displayedEntries.value.map((e) => e.id));
        const newUniqueData = (newPaginator?.data ?? []).filter((e) => !existingIds.has(e.id));
        displayedEntries.value = [...displayedEntries.value, ...newUniqueData];
      }
      nextPageUrl.value = newPaginator?.next_page_url ?? null;
      loadingMore.value = false;
    },
    { deep: true, immediate: true },
  );

  const reloadEntries = (newFilters = {}) => {
    router.get(
      route('app.time-tracking.index'),
      {
        ...currentFilters.value,
        ...newFilters,
        page: 1,
      },
      {
        preserveState: true,
        preserveScroll: true,
        replace: true,
      },
    );
  };

  return {
    displayedEntries,
    loadingMore,
    loadMoreEntries,
    reloadEntries,
    hasNextPage: computed(() => !!nextPageUrl.value),
  };
}
