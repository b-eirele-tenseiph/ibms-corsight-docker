<script setup lang="ts">
import { Sheet, SheetTrigger } from '@/components/ui/sheet';
import { HTMLAttributes } from 'vue';
import SideSheetContent from './SideSheetContent.vue';
const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <Sheet>
        <SheetTrigger>
            <slot name="trigger" />
        </SheetTrigger>
        <SideSheetContent :class="props.class">
            <slot name="content" />
        </SideSheetContent>
    </Sheet>
</template>
