<script setup lang="ts">
import { InputNumberField } from '@/components/shared/inputs';
import ModalFileViewer from '@/components/shared/modals/ModalFileViewer.vue';
import type { Food } from '@/types';
import { ref } from 'vue';

const props = defineProps<{
    food: Food;
    quantity: number;
}>();

const isExpanded = ref(false);
const isViewerOpen = ref(false);

const emit = defineEmits<{
    (e: 'increment', food: Food): void;
    (e: 'decrement', food: Food): void;
    (e: 'update-quantity', payload: { food: Food; quantity: number }): void;
}>();
</script>

<template>
    <div class="bg-card flex h-full w-full flex-col rounded-xl border p-3 shadow-sm transition-all duration-300 hover:shadow-lg">
        <div class="h-32 w-full shrink-0 cursor-pointer overflow-hidden rounded" @click="isViewerOpen = true">
            <img
                v-if="food.image"
                :src="props.food.image"
                :alt="food.name"
                class="h-full w-full object-cover transition-transform duration-500 hover:scale-110"
            />
        </div>
        <div class="flex flex-1 flex-col gap-y-3 pt-2">
            <div class="flex flex-1 flex-col gap-y-1">
                <h2 class="text-sm font-semibold capitalize">{{ food.name }}</h2>
                <div class="flex-1">
                    <p :class="['text-xs font-normal text-gray-500 normal-case transition-all duration-300', isExpanded ? '' : 'line-clamp-2']">
                        {{ food.description }}
                    </p>
                    <button
                        v-if="food.description && food.description.length > 80"
                        @click="isExpanded = !isExpanded"
                        class="mt-1 cursor-pointer text-[10px] font-bold tracking-wider text-blue-600 uppercase hover:text-blue-800"
                    >
                        {{ isExpanded ? $t('action.show_less') : $t('action.show_more') }}
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between border-t border-gray-50 pt-2">
                <p class="fluid-text-xs-sm text-primary align-middle font-semibold">₱ {{ food.price }}</p>
                <InputNumberField
                    :model-value="props.quantity"
                    @update:modelValue="(val) => emit('update-quantity', { food: props.food, quantity: val })"
                />
            </div>
        </div>
    </div>
    <ModalFileViewer v-model:open="isViewerOpen" :file="{ name: food.name, file_type: 'image' }" :preview-url="food.image" :title="food.name" />
</template>
