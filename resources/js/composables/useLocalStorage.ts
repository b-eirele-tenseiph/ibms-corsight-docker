import { ref, watch } from 'vue';

export function useLocalStorageRef<T>(key: string, defaultValue: T) {
    const storedValue = localStorage.getItem(key);
    const data = ref<T>(storedValue ? JSON.parse(storedValue) : defaultValue);

    watch(
        data,
        (newVal) => {
            localStorage.setItem(key, JSON.stringify(newVal));
        },
        { deep: true },
    );

    return data;
}
