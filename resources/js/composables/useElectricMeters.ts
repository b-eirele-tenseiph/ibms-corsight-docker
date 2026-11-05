import { electricMeters } from '@/routes/admin/dashboard';
import { ElectricMeterListData, PaginatedResponse } from '@/types';
import axios from 'axios';
import { nextTick, ref } from 'vue';

export function useElectricMeters() {
    const meters = ref<ElectricMeterListData[]>([]);
    const paginatedMeters = ref({} as PaginatedResponse<ElectricMeterListData>);
    const isLoading = ref(false);

    const fetchMeters = async (paginate = true, filters = {}) => {
        nextTick(async () => {
            isLoading.value = true;
            axios
                .get(electricMeters.url(), { params: paginate ? { per_page: 6, ...filters } : { all: true } })
                .then(({ data }) => {
                    meters.value = paginate ? data.data : data;
                    paginatedMeters.value = paginate ? data : ({} as PaginatedResponse<ElectricMeterListData>);
                })
                .finally(() => (isLoading.value = false));
        });
    };

    return { meters, paginatedMeters, isLoading, fetchMeters };
}
