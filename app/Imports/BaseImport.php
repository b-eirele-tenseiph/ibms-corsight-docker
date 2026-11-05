<?php

namespace App\Imports;

use App\Models\Activity;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Validators\Failure;

abstract class BaseImport implements
    ToModel,
    WithHeadingRow,
    SkipsEmptyRows,
    WithValidation,
    SkipsOnFailure
{
    use SkipsFailures;

    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // $headers                (1.0)  The required headers for the import.
    // $verifyHeader           (2.0)  Verify that the file contains the required
    //                                headers
    // $truncate               (3.0)  Truncate the table before importing.
    // $rowsAffected           (4.0)  How many rows were affected by the import.
    // $time_start             (5.0)  Execution time in seconds.
    // $grouped                (6.0)  An array of grouped error messages and
    //                                their associated rows.
    // $failedRows             (7.0)  An array of rows that failed to import.
    // $service                (8.0)  The shared lookup service instance.
    // __construct             (9.0)  Construct a new instance of the BaseImport
    //                                class.
    // startExecutionTime      (10.0) Start timing the import process.
    // onFailure               (11.0) Handle a job failure.
    // summary                 (12.0) Get a summary of the import process.
    // logImportActivity       (13.0) Log the import activity in the system.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $headers
     * <Function> The required headers for the import.
     *
     * @var array<string>
     */
    public array $headers = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $verifyHeader
     * <Function> Verify that the file contains the required headers.
     *
     * @var bool
     */
    public bool $verifyHeader = true;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $truncate
     * <Function> Truncate the table before importing.
     *
     * @var bool
     */
    public bool $truncate = false;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $rowsAffected
     * <Function> How many rows were affected by the import.
     *
     * @var int
     */
    public int $rowsAffected = 0;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $time_start
     * <Function> Execution time in seconds.
     *
     * @var float|null
     */
    public ?float $time_start = null;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $grouped
     * <Function> An array of grouped error messages and their associated rows.
     *
     * @var array<string, array{msg:string, rows:int[]}>
     */
    protected array $grouped = [];

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> $failedRows
     * <Function> An array of rows that failed to import.
     *
     * @var array<int, true>
     */
    protected array $failedRows = [];

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> $service
     * <Function> The shared lookup service instance.
     *
     * @var RowModelMapper
     */
    protected RowModelMapper $service;

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> __construct
     * <Function> Construct a new instance of the BaseImport class.
     *
     * @param RowModelMapper|null $service
     */
    public function __construct(?RowModelMapper $service = null)
    {
        $this->service = $service ?? new RowModelMapper();

        $fileHeaders = (new HeadingRowImport)->toArray(request()->file('file'))[0][0] ?? [];

        $missingHeaders = array_diff($this->headers, $fileHeaders);

        if ($missingHeaders) {
            throw ValidationException::withMessages([
                'file' => 'The file is missing required columns: ' . implode(', ', $missingHeaders)
            ]);
        }
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> startExecutionTime
     * <Function> Start timing the import process.
     *
     * @return void
     */
    public function startExecutionTime(): void
    {
        $this->time_start = microtime(true);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> onFailure
     * <Function> Handle a job failure.
     *
     * @param Failure ...$failures
     * @return void
     */
    public function onFailure(Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            $row = $failure->row();
            $this->failedRows[$row] = true;

            foreach ($failure->errors() as $raw) {
                $attr = ucfirst(str_replace('_', ' ', $failure->attribute()));

                // If the failure string looks like a translation key (contains a dot),
                // attempt to translate it. Otherwise treat it as a literal message.
                if (str_contains($raw, '.')) {
                    $translated = __($raw);
                    if ($translated !== $raw) {
                        // Use translated string and replace attribute placeholder
                        $msg = str_replace(':attribute', $attr, $translated);
                    } else {
                        // No translation found: humanize the key (replace dots/underscores)
                        $msg = str_replace([':attribute', '_', '.'], [$attr, ' ', ' '], $raw);
                    }
                } else {
                    // Literal message: just replace :attribute placeholder
                    $msg = str_replace(':attribute', $attr, $raw);
                }

                // Normalize whitespace
                $msg = preg_replace('/\s+/', ' ', trim($msg));

                $g    = &$this->grouped[$msg];
                $g['msg']  = $msg;
                $g['rows'] = isset($g['rows']) ? array_values(array_unique([...$g['rows'], $row])) : [$row];
            }
        }
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> summary
     * <Function> Get a summary of the import process.
     *
     * @return array
     */
    public function summary(): array
    {
        $failed = count($this->failedRows);
        $errors = array_map(
            fn($g) => $g['msg'] . ' (' . __('defaults.rows') . ' ' . implode(', ', $g['rows']) . ')',
            array_values($this->grouped)
        );

        return [
            'processed'  => $this->rowsAffected + $failed,
            'successful' => $this->rowsAffected,
            'failed'     => $failed,
            'errors'     => $errors,
        ];
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> logImportActivity
     * <Function> Log the import activity in the system.
     *
     * @return \App\Models\Activity|null
     */
    public function logImportActivity(string $importName, mixed $user, array $extraProperties = []): Activity|null
    {
        $failed = count($this->failedRows);
        $logType = $failed ? Activity::TYPE_ERROR : Activity::TYPE_INFO;
        $code = $failed ? 'E200' : 'I200';

        return activity()
            ->causedBy($user)
            ->inLog($logType)
            ->withProperties(array_merge([
                'Code'           => $code,
                'Import Name'    => $importName,
                'Imported Row' => $this->rowsAffected,
                'Failed Row'     => $failed,
            ], $extraProperties))
            ->log(get_message($logType, $code, [
                'fileName' => $importName,
                'rows'       => $this->rowsAffected,
                'failed'     => $failed,
            ]));
    }
}
