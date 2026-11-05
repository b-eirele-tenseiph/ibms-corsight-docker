import { Food } from './t-food';

export interface RestaurantFoodOrderHistory {
    id: number | string;
    user?: {
        id: number;
        name: string;
    };
    room?: {
        id: number;
        name: string;
    };
    details: { food: Food; quantity: number }[];
    createdAt: Date;
    status: string;
    remarks?: string;
    created_at: Date;
}
