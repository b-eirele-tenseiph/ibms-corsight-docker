<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasUuids, SoftDeletes;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded     (1.0) The attributes that are not mass assignable.
    // $appends     (2.0) Appended accessor attributes.
    // boot         (3.0) Handles file archiving on delete and restoration.
    // daysLeft     (4.0) Calculates days remaining before permanent deletion.
    // admin        (5.0) Belongs-to relationship with Admin model.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.`
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $appends
     * <Function> Appended accessor attributes.
     *
     * @var array
     */
    protected $appends = ['days_left'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> boot
     * <Function> Handles file archiving on delete and restoration.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($file) {
            $archivedPath = 'archived/' . $file->name;

            if ($file->isForceDeleting()) {
                Storage::disk('gcs')->delete($archivedPath);
            } else {
                $reportPath = 'reports/' . $file->name;
                Storage::disk('gcs')->move($reportPath, $archivedPath);
            }
        });

        static::restoring(function ($file) {
            $archivedPath = 'archived/' . $file->name;
            $reportPath = 'reports/' . $file->name;
            Storage::disk('gcs')->move($archivedPath, $reportPath);
        });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> daysLeft
     * <Function> Calculates days remaining before permanent deletion.
     *
     * @return Attribute
     */
    protected function daysLeft(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->deleted_at
                ? max(0, 30 - (int) $this->deleted_at->diffInDays(now()))
                : null
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> admin
     * <Function> Belongs-to relationship with Admin model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }
}
