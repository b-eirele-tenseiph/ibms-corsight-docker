<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { reactiveOmit } from '@vueuse/core'
import {
  DialogClose,
  DialogContent,
  DialogTitle,
  type DialogContentEmits,
  type DialogContentProps,
  DialogPortal,
  useForwardPropsEmits,
  VisuallyHidden,
} from 'reka-ui'
import SheetOverlay from './SheetOverlay.vue'
import SheetDescription from './SheetDescription.vue'
import { Icon } from '@iconify/vue'

interface SheetContentProps extends DialogContentProps {
  class?: HTMLAttributes['class']
  side?: 'top' | 'right' | 'bottom' | 'left'
  customClosable?: boolean
  showClose?: boolean
}

defineOptions({
  inheritAttrs: false,
})

const props = withDefaults(defineProps<SheetContentProps>(), {
    side: 'right',
    customClosable: true,
    showClose: true,
})
const emits = defineEmits<DialogContentEmits>()

const delegatedProps = reactiveOmit(props, 'class', 'side')

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <DialogPortal>
    <SheetOverlay />
    <DialogContent
      data-slot="sheet-content"
      :aria-describedby="undefined"
      :class="cn(
        'bg-background data-[state=open]:animate-in data-[state=closed]:animate-out fixed z-50 flex flex-col gap-4 shadow-lg transition ease-in-out data-[state=closed]:duration-300 data-[state=open]:duration-500',
        side === 'right'
          && 'data-[state=closed]:slide-out-to-right data-[state=open]:slide-in-from-right inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm',
        side === 'left'
          && 'data-[state=closed]:slide-out-to-left data-[state=open]:slide-in-from-left inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm',
        side === 'top'
          && 'data-[state=closed]:slide-out-to-top data-[state=open]:slide-in-from-top inset-x-0 top-0 h-auto border-b',
        side === 'bottom'
          && 'data-[state=closed]:slide-out-to-bottom data-[state=open]:slide-in-from-bottom inset-x-0 bottom-0 h-auto border-t',
        props.class)"
      v-bind="{ ...forwarded, ...$attrs }"
    >
      <slot />
      <VisuallyHidden asChild>
        <DialogTitle>
        </DialogTitle>
        <SheetDescription>
        </SheetDescription>
      </VisuallyHidden>
      <VisuallyHidden asChild>
        <DialogTitle>
            Sidebar
        </DialogTitle>
      </VisuallyHidden>
      <VisuallyHidden asChild>
      <DialogClose
        v-if="showClose && customClosable"
        class="ring-offset-background cursor-pointer focus:ring-ring data-[state=open]:bg-secondary absolute top-2 right-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none"
      >
        <Icon icon="lucide-x" class="size-5 text-secondary-text"/>
        <span class="sr-only">Close</span>
      </DialogClose>
      </VisuallyHidden>
    </DialogContent>
  </DialogPortal>
</template>
