<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { Icon } from '@iconify/vue';
import { inject, onMounted } from 'vue';

const props = defineProps<{
    data: any;
}>();

const form = props.data;

const theme = inject('theme') as {
    palettes: Record<string, { bg: string; text: string; border: string }>;
    icons: string[];
};

const colors = Object.keys(theme?.palettes ?? {});

const icons = theme?.icons ?? [];

const pick = <T,>(arr: T[]) => arr[(Math.random() * arr.length) | 0];

onMounted(() => {
    if (colors.length && !form.color) form.color = pick(colors);
    if (icons.length && !form.icon) form.icon = pick(icons);
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child class="fluid-text-xs-sm w-fit">
            <Button variant="outline" class="fluid-text-xs-sm flex h-9 w-fit items-center gap-2 px-3">
                <span
                    :class="[
                        theme?.palettes[form.color]?.bg,
                        theme?.palettes[form.color]?.text,
                        theme?.palettes[form.color]?.border,
                        'flex size-5 items-center justify-center rounded-sm border',
                    ]"
                >
                    <Icon :icon="form.icon" class="size-4" />
                </span>
                <span>{{ $t('form.placeholder.color_icon') }}</span>
            </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent class="w-56 p-4" align="start">
            <DropdownMenuGroup class="flex flex-col">
                <span class="fluid-text-xs-sm mb-2"> {{ $t('form.label.color') }}</span>
                <ToggleGroup v-model="form.color" type="single" class="grid w-full grid-cols-6 gap-2">
                    <ToggleGroupItem
                        v-for="color in colors"
                        :key="color"
                        :value="color"
                        :class="[
                            theme?.palettes[color].bg,
                            theme?.palettes[color].text,
                            theme?.palettes[color].border,
                            'flex size-5 items-center justify-center rounded-sm border data-[state=on]:bg-gray-50',
                        ]"
                    >
                        <Icon v-if="form.color === color" icon="tabler:check" :class="[theme?.palettes[color].text, 'size-4']" />
                    </ToggleGroupItem>
                </ToggleGroup>
            </DropdownMenuGroup>
            <DropdownMenuSeparator class="my-4" />
            <ScrollArea class="h-56">
                <DropdownMenuGroup class="flex flex-col">
                    <span class="fluid-text-xs-sm mb-2"> {{ $t('form.label.icon') }}</span>
                    <ToggleGroup v-model="form.icon" type="single" class="grid grid-cols-4 gap-2">
                        <ToggleGroupItem v-for="icon in theme.icons" :key="icon" :value="icon">
                            <Icon :icon="icon" class="size-4 sm:size-5" />
                        </ToggleGroupItem>
                    </ToggleGroup>
                </DropdownMenuGroup>
            </ScrollArea>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
