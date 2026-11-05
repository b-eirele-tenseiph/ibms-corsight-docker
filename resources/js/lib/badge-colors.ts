const gray = 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
const blue = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
const green = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
const orange = 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
const purple = 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
const red = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
const pink = 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300';
const yellow = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
const teal = 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300';
const indigo = 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300';
const violet = 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300';

const fallbackColors = [blue, purple, indigo, violet, yellow, red, pink, orange, teal, green];

const pickFallback = (key: string | undefined): string =>
    fallbackColors[[...(key ?? '')].reduce((s, c) => s + c.charCodeAt(0), 0) % fallbackColors.length];

export const AMENITY_CATEGORY_COLORS: Record<string, string> = {
    'Accommodation and Rooms': blue,
    'Dining and Beverages': orange,
    'Wellness and Recreation': green,
    'Transportation Services': purple,
    'Business and Events': red,
    'Entertainment and Leisure': pink,
    'Retail and Convenience': yellow,
    'Outdoor and Scenic Areas': teal,
    'Public Facilities': indigo,
    'Special Experiences': violet,
};

export const LOGS_COLORS: Record<string, string> = {
    user: green,
    admin: blue,
    lorawanDevice: orange,
    switchBotDevice: orange,
    switchBotRemote: orange,
    system: gray,
};

export const GATEWAY_CONNECTION_STATUS_COLORS: Record<string, string> = {
    kerlink: orange,
    milesight: blue,
    thingpark: green,
};

export const ORDER_STATUS_COLORS: Record<string, string> = {
    new: blue,
    'on cook': orange,
    completed: green,
};

export const ROLE_COLORS: Record<string, string> = {
    crew: teal,
    engineering: pink,
    admin: blue,
    supervisor: orange,
    staff: green,
};

export const getCategoryBadgeClass = (categoryName: string | undefined): string =>
    AMENITY_CATEGORY_COLORS[categoryName ?? ''] ?? pickFallback(categoryName);

export const getLogBadgeClass = (origin: string | undefined): string => LOGS_COLORS[origin ?? ''] ?? pickFallback(origin);

export const getGatewayConnectionStatusBadgeClass = (status: string | undefined): string =>
    GATEWAY_CONNECTION_STATUS_COLORS[status ?? ''] ?? pickFallback(status);

export const getOrderStatusBadgeClass = (status: string | undefined): string => ORDER_STATUS_COLORS[status ?? ''] ?? pickFallback(status);

export const getRoleBadgeClass = (role: string | undefined): string => ROLE_COLORS[role ?? ''] ?? pickFallback(role);

export const getDefaultBadgeClass = (permission: string | undefined): string => pickFallback(permission);
