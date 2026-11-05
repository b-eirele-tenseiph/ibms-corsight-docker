<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute as Attribute;

trait HasLorawanDeviceType
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // ofDeviceType       (1.0) Filter query by device type.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> ofDeviceType
     * <Function> Filter query by device type.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeviceType(Builder $query, string $type): Builder
    {
        $map = [
            'switches' => function ($q) {
                return $q->whereIn('model', ['WS501', 'WS502', 'WS503']);
            },
            'thermostat' => function ($q) {
                return $q->whereIn('model', ['UC300'])
                    ->where('context->slave_model', 'RCF230CD');
            },
            'siren' => function ($q) {
                return $q->whereIn('model', ['R602B']);
            },
            'peopleCounter' => function ($q) {
                return $q->whereIn('model', ['VS121']);
            },
            'sensors' => function ($q) {
                return $q->whereIn('model', ['WS202', 'R313A']);
            },
            'occuTempLight' => function ($q) {
                return $q->whereIn('model', ['RB11E']);
            },
            'co2TempHumid' => function ($q) {
                return $q->whereIn('model', ['RA0715']);
            },
            'electricMeter' => function ($q) {
                return $q
                    ->whereIn('model', ['ADW300'])
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereIn('model', ['UC300'])
                            ->whereIn('context->slave_model', ['EPM5500P', 'ADW300']);;
                    });
            },
        ];

        return isset($map[$type]) ? $map[$type]($query) : $query;
    }
}
