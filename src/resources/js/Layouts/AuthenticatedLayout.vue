<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Toaster, useToast } from '@/Components/ui/toast';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/Components/ui/sheet';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/ui/avatar';
import { Separator } from '@/Components/ui/separator';
import { LogOut, User as UserIcon, Settings, Menu } from 'lucide-vue-next';

defineProps({
  title: String,
});

const page = usePage();
const { toast } = useToast();

const showingMobileMenu = ref(false);

const logout = () => {
  router.post(route('logout'));
};

watch(
  () => page.props.flash.notification,
  (notification) => {
    if (notification && typeof notification === 'object' && notification !== null) {
      console.log('Flash notification received:', notification);

      let toastVariant = 'default';
      if (notification.type === 'error' || notification.type === 'danger') {
        toastVariant = 'destructive';
      }

      toast({
        title: notification.title ?? 'Повідомлення',
        description: notification.message ?? '',
        variant: toastVariant,
        duration: notification.duration ?? 5000,
      });

      page.props.flash.notification = null;
    }
  },
  { deep: true, immediate: false },
);
</script>

<template>
  <div>
    <Head :title="title" />

    <div class="min-h-screen w-full flex flex-col bg-background text-foreground">
      <header class="sticky top-0 z-40 w-full border-b border-border bg-card">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
              <Link :href="route('app.time-tracking.index')" class="mr-6 flex items-center space-x-2">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="h-6 w-6 text-primary"
                >
                  <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0-18 0Z" />
                  <path d="M12 7v5l3 1.5" />
                </svg>
                <span class="font-bold text-foreground hidden sm:inline-block">Tasker</span>
              </Link>
              <nav class="hidden md:flex md:items-center md:space-x-6 text-sm font-medium">
                <Link
                  :href="route('app.time-tracking.index')"
                  class="transition-colors hover:text-foreground/80"
                  :class="route().current('app.time-tracking.index') ? 'text-foreground' : 'text-muted-foreground'"
                >
                  Time Tracking
                </Link>
                <Link href="#" class="transition-colors hover:text-foreground/80 text-muted-foreground">
                  Tasks (TBD)
                </Link>
                <Link href="#" class="transition-colors hover:text-foreground/80 text-muted-foreground">
                  Projects (TBD)
                </Link>
              </nav>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-4">
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                  <Button variant="ghost" class="relative h-9 w-9 rounded-full">
                    <Avatar class="h-9 w-9">
                      <AvatarImage
                        v-if="$page.props.auth.user.profile_photo_url"
                        :src="$page.props.auth.user.profile_photo_url"
                        :alt="$page.props.auth.user.name"
                      />
                      <AvatarFallback>
                        {{ $page.props.auth.user.name.substring(0, 2).toUpperCase() }}
                      </AvatarFallback>
                    </Avatar>
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-56" align="end">
                  <DropdownMenuLabel>
                    <div class="font-medium text-sm text-foreground">{{ $page.props.auth.user.name }}</div>
                    <div class="text-xs text-muted-foreground">{{ $page.props.auth.user.email }}</div>
                  </DropdownMenuLabel>
                  <Separator />
                  <DropdownMenuItem as-child>
                    <Link :href="route('profile.edit')">
                      <UserIcon class="mr-2 h-4 w-4" />
                      <span>Profile</span>
                    </Link>
                  </DropdownMenuItem>
                  <DropdownMenuItem disabled>
                    <Settings class="mr-2 h-4 w-4" />
                    <span>Settings (TBD)</span>
                  </DropdownMenuItem>
                  <Separator />
                  <DropdownMenuItem class="cursor-pointer" @click.prevent="logout">
                    <LogOut class="mr-2 h-4 w-4" />
                    <span>Log Out</span>
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>

              <Sheet v-model:open="showingMobileMenu">
                <SheetTrigger as-child class="md:hidden">
                  <Button variant="ghost" size="icon">
                    <Menu class="h-5 w-5" />
                    <span class="sr-only">Toggle Menu</span>
                  </Button>
                </SheetTrigger>
                <SheetContent side="left" class="w-[280px]">
                  <SheetHeader class="mb-4 text-left">
                    <SheetTitle>
                      <Link
                        :href="route('app.time-tracking.index')"
                        class="flex items-center space-x-2"
                        @click="showingMobileMenu = false"
                      >
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="h-6 w-6 text-primary"
                        >
                          <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0-18 0Z" />
                          <path d="M12 7v5l3 1.5" />
                        </svg>
                        <span class="font-bold text-lg">Tasker</span>
                      </Link>
                    </SheetTitle>
                  </SheetHeader>
                  <Separator class="mb-4" />
                  <div class="flex flex-col space-y-1">
                    <Link
                      :href="route('app.time-tracking.index')"
                      class="text-base py-2 px-2 rounded-md hover:bg-accent"
                      :class="
                        route().current('app.time-tracking.index')
                          ? 'text-primary font-semibold bg-accent'
                          : 'text-muted-foreground hover:text-foreground'
                      "
                      @click="showingMobileMenu = false"
                    >
                      Time Tracking
                    </Link>
                    <Link
                      href="#"
                      class="text-base py-2 px-2 rounded-md text-muted-foreground hover:text-foreground hover:bg-accent"
                      @click="showingMobileMenu = false"
                    >
                      Tasks (TBD)
                    </Link>
                    <Link
                      href="#"
                      class="text-base py-2 px-2 rounded-md text-muted-foreground hover:text-foreground hover:bg-accent"
                      @click="showingMobileMenu = false"
                    >
                      Projects (TBD)
                    </Link>
                    <Separator class="my-2" />
                    <Link
                      :href="route('profile.edit')"
                      class="text-base py-2 px-2 rounded-md hover:bg-accent"
                      :class="
                        route().current('profile.edit')
                          ? 'text-primary font-semibold bg-accent'
                          : 'text-muted-foreground hover:text-foreground'
                      "
                      @click="showingMobileMenu = false"
                    >
                      Profile
                    </Link>
                    <Separator class="my-2" />
                    <button
                      class="w-full text-left text-base py-2 px-2 rounded-md text-muted-foreground hover:text-foreground hover:bg-accent"
                      @click.prevent="logout"
                    >
                      Log Out
                    </button>
                  </div>
                </SheetContent>
              </Sheet>
            </div>
          </div>
        </div>
      </header>

      <div v-if="$slots.header" class="border-b border-border bg-card shadow-sm">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </div>

      <main class="flex-1 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
        <slot />
      </main>

      <footer class="py-4 text-center text-sm text-muted-foreground border-t border-border mt-auto">
        Tasker App &copy; {{ new Date().getFullYear() }}
      </footer>
      <Toaster />
    </div>
  </div>
</template>
