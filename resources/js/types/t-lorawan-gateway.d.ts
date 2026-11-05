import { Floor, PaginatedResponse, QueryParams } from '.';

export type LorawanConnection = Array<'milesight' | 'thingpark' | 'kerlink'>;

export interface LorawanGateway {
    id: string;
    ip_address: string;
    mac_address: string;
    connection: string;
    floor: Floor;
    floor_id: number | null;
    devices: Array<{ id: string }>;
    devices_count: number;
}

export interface LorawanGatewayProps {
    gateways: PaginatedResponse<LorawanGateway>;
    floors: Array<Floor>;
    connection: LorawanConnection;
    queryParams: QueryParams;
}

export type LorawanGatewayFormData = Omit<LorawanGateway, 'id' | 'floor' | 'devices' | 'devices_count'> & Record<string, any>;
