<script setup lang="ts">
import { InputSelectWithLabel, InputTimePicker, InputWithLabel } from '@/components/shared/inputs';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { ResizableHandle, ResizablePanel, ResizablePanelGroup } from '@/components/ui/resizable';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Switch } from '@/components/ui/switch';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { useModal } from '@/composables/useModal';
import { formatDeviceCommand } from '@/lib/command-formatter';
import { Room } from '@/types/t-room';
import { Device, Scene } from '@/types/t-scene';
import { Icon } from '@iconify/vue';
import { computed, ref } from 'vue';
import { SceneDeviceSelection, SceneHeader, SceneHeaderTitle, ScenePanelContent } from '.';
import SceneColorIconSelection from './SceneColorIconSelection.vue';

const props = defineProps<{
    admin?: boolean;
    mode: 'create' | 'edit';
    form: any;
    rooms: Room[] | Room;
    items: Device[];
    scene?: Scene;
    isLoading: boolean;
}>();

const emit = defineEmits<{ (e: 'submit'): void }>();

const form = props.form;

const showDeviceInput = ref(false);

const showAllDevices = ref(false);

const selectedDeviceId = ref<string | null>(null);

const modals = {
    deviceList: useModal<any>(),
};

const {
    deviceList: { visible: showDeviceListModal, data: deviceListData, close: closeDeviceListModal },
} = modals;

const itemNameById = computed(() => new Map(props.items.map((i) => [i.id, i.name])));

const visibleDevices = computed(() => {
    const cmds = form.commands;
    const deviceMap = new Map<string, { name: string; parts: string[] }>();
    for (const c of cmds) {
        const id = c?.id;
        if (!id) continue;
        const name = itemNameById.value.get(id) ?? '';
        const part = formatDeviceCommand(c.command);
        const entry = deviceMap.get(id) ?? { name, parts: [] };
        if (part) entry.parts.push(part);
        deviceMap.set(id, entry);
    }
    const limit = showAllDevices.value ? undefined : 10;
    return Array.from(deviceMap, ([id, { name, parts }]) => ({
        id,
        name,
        commandDisplay: parts.join(' , '),
    })).slice(0, limit);
});

const removeDevice = (id: string | number | undefined) => {
    if (id == null) return;
    const key = String(id);
    form.devices = (form.devices ?? []).filter((d: any) => String(d?.id ?? d?.device_id ?? '') !== key);
    form.commands = (form.commands ?? []).filter((c: any) => String(c?.id ?? c?.device_id ?? '') !== key);
    if (selectedDeviceId.value === key) selectedDeviceId.value = null;
};

const handleAddDevice = () => {
    if (!selectedDeviceId.value) return;
    const deviceToAdd = props.items.find((item) => item.id === selectedDeviceId.value);
    if (!deviceToAdd) return;
    if (!Array.isArray(form.devices)) form.devices = [];
    if (!Array.isArray(form.commands)) form.commands = [];
    selectedDeviceId.value = null;
    showDeviceInput.value = false;
};

const handleCancelAddDevice = () => {
    selectedDeviceId.value = null;
    showDeviceInput.value = false;
};
</script>

