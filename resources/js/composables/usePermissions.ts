import { index as adminDashboardIndex } from '@/routes/admin/dashboard';
import { index as crewDashboardIndex } from '@/routes/crew/dashboard';
import { index as staffDashboardIndex } from '@/routes/staff/dashboard';
import { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export const usePermissions = () => {
    const page = usePage<SharedData>();
    const impersonation = page.props.impersonation;
    const user = page.props.auth.user;

    const can = (value: string): boolean => {
        if (!value) return false;

        const permissions = user?.permissions;
        if (!permissions?.length) return false;

        if (value.includes('|')) {
            return value.split('|').some((p) => permissions.includes(p));
        }

        return permissions.includes(value);
    };

    const hasExternalApp = (value: string): boolean => {
        if (!value) return false;

        const externalApps = user?.external_apps;
        if (!externalApps?.length) return false;

        return externalApps.includes(value);
    };

    const homeLink = computed(() => {
        const role = impersonation.active ? impersonation.role : page.props.auth?.user?.role;
        switch (role) {
            case 'staff':
                return staffDashboardIndex.url();
            case 'crew':
                return crewDashboardIndex.url();
            default:
                return adminDashboardIndex.url();
        }
    });

    return { can, hasExternalApp, homeLink };
};
