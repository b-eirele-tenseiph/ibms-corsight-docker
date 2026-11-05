import { devicesSummaryData } from '@/routes/admin/dashboard';
import axios from 'axios';
import { computed, nextTick, ref } from 'vue';

let store: any;

export function useDeviceSummary() {
    if (store) return store;

    const deviceSummary = ref<any[]>([]);
    const isLoading = ref<boolean>(false);

    const activeDevices = computed(() => deviceSummary.value.reduce((sum, d) => sum + d.active, 0));

    const loadDeviceSummary = async () => {
        nextTick(async () => {
            isLoading.value = true;

            try {
                const { data } = await axios.get(devicesSummaryData.url());
                deviceSummary.value = data.summary;
            } finally {
                isLoading.value = false;
            }
        });
    };

    store = { deviceSummary, isLoading, activeDevices, loadDeviceSummary };

    return store;
}
