<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class WaterRate extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // consumer_type              (1.0) The types of consumers for water rates.
    // $appends                   (2.0) The accessors to append to the model's
    //                                  array form.
    // $fillable                  (3.0) The attributes that are mass assignable.
    // $casts                     (4.0) The attributes that should be cast.
    // scopeLatestRateWithHistory (5.0) Get the latest water rate for the
    //                                  current month, including historical
    //                                  rates for the same consumer type and
    //                                  meter size.
    // rates                      (6.0) Get the historical rates for the current
    //                                  water rate's consumer type and meter
    //                                  size.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> consumer_type
     * <Function> The types of consumers for water rates.
     *
     * @var array<string>
     */
    public const consumer_type = [
        'residential',
        'commercial',
        'industrial',
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $appends
     * <Function> The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = ['rates'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['consumer_type', 'meter_size', 'effective_date', 'notes', 'tiers'];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'tiers' => 'array',
        'effective_date' => 'date',
    ];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $casts
     * <Function> Get the latest water rate for the current month, including historical rates for the same consumer type and meter size.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestRateWithHistory(Builder $query): Builder
    {
        return $query->whereIn('id', function ($q) {
            $q->selectRaw('id')
                ->from('water_rates')
                ->whereRaw('effective_date =
                    (SELECT MAX(effective_date)
                     FROM water_rates as wr
                     WHERE wr.consumer_type = water_rates.consumer_type
                     AND wr.meter_size = water_rates.meter_size)');
        });
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> rates
     * <Function> Get the historical rates for the current water rate's consumer type and meter size.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function rates(): Attribute
    {
        return Attribute::make(
            get: fn() => self::select('id', 'effective_date', 'tiers')
                ->where('consumer_type', $this->consumer_type)
                ->where('meter_size', $this->meter_size)
                ->orderBy('effective_date', 'desc')
                ->get()
                ->map(fn($rate) => [
                    'id' => $rate->id,
                    'effective_date' => $rate->effective_date,
                    'tiers' => $rate->tiers,
                ])
                ->toArray()
        );
    }
}
