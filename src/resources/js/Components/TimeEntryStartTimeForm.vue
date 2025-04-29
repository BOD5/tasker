<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Button } from '@/Components/ui/button';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <form @submit.prevent>
    <div class="p-4 space-y-2">
      <Label for="start_time_edit" class="font-medium">Час початку</Label>
      <Input id="start_time_edit" type="datetime-local" step="1" v-model="form.started_at" class="w-full" required />
      <InputError class="mt-1" :message="form.errors?.started_at" />
    </div>
  </form>
</template>
