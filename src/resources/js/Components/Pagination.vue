<script setup>
import { Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';

defineProps({
  links: {
    type: Array,
    default: () => [],
  },
});
</script>

<template>
  <div v-if="links.length > 3" class="flex justify-center items-center space-x-1 mt-6">
    <template v-for="(link, key) in links" :key="key">
      <Button
        v-if="link.url"
        as="a"
        :href="link.url"
        preserve-scroll
        preserve-state
        v-html="link.label"
        size="sm"
        :variant="link.active ? 'outline' : 'ghost'"
        :class="{ 'font-bold border-primary text-primary': link.active, 'text-muted-foreground': !link.active }"
        class="h-9 px-3"
      />
      <Button
        v-else
        v-html="link.label"
        size="sm"
        variant="ghost"
        class="h-9 px-3 text-muted-foreground/50 cursor-not-allowed"
        disabled
      />
    </template>
  </div>
</template>
