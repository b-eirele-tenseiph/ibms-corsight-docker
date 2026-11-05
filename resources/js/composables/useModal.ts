import { ref } from 'vue';

export function useModal<T = null>(initialData: T | null = null) {
    const visible = ref(false);
    const data = ref<T | null>(initialData);

    function open(payload: T | null = null) {
        data.value = payload;
        visible.value = true;
    }

    function close() {
        visible.value = false;
        data.value = null;
    }

    return {
        visible,
        data,
        open,
        close,
    };
}
