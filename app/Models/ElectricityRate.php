<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ElectricityRate extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable       (1.0) The attributes that are mass assignable.
    // $casts          (2.0) The attributes that should be cast.
    // latestRate      (3.0) Get the latest electricity rate for the current
    //                     month.
    // effectiveRate   (4.0) Get the effective rate for a given month.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['effective_date', 'rate_per_kwh'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'rate_per_kwh' => 'decimal:4',
        'effective_date' => 'date',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> latestRate
     * <Function> Get the latest electricity rate for the current month.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    #[Scope]
    public function latestRate(Builder $query): Builder
    {
        $currentMonth = $query->clone()
            ->whereYear('effective_date', now()->year)
            ->whereMonth('effective_date', now()->month)
            ->latest()
            ->value('id');

        return $currentMonth ? $query->where('id', $currentMonth) : $query->latest('effective_date');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> effectiveRate
     * <Function> Get the effective rate for a given month.
     * Uses the latest rate on or before the target month (carry-forward).
     * Returns null if no rate exists.
     *
     * @param \Carbon\CarbonInterface $date
     * @return float|null
     */
    public static function effectiveRate($date): ?float
    {
        static $cache = null;
        $rates = $cache ??= static::orderBy('effective_date')
            ->get(['effective_date', 'rate_per_kwh']);

        return $rates
            ->filter(fn($r) => $r->effective_date->format('Y-m') <= $date->copy()->startOfMonth()->format('Y-m'))
            ->sortByDesc('effective_date')
            ->first()
            ?->rate_per_kwh;
    }
}
