<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
</script>

<template>
    <Card class="bg-card flex h-full w-full flex-col rounded-xl border p-7 shadow-md 2xl:max-w-105">
        <div class="flex flex-col gap-y-5">
            <div class="flex justify-between">
                <div class="fluid-text-base-xl font-bold">
                    <slot name="header-title" />
                </div>
                <slot name="header-button" />
            </div>
            <hr />
        </div>

        <div>
            <slot name="content-button" />
            <slot name="content-detail-additionals" />
        </div>

        <div class="flex flex-1 flex-col justify-start gap-y-5 overflow-y-auto">
            <ScrollArea class="flex-1">
                <slot name="content-details" />
                <ScrollBar orientation="vertical" />
            </ScrollArea>
        </div>
        <div class="flex flex-col gap-y-5">
            <hr />
            <div class="flex w-full flex-col gap-y-3.5">
                <slot name="footer" />
            </div>
        </div>
    </Card>
</template>
