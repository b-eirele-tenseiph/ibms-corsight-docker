<?php

namespace App\Models;

use App\Observers\ElectricMeter\MonthlyConsumptionDataObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

#[ObservedBy([MonthlyConsumptionDataObserver::class])]
class MonthlyConsumption extends Model
{
    use Prunable, HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable   (1.0) The attributes that are mass assignable.
    // prunable    (2.0) The query for determining which records to prune.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> prunable
     * <Function> The query for determining which records should be pruned.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subYear());
    }
}
