<?php

namespace App\Traits\Scopes;

use App\Models\SwitchBotType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasSwitchBotType
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // type             (1.0) The type of the SwitchBot resource.
    // scopeOfType      (2.0) Scope a query to only include the SwitchBot
    //                        resources with the given type(s).

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> type
     * <Function> The type of the SwitchBot resource.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(SwitchBotType::class, 'type_id', 'id');
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> scopeOfType
     * <Function> Scope a query to only include the SwitchBot resources with the
     *            given type(s).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string[]  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType(Builder $query, string ...$type): Builder
    {
        return $query->whereHas('type', fn($q) => $q->whereIn('switch_bot_types.name', $type));
    }
}
