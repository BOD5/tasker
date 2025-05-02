// src/resources/js/Composables/useTimeEntryActions.js
import { router, usePage } from '@inertiajs/vue3';
import { useToast } from '@/Components/ui/toast/use-toast';
import { useDateTimeFormatters } from '@/Composables/useDateTimeFormatters';

export function useTimeEntryActions(reloadEntriesCallback, closeModalCallback) {
  const { toast } = useToast();
  const { dateToUTC } = useDateTimeFormatters();

  const submitEntry = (formData) => {
    const isEditing = !!formData.id;
    const routeName = isEditing ? 'app.time-entries.update' : 'app.time-entries.store';
    const method = isEditing ? 'put' : 'post';
    const entryId = isEditing ? formData.id : undefined;

    const payload = {
      description: formData.description,
      team_id: formData.team_id,
      task_id: formData.task_id,
      started_at: dateToUTC(formData.started_at),
      ended_at: dateToUTC(formData.ended_at),
      custom_fields: formData.custom_fields,
    };

    router.visit(route(routeName, entryId), {
      method: method,
      data: payload,
      preserveScroll: true,
      preserveState: false,
      onSuccess: () => {
        closeModalCallback();
        toast({ title: isEditing ? 'Запис оновлено' : 'Запис успішно додано' });
      },
      onError: (errors) => {
        formData.setError(errors);
        toast({
          title: isEditing ? 'Помилка оновлення' : 'Помилка додавання запису',
          description: 'Будь ласка, перевірте форму.',
          variant: 'destructive',
        });
      },
    });
  };

  const stopTimeEntry = (entryId) => {
    router.put(
      route('app.time-entries.stop', entryId),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          reloadEntriesCallback({ page: usePage().props.timeEntries?.current_page });
        },
      },
    );
  };

  const deleteEntry = (entryId) => {
    if (confirm('Ви впевнені?')) {
      router.delete(route('app.time-entries.destroy', entryId), {
        preserveScroll: true,
        onSuccess: () => {
          toast({ title: 'Запис видалено' });
          reloadEntriesCallback({ page: usePage().props.timeEntries?.current_page });
        },
        onError: (errors) => {
          toast({ title: 'Помилка видалення', description: Object.values(errors).join(' '), variant: 'destructive' });
        },
      });
    }
  };

  return { submitEntry, deleteEntry, stopTimeEntry };
}