<template>
    <SceneHeader :label="mode === 'edit' ? 'update' : 'create'" @primaryAction="emit('submit')" :scene :admin />

    <div class="flex min-h-0 flex-col gap-2">
        <SceneHeaderTitle :title="mode === 'edit' ? 'update_scene' : 'add_scene'" />
        <ScenePanelContent>
            <template #information>
                <div class="flex flex-col items-start gap-4">
                    <div class="flex w-full flex-col gap-4">
                        <Label class="text-secondary-text fluid-text-xs-sm font-bold">{{ $t('modal.title.scene_detail') }}</Label>
                        <div class="space-y-2">
                            <Label class="fluid-text-xs-sm"> {{ $t('form.label.icon_theme') }}</Label>
                            <SceneColorIconSelection :data="form" />
                        </div>

                        <InputWithLabel
                            label="form.label.scene_name"
                            placeholder="form.placeholder.scene_name"
                            v-model="form.name"
                            :error="form.errors.name"
                        />
                        <InputSelectWithLabel
                            v-if="props.admin"
                            label="form.label.room_name"
                            placeholder="form.placeholder.room_name"
                            v-model="form.room_id"
                            :error="form.errors.room_id"
                            :items="rooms as Room[]"
                        />
                    </div>

                    <div class="flex w-full flex-col gap-4">
                        <Label class="text-secondary-text fluid-text-xs-sm font-bold">{{ $t('modal.title.scene_schedule') }}</Label>

                        <div class="flex items-center gap-2">
                            <Switch v-model="form.scheduled" root-size="lg" thumb-size="lg" />
                            <Label class="fluid-text-xs-sm">
                                {{ $t(form.scheduled ? 'form.label.scheduled' : 'form.label.manual') }}
                            </Label>
                        </div>

                        <div v-if="form.scheduled" class="flex flex-col gap-6">
                            <div class="w-fit">
                                <InputTimePicker label="form.label.time" v-model="form.time" />
                            </div>
                            <p v-show="form.errors.time" class="text-destructive-text -mt-4 text-sm">{{ form.errors.time }}</p>

                            <div class="space-y-4">
                                <Label class="fluid-text-xs-sm">{{ $t('form.label.schedule') }}</Label>
                                <RadioGroup v-model="form.scheduleType" orientation="vertical" class="gap-4">
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem value="everyday" />
                                        <Label class="fluid-text-xs-sm">{{ $t('form.label.everyday') }}</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem value="custom" />
                                        <Label class="fluid-text-xs-sm">{{ $t('form.label.custom') }}</Label>
                                    </div>
                                </RadioGroup>
                            </div>

                            <div v-if="form.scheduleType === 'custom'" class="space-y-4">
                                <Label class="fluid-text-xs-sm">{{ $t('form.label.repeat') }}</Label>
                                <ScrollArea class="flex overflow-x-auto">
                                    <ToggleGroup v-model="form.days" type="multiple" class="mb-4 flex gap-2">
                                        <ToggleGroupItem
                                            v-for="(fullDayName, shortDayCode) in {
                                                sun: 'Sunday',
                                                mon: 'Monday',
                                                tue: 'Tuesday',
                                                wed: 'Wednesday',
                                                thu: 'Thursday',
                                                fri: 'Friday',
                                                sat: 'Saturday',
                                            }"
                                            :key="shortDayCode"
                                            :value="fullDayName"
                                            class="bg-controls-active data-[state=on]:bg-primary text-xxs flex size-10 cursor-pointer items-center justify-center rounded-full border uppercase transition-colors data-[state=on]:text-white sm:text-xs"
                                        >
                                            {{ $t(`page.scene_management.days.${shortDayCode}`) }}
                                        </ToggleGroupItem>
                                    </ToggleGroup>
                                    <ScrollBar orientation="horizontal" />
                                </ScrollArea>
                                <p v-show="form.errors.days" class="text-destructive -mt-2 text-sm">{{ form.errors.days }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <template #devices>
                <div class="flex flex-col items-start gap-4">
                    <div class="flex w-full flex-col items-start gap-4">
                        <p v-show="form.errors.commands" class="text-destructive text-sm">{{ form.errors.commands }}</p>

                        <Button
                            variant="link"
                            @click="modals.deviceList.open(props.items)"
                            class="fluid-text-xs-sm pl-0 hover:no-underline has-[>svg]:p-0"
                        >
                            <div class="rounded-full border border-dashed border-blue-200 bg-blue-200">
                                <Icon icon="lucide:plus" class="size-4" />
                            </div>
                            {{ $t('form.label.add_devices') }}
                        </Button>

                        <div v-if="showDeviceInput" class="w-full">
                            <InputSelectWithLabel
                                :items="props.items"
                                label="form.label.device_name"
                                placeholder="form.placeholder.device_name"
                                v-model="selectedDeviceId"
                            />
                            <div class="mt-3 space-x-px">
                                <Button @click="handleAddDevice" variant="ghost" size="sm" class="text-xs font-semibold uppercase">{{
                                    $t('action.apply')
                                }}</Button>
                                <Button @click="handleCancelAddDevice" variant="ghost" size="sm" class="text-xs font-semibold uppercase">{{
                                    $t('action.cancel')
                                }}</Button>
                            </div>
                        </div>
                    </div>

                    <div v-if="form.devices && form.devices.length" class="flex w-full flex-col gap-4">
                        <div class="fluid-text-xs-sm flex w-full flex-row items-start justify-between gap-2">
                            <Label class="text-secondary-text font-bold">{{ $t('modal.action.scene_device_selection') }}</Label>
                            <Label
                                @click="showAllDevices = !showAllDevices"
                                class="text-primary-link-text cursor-pointer hover:underline"
                                v-show="visibleDevices.length > 9"
                            >
                                {{ showAllDevices ? $t('action.show_less') : $t('action.show_more') }}
                            </Label>
                        </div>

                        <ScrollArea>
                            <div class="max-h-120 space-y-2 pr-3">
                                <div v-for="device in visibleDevices" :key="device.id" class="flex w-full items-center gap-2">
                                    <ResizablePanelGroup direction="horizontal" class="flex-1 gap-4">
                                        <ResizablePanel :default-size="45" class="bg-controls-active rounded-lg border p-2">
                                            <div class="fluid-text-xs-sm">
                                                {{ device.name }}
                                            </div>
                                        </ResizablePanel>
                                        <ResizableHandle with-handle />
                                        <ResizablePanel :default-size="55" class="bg-controls-active rounded-lg border p-2">
                                            <div class="fluid-text-xs-sm capitalize">
                                                {{ device.commandDisplay }}
                                            </div>
                                        </ResizablePanel>
                                    </ResizablePanelGroup>

                                    <div
                                        class="bg-controls-active flex size-6 cursor-pointer items-center justify-center rounded-full"
                                        :aria-label="`Remove ${device.name}`"
                                        @click="removeDevice(device.id)"
                                    >
                                        <Icon icon="lucide:x" />
                                    </div>
                                </div>
                            </div>
                        </ScrollArea>
                    </div>
                </div>
            </template>
        </ScenePanelContent>
    </div>

    <SceneDeviceSelection :isLoading :items="deviceListData || []" :open="showDeviceListModal" @close="closeDeviceListModal" :form="props.form" />
</template>
