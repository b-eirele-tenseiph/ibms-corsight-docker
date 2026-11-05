<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Icon } from '@iconify/vue';
import dayjs from 'dayjs';
import { ref } from 'vue';

const date = defineModel<string>();

const isOpen = ref(false);

const viewYear = ref(date.value ? dayjs(date.value).year() : dayjs().year());
const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

const selectMonth = (index: number) => {
    date.value = dayjs().year(viewYear.value).month(index).date(1).format('YYYY-MM-DD');
    isOpen.value = false;
};
</script>

<template>
    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                v-bind="$attrs"
                class="h-10 w-full justify-between bg-transparent font-normal hover:bg-transparent focus-visible:ring"
            >
                <span class="fluid-text-xs-sm">{{ date ? dayjs(date).format('MMMM YYYY') : $t('filter.select_month') }}</span>
                <Icon icon="lucide:calendar-days" class="size-4 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-fit p-4" align="center">
            <div class="mb-4 flex w-full items-center justify-between">
                <Button variant="ghost" size="icon" class="size-6" @click="viewYear--"><Icon icon="lucide:chevron-left" /></Button>
                <div class="text-sm font-semibold">{{ viewYear }}</div>
                <Button variant="ghost" size="icon" class="size-6" @click="viewYear++"><Icon icon="lucide:chevron-right" /></Button>
            </div>
            <div class="grid w-full grid-cols-3 gap-2">
                <Button
                    v-for="(month, i) in months"
                    :key="month"
                    class="h-10 text-sm"
                    @click="selectMonth(i)"
                    :variant="date && dayjs(date).year() === viewYear && dayjs(date).month() === i ? 'default' : 'ghost'"
                >
                    {{ month }}
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>
