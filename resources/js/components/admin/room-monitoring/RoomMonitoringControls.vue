<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Label } from '@/components/ui/label';
import { ControlsDeviceList } from '@/types';
import { Icon } from '@iconify/vue';

defineProps<{
    accordionItems: Array<ControlsDeviceList>;
}>();
</script>

<template>
    <Accordion type="single" collapsible class="flex w-full flex-col gap-2">
        <AccordionItem v-for="item in accordionItems" :key="item.id" :value="item.id" class="flex flex-col gap-2 border-b-0">
            <AccordionTrigger class="bg-accent/60 flex items-center gap-1 overflow-hidden rounded-lg px-2 focus-visible:ring-0">
                <Label class="text-primary-text fluid-text-xs-sm h-full flex-1 truncate font-semibold"> {{ item.title }} </Label>
                <span
                    v-for="(on, index) in item.indicators"
                    :key="index"
                    :class="[on ? 'bg-success' : 'bg-destructive', 'size-3 shrink-0 rounded-full md:size-4']"
                ></span>

                <template #icon>
                    <Icon icon="lucide:chevron-down" class="text-secondary-text size-4 md:size-5" />
                </template>
            </AccordionTrigger>

            <AccordionContent
                :class="
                    ['projector', 'television', 'thermo', 'aircon'].includes(item.type)
                        ? 'w-full'
                        : item.props.light?.model == 'WS503'
                          ? 'xs:w-3/5 w-full'
                          : 'xs:w-52 w-full'
                "
            >
                <component :is="item.component" v-bind="item.props" />
            </AccordionContent>
        </AccordionItem>
    </Accordion>
</template>
