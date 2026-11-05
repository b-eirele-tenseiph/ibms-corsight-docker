<?php

namespace App\Models;

use App\Observers\PromoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([PromoObserver::class])]
class Promo extends Model
{
    use HasFactory, HasUuids;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded                   (1.0) Attributes that are not mass assignable.
    // $with                      (2.0) Relationships to always eager load.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> Attributes that are not mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $with
     * <Function> Relationships to always eager load.
     *
     * @var list<string>
     */
    protected $with = [];
}
