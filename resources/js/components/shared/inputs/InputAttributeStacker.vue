<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger, AccordionTriggerWithIcon } from '@/components/ui/accordion';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Icon } from '@iconify/vue';
import { ref, watch } from 'vue';

// Props and emits
const props = defineProps<{
    modelValue: { name: string; description: string }[];
    stackLabel: string;
    stackAction: string;
    stackFallback: string;
    stackFormLabel: string;
    stackFormHint: string;
    iconStyling?: string;
}>();
const emit = defineEmits(['update:modelValue']);

// Local form input state
const attributeName = ref('');
const attributeDescription = ref('');

// Stack of applied attributes
const attributes = ref([...props.modelValue]);

// Watch for external changes to sync
watch(
    () => props.modelValue,
    (newVal) => {
        attributes.value = [...newVal];
    },
);

// Accordion state (multiple can be open at once)
const accordionValue = ref<string[]>([]);

// Emit updated attribute list
function updateAttributes() {
    emit('update:modelValue', [...attributes.value]);
}

// Apply new attribute to the list
function applyAttribute() {
    if (attributeName.value.trim() && attributeDescription.value.trim()) {
        attributes.value.push({
            name: attributeName.value,
            description: attributeDescription.value,
        });

        updateAttributes();

        // Open both sections when an attribute is added
        accordionValue.value = ['attributes-list', 'extra-info'];

        // Reset form
        attributeName.value = '';
        attributeDescription.value = '';
    }
}

// Cancel input
function cancelAttribute() {
    attributeName.value = '';
    attributeDescription.value = '';
    accordionValue.value = accordionValue.value.filter((v) => v !== 'extra-info');
}

// Remove attribute
function removeAttribute(index: number) {
    attributes.value.splice(index, 1);
    updateAttributes();
}
</script>

<template>
    <div>
        <Accordion type="multiple" class="w-full" collapsible v-model="accordionValue">
            <!-- Display stacked attributes -->
            <AccordionItem value="attributes-list" class="border-0">
                <div class="flex justify-between">
                    <p class="flex items-center align-middle text-sm font-medium">
                        {{ $t(stackLabel) }}
                    </p>
                    <AccordionTrigger class="text-primary-link-text cursor-pointer text-xs font-bold underline">
                        {{ $t(stackAction) }}
                    </AccordionTrigger>
                </div>
                <AccordionContent>
                    <div v-if="attributes.length" class="space-y-1">
                        <div v-for="(attr, index) in attributes" :key="index" class="flex flex-row items-center gap-x-2 py-1">
                            <div class="flex w-full gap-x-3">
                                <Input :model-value="attr.name" readonly class="bg-background w-1/3 rounded-md border" />
                                <Input :model-value="attr.description" readonly class="bg-background w-2/3 rounded-md border" />
                            </div>
                            <Button type="button" variant="secondary" class="h-6.5 w-6.5 rounded-full" @click="removeAttribute(index)">
                                <Icon icon="lucide:x" />
                            </Button>
                        </div>
                    </div>
                    <div v-else class="text-secondary-text fluid-text-xxs-xs font-medium italic">
                        {{ $t(stackFallback) }}
                    </div>
                </AccordionContent>
            </AccordionItem>

            <!-- Attribute form -->
            <AccordionItem value="extra-info">
                <AccordionTriggerWithIcon
                    class="text-primary-link-text flex cursor-pointer items-center justify-start gap-x-2.5 p-0 hover:underline"
                    :icon-class="iconStyling"
                >
                    <div class="flex items-center gap-x-2.5">
                        <div class="h-[20px] w-[20px] rounded-full border border-dashed border-blue-200 bg-blue-100">
                            <Icon icon="lucide:plus" class="size-2.5 h-full w-full text-blue-600" />
                        </div>
                        <p class="text-primary-link-text text-sm font-semibold hover:underline">{{ $t(stackFormLabel) }}</p>
                    </div>
                </AccordionTriggerWithIcon>
                <AccordionContent class="flex flex-col gap-y-4 py-3">
                    <p class="text-secondary-text text-xs font-semibold">{{ $t(stackFormHint) }}</p>
                    <div class="flex gap-x-2">
                        <Input v-model="attributeName" :placeholder="$t('form.placeholder.amenity_attribute_name')" />
                        <Input v-model="attributeDescription" :placeholder="$t('form.placeholder.amenity_attribute_description')" />
                    </div>
                    <div class="flex gap-x-6">
                        <Button variant="link" type="button" @click="applyAttribute" class="text-secondary-text hover p-0">
                            {{ $t('action.apply') }}
                        </Button>
                        <Button variant="link" type="button" @click="cancelAttribute" class="text-secondary-text hover p-0">
                            {{ $t('action.cancel') }}
                        </Button>
                    </div>
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </div>
</template>
