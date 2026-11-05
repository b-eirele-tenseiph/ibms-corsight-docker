<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import {
  AccordionHeader,
  AccordionTrigger,
  type AccordionTriggerProps,
} from 'reka-ui'
import { cn } from '@/lib/utils'
import { Icon } from '@iconify/vue'

const props = defineProps<AccordionTriggerProps & { class?: HTMLAttributes['class'], iconClass?: string, }>()

const delegatedProps = reactiveOmit(props, 'class', 'iconClass')
</script>

<template>
  <AccordionHeader class="flex">
    <AccordionTrigger
      data-slot="accordion-trigger"
      v-bind="delegatedProps"
      :class="
        cn(
          'focus-visible:border-ring focus-visible:ring-ring/50 flex flex-1 items-start justify-between gap-4 rounded-md py-4 text-left text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 [&[data-state=open]>svg]:rotate-180',
          props.class,
        )
      "
    >
      <slot />
      <slot name="icon">
        <Icon icon="lucide:chevron-down" :class="cn('h-4 w-4 shrink-0 transition-transform duration-200', props.iconClass)" />
      </slot>
    </AccordionTrigger>
  </AccordionHeader>
</template>
