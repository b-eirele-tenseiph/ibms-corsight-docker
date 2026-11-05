<?php

namespace App\Exports;

use App\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Facades\Excel;

class BaseExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct              (1.0) Constructor for initializing the class
    // collection               (2.0) Get the collection of items that should
    //                                be exported
    // headings                 (3.0) Return the headings for the worksheet
    // getFileName              (4.0) Get the file name, current time and
    //                                date for the exported file
    // styles                   (5.0) Styles to be applied to the worksheet
    // download                 (6.0) Handling the download of the exported
    //                                file and logging the export activity.
    // logExportActivity        (7.0) Log the export activity in the system.

    protected ?string $errorMessage = null;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor for initializing the class
     *
     * @param Collection $rows
     * @param array $headings
     */
    public function __construct(
        protected Collection $rows,
        protected array $headings
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> collection
     * <Function> Get the collection of items that should be exported.
     *
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->rows;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> headings
     * <Function> Return the headings for the worksheet
     *
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getFileName
     * <Function> Get the file name, current time and
     *            date for the exported file
     *
     * @param string $prefix
     * @return string
     */
    public static function getFileName(string $prefix): string
    {
        return $prefix . '_' . now('Asia/Manila')->format('Ymd') . '.xlsx';
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> styles
     * <Function> Styles to be applied to the worksheet
     *
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ffc642'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['rgb' => 'A9A9A9'],
                    ],

                ],
            ]
        ];
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> download
     * <Function> Handling the download of the exported file and logging the export activity.
     *
     * @param string $exportName
     * @param mixed $user
     * @param array $properties
     * @return mixed
     */
    public function download(string $exportName, mixed $user, array $properties = []): mixed
    {
        $fileName = $this->getFileName($exportName);

        try {
            $file = Excel::download($this, $fileName);
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->logExportActivity([
                'exportName' => $exportName,
                'fileName'   => $fileName,
                'user'       => $user,
                'properties' => $properties
            ]);
        }
        return $file ?? null;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> logExportActivity
     * <Function> Log the export activity in the system.
     *
     * @param string $exportName
     * @param string $fileName
     * @param mixed $user
     * @param array $properties
     * @return mixed
     */
    public function logExportActivity(array $properties = []): Activity|null
    {
        ['exportName' => $exportName, 'fileName' => $fileName, 'user' => $user, 'properties' => $properties] = $properties;

        $failed = !is_null($this->errorMessage);

        $logType = $failed ? Activity::TYPE_ERROR : Activity::TYPE_INFO;

        $code = $failed ? 'E201' : 'I201';

        return activity()
            ->causedBy($user)
            ->inLog($logType)
            ->withProperties(array_merge([
                'Code'          => $code,
                'Export Name'   => $fileName,
                'Data Exported' => $this->rows->count(),
                'Error'         => $this->errorMessage,
            ], $properties))
            ->log(get_message($logType, $code, [
                'fileName'      => Str::headline($exportName),
                'rows'          => $this->rows->count(),
            ]));
    }
}
