<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { useSidebar } from './utils'
import { Icon } from '@iconify/vue';

const props = defineProps<{
    class?: HTMLAttributes['class']
    icon?: string
}>()

const { toggleSidebar } = useSidebar()
</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-7 w-7', props.class)"
    @click="toggleSidebar"
  >
    <Icon :icon="icon" class="size-7"  />
    <span class="sr-only">Toggle Sidebar</span>
  </Button>
</template>
