<?php

namespace App\Traits\Scopes;

trait HasSettings
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getSettingsAttribute  (1.0) Fetch configurable settings for device

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getSettingsAttribute
     * <Function> Fetch configurable settings for device
     *
     * @return array|null
     */
    public function getSettingsAttribute()
    {
        $context = optional($this->context)->toArray() ?? [];

        switch ($this->model) {
            case 'WS501':  // Milesight switch settings
                return array_merge(config('settings.switch1'), $context);
            case 'WS502':  // Milesight switch settings
                return array_merge(config('settings.switch2'), $context);
            case 'WS503':  // Milesight switch settings
                return array_merge(config('settings.switch3'), $context);
            case 'ADW300': // Acrel Electric meter settings
                return array_merge(config('settings.electric_meter'), $context);
            case 'UC300':  // Milesight iot controller settings
                return array_merge(config('settings.iot_controller'), $context);
            case 'VS121':  // Milesight AI Workplace settings
                return array_merge(config('settings.ai_sensor'), $context);
            default:
                return null;
        }
    }
}
