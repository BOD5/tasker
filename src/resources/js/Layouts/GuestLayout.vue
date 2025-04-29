<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-background text-foreground">
    <div class="mb-6">
      <Link href="/">
        <span class="text-3xl font-bold text-foreground">Tasker</span>
      </Link>
    </div>
    <div class="w-full sm:max-w-md px-6 py-8 bg-card text-card-foreground shadow-md overflow-hidden sm:rounded-lg">
      <slot />
    </div>
  </div>
</template>
