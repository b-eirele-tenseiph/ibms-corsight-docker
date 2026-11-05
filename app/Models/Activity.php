<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Models\Activity as ActivityBase;

class Activity extends ActivityBase
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // TYPE_INFO     (1.0) Constant string for information type of log.
    // TYPE_WARNING  (2.0) Constant string for warning type of log.
    // TYPE_ERROR    (3.0) Constant string for error type of log.
    // TYPE_AUDIT    (4.0) Constant string for audit type of log.
    // TYPE_FOOD     (5.0)  Constant string for food type of log.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> TYPE_INFO,
     * <Function> Constant string for information type of log.
     *
     * @var string
     */
    public const TYPE_INFO = 'information';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> TYPE_WARNING,
     * <Function> Constant string for warning type of log.
     *
     * @var string
     */
    public const TYPE_WARNING = 'warning';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> TYPE_ERROR,
     * <Function> Constant string for error type of log.
     *
     * @var string
     */
    public const TYPE_ERROR = 'error';

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> TYPE_AUDIT,
     * <Function> Constant string for audit type of log.
     *
     * @var string
     */
    public const TYPE_AUDIT = 'audit';

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> TYPE_FOOD,
     * <Function> Constant string for food type of log.
     *
     * @var string
     */
    public const TYPE_FOOD = 'food';
}
