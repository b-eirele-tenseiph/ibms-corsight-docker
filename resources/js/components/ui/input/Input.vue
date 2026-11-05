<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { useVModel } from '@vueuse/core'

const props = defineProps<{
  defaultValue?: string | number
  modelValue?: string | number
  class?: HTMLAttributes['class']
}>()

const emits = defineEmits<{
  (e: 'update:modelValue', payload: string | number): void
}>()

const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
})
</script>

<template>
  <input
    v-model="modelValue"
    data-slot="input"
    :class="cn(
      'file:bg-secondary file:cursor-pointer placeholder:text-secondary-text dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 file:rounded-l-sm file:px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:mr-4 file:h-full file:text-sm file:font-semibold disabled:cursor-not-allowed disabled:bg-accent/70 disabled:opacity-75 md:text-sm',
      'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
      'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
      props.class,
    )"
  >
</template>
