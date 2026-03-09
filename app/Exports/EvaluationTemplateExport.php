<?php

namespace App\Exports;

use App\Domain\Evaluation\Services\DepartmentEmployeeResolver;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * EvaluationTemplateExport
 *
 * Generates a grading template Excel file pre-filled with the employee
 * list visible to the current user for a given period.
 *
 * Two types supported:
 *  - 'regular' → 5-field scoring (A/B/C/D), columns: No | NIK | Nama | Kerajinan | Kerapian | Loyalitas | Perilaku | Prestasi
 *  - 'yayasan'|'magang' → 9-field scoring (A/B/C/D/E), columns: No | NIK | Nama | Kemampuan | ... | Integritas
 */
class EvaluationTemplateExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    private string $type;
    private int $month;
    private int $year;

    public function __construct(string $type, int $month, int $year)
    {
        $this->type  = $type;
        $this->month = $month;
        $this->year  = $year;
    }

    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        if ($this->type === 'regular') {
            return [
                'No', 'NIK', 'Nama Karyawan',
                'Kerajinan Kerja (A/B/C/D)',
                'Kerapian Kerja (A/B/C/D)',
                'Loyalitas (A/B/C/D)',
                'Perilaku Kerja (A/B/C/D)',
                'Prestasi (A/B/C/D)',
                'Keterangan',
            ];
        }

        // yayasan / magang
        return [
            'No', 'NIK', 'Nama Karyawan',
            'Kemampuan Kerja (A/B/C/D/E)',
            'Kecerdasan Kerja (A/B/C/D/E)',
            'Kualitas Kerja (A/B/C/D/E)',
            'Disiplin Kerja (A/B/C/D/E)',
            'Kepatuhan Kerja (A/B/C/D/E)',
            'Lembur (A/B/C/D/E)',
            'Efektivitas Kerja (A/B/C/D/E)',
            'Relawan (A/B/C/D/E)',
            'Integritas (A/B/C/D/E)',
            'Keterangan',
        ];
    }

    public function collection()
    {
        $user     = Auth::user();
        $resolver = app(DepartmentEmployeeResolver::class);

        try {
            $employees = match ($this->type) {
                'yayasan' => $resolver->resolveYayasanForUser($user),
                'magang'  => $resolver->resolveMagangForUser($user),
                default   => $resolver->resolveForUser($user),
            };
        } catch (\Throwable) {
            $employees = collect();
        }

        return $employees->values()->map(function ($emp, $idx) {
            $base = [
                $idx + 1,           // No
                $emp->nik ?? '',    // NIK
                $emp->name ?? '',   // Nama
            ];

            // Score columns are left blank for the grader to fill in
            $scoreCount = $this->type === 'regular' ? 5 : 9;

            return array_merge($base, array_fill(0, $scoreCount, ''), ['']);
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row — dark indigo background, white text, bold
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3730A3']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $sheet->getHighestRow();
                $lastCol   = $sheet->getHighestColumn();
                $dataRange = "A1:{$lastCol}{$lastRow}";

                // Grid borders
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(
                    Border::BORDER_THIN
                );

                // Freeze the header row
                $sheet->freezePane('A2');

                // Alternating row shading starting from row 2
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FFF5F3FF'); // light purple
                    }
                }

                // Add a legend note below the data
                $legendRow = $lastRow + 2;

                if ($this->type === 'regular') {
                    $sheet->setCellValue("A{$legendRow}", 'Keterangan Nilai: A = Sangat Baik (10), B = Baik (7.5), C = Cukup (5), D = Kurang (2.5). Prestasi: A=20, B=15, C=10, D=5.');
                } else {
                    $sheet->setCellValue("A{$legendRow}", 'Keterangan Nilai: A = Sangat Baik, B = Baik, C = Cukup, D = Kurang Baik, E = Tidak Baik (0).');
                }

                $sheet->getStyle("A{$legendRow}")->getFont()->setItalic(true)->setSize(9);
                $sheet->mergeCells("A{$legendRow}:{$lastCol}{$legendRow}");
            },
        ];
    }
}
