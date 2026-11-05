<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Icon } from '@iconify/vue';
import { type DateValue, fromDate, parseDate } from '@internationalized/date';
import dayjs from 'dayjs';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    modelValue?: string;
}>();

const emit = defineEmits<{
    (e: 'date-selected', date: string): void;
}>();

const tz = 'Asia/Manila';

const selectedDate = ref<DateValue>(props.modelValue ? parseDate(props.modelValue) : parseDate(new Date().toISOString().split('T')[0]));

// Sync prop -> state
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal) {
            selectedDate.value = fromDate(new Date(newVal), tz);
        }
    },
);

// Emit state -> parent
watch(selectedDate, (val) => {
    const formatted = new Intl.DateTimeFormat('en-CA', {
        timeZone: tz,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(val.toDate(tz));
    emit('date-selected', formatted);
});

// --- Dropdown logic ---
const jsDate = computed(() => selectedDate.value.toDate(tz));
const selectedMonth = ref(jsDate.value.getMonth());
const selectedYear = ref(jsDate.value.getFullYear());

const monthOptions = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const yearOptions = Array.from({ length: 31 }, (_, i) => 2000 + i);
const selectedMonthLabel = computed(() => monthOptions[selectedMonth.value] ?? 'Month');
const selectedYearLabel = computed(() => selectedYear.value?.toString() ?? 'Year');

// On dropdown change
const onMonthYearChange = () => {
    selectedDate.value = fromDate(new Date(selectedYear.value, selectedMonth.value, 1), tz);
};

// Keep dropdowns in sync with selectedDate
watch(selectedDate, (val) => {
    const d = val.toDate(tz);
    selectedMonth.value = d.getMonth();
    selectedYear.value = d.getFullYear();
});

// --- Date navigation ---
const changeDate = (days: number) => {
    selectedDate.value = selectedDate.value.add({ days });
};
</script>

<template>
    <div class="relative w-full items-center">
        <Popover>
            <PopoverTrigger as-child>
                <Button variant="outline" class="flex w-full items-center justify-between gap-1 bg-white px-2 text-left font-normal hover:bg-white">
                    <div class="text-secondary-text flex gap-1">
                        <button class="hover:cursor-pointer" @click.stop.prevent="changeDate(-7)" title="Previous week">
                            <Icon icon="ci:chevron-left-duo" class="size-6" />
                        </button>
                        <button class="hover:cursor-pointer" @click.stop.prevent="changeDate(-1)" title="Previous day">
                            <Icon icon="ci:chevron-left-md" class="size-6" />
                        </button>
                    </div>

                    <span class="fluid-text-xs-sm text-primary-text mx-auto text-center">
                        {{ dayjs(selectedDate.toDate(tz)).format('MMM. DD, YYYY') }}
                    </span>

                    <div class="text-secondary-text flex gap-1">
                        <button class="hover:cursor-pointer" @click.stop.prevent="changeDate(1)" title="Next day">
                            <Icon icon="ci:chevron-right-md" class="size-6" />
                        </button>
                        <button class="hover:cursor-pointer" @click.stop.prevent="changeDate(7)" title="Next week">
                            <Icon icon="ci:chevron-right-duo" class="size-6" />
                        </button>
                    </div>
                </Button>
            </PopoverTrigger>

            <PopoverContent class="fluid-text-xs-sm xs:-ml-2 ml-2 flex w-full max-w-xs flex-col gap-y-2 p-2">
                <!-- Month & Year Selects -->
                <div class="flex gap-2">
                    <Select v-model="selectedMonth" @update:model-value="onMonthYearChange">
                        <SelectTrigger
                            ><SelectValue :placeholder="'Month'">
                                {{ selectedMonthLabel }}
                            </SelectValue></SelectTrigger
                        >
                        <SelectContent>
                            <SelectItem v-for="(month, index) in monthOptions" :key="index" :value="index.toString()">
                                {{ month }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select v-model="selectedYear" @update:model-value="onMonthYearChange">
                        <SelectTrigger
                            ><SelectValue :placeholder="'Year'">
                                {{ selectedYearLabel }}
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="year in yearOptions" :key="year" :value="year.toString()">
                                {{ year }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Calendar -->
                <Calendar v-model="selectedDate" />
            </PopoverContent>
        </Popover>
    </div>
</template>
