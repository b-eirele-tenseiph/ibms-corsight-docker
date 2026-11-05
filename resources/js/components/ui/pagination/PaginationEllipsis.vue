<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { reactiveOmit } from '@vueuse/core'
import { Icon } from '@iconify/vue'
import { PaginationEllipsis, type PaginationEllipsisProps } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<PaginationEllipsisProps & { class?: HTMLAttributes['class'] }>()

const delegatedProps = reactiveOmit(props, 'class')
</script>

<template>
  <PaginationEllipsis
    data-slot="pagination-ellipsis"
    v-bind="delegatedProps"
    :class="cn('flex size-9 items-center justify-center', props.class)"
  >
    <slot>
      <Icon icon="vaadin:ellipsis-dots-h" class="size-3" />
      <span class="sr-only">More pages</span>
    </slot>
  </PaginationEllipsis>
</template>
