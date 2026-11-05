<script setup lang="ts"></script>
<template>
    <div v-for="n in 4" :key="n" class="bg-skeleton/40 my-4 flex h-12 w-full animate-pulse flex-row items-center justify-between rounded-md px-3">
        <div class="bg-skeleton h-6 w-40 animate-pulse rounded-sm"></div>
        <div class="flex flex-row items-center justify-end gap-2">
            <div class="bg-skeleton size-6 animate-pulse rounded-full"></div>
            <div class="bg-skeleton h-3 w-5 animate-pulse rounded-sm"></div>
        </div>
    </div>
</template>
