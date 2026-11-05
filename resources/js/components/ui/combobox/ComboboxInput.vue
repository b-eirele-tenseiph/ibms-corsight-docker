<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Icon } from '@iconify/vue'
import { ComboboxInput, type ComboboxInputEmits, type ComboboxInputProps, useForwardPropsEmits } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

defineOptions({
  inheritAttrs: false,
})

const props = defineProps<ComboboxInputProps & {
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<ComboboxInputEmits>()

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props

  return delegated
})

const forwarded = useForwardPropsEmits(delegatedProps, emits)
</script>

<template>
  <div
    data-slot="command-input-wrapper"
    class="flex items-center gap-2"
  >

    <ComboboxInput
      data-slot="command-input"
      :class="cn(
        'placeholder:text-muted-foreground flex h-10 w-full rounded-md pl-3 bg-transparent py-3 text-sm outline-hidden disabled:cursor-not-allowed disabled:opacity-50 border border-input',
        props.class,
      )"

      v-bind="{ ...forwarded, ...$attrs }"
    >
      <slot />
    </ComboboxInput>
  </div>
</template>
