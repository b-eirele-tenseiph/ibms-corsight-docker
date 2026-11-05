<script setup lang="ts">
import type { SwitchRootEmits, SwitchRootProps } from 'reka-ui'
import type { HTMLAttributes } from 'vue'
import type { SwitchVariantsProps,  SwitchThumbVariantsProps } from '.'
import { cn } from '@/lib/utils'
import { SwitchRoot, SwitchThumb, useForwardPropsEmits } from 'reka-ui'
import { computed } from 'vue'
import { switchThumbVariants, switchVariants } from '.'

const props = withDefaults(defineProps<SwitchRootProps & {
  rootClass?: HTMLAttributes['class'],
  thumbClass?: HTMLAttributes['class'],
  rootSize?: SwitchVariantsProps['size']
  thumbSize?: SwitchThumbVariantsProps['size']
}>(), {
  rootSize: 'base',
  thumbSize: 'base'
})

const emits = defineEmits<SwitchRootEmits>()
const forwarded = useForwardPropsEmits(props, emits)

const switchRootClassName = computed(() => {
    return cn(switchVariants({size: props.rootSize}),
    props.rootClass
  )
})

const switchThumbClassName = computed(() => {
  return cn(switchThumbVariants({size: props.thumbSize}),
    props.thumbClass
  )
})
</script>

<template>
  <SwitchRoot data-slot="switch" v-bind="forwarded" :class="switchRootClassName">
    <SwitchThumb data-slot="switch-thumb" :class="switchThumbClassName">
      <slot name="thumb" />
    </SwitchThumb>
  </SwitchRoot>
</template>
