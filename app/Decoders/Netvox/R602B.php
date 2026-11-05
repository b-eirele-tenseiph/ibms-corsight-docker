<?php

namespace App\Decoders\Netvox;

use App\Contracts\Netvox\DecoderContract;

class R602B extends DecoderContract
{
    /**
     * Return an empty array to keep all attributes the base contract
     * extracts for the detected prefix.
     */
    protected function dataTypes(): array
    {
        return [];
        // Or restrict: return ['battery','status','fire','hightemp'];
    }
}
