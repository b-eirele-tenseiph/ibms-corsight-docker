import { ImportSummary } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

export const useImportSummary = () => {
    const page = usePage<{ import?: { summary?: ImportSummary | null } }>();

    const summary = ref<ImportSummary | null>(page.props.import?.summary ?? null);

    watch(
        () => page.props.import?.summary,
        (v) => (summary.value = v ?? null),
        { immediate: true },
    );

    return { summary };
};
