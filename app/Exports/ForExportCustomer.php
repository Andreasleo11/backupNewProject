<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ForExportCustomer implements FromView, ShouldAutoSize, WithStyles
{
    use Exportable;

    private $monthm;
    private $materials;
    private $values;
    private $uniqueMonths;
    private $vendorCode;
    private $qforecast;
    private $vendorname;
    private $contact;

    public function __construct(
        $monthm,
        $materials,
        $values,
        $uniqueMonths,
        $vendorCode,
        $qforecast,
        $vendorname,
        $contact,
    ) {
        $this->monthm = $monthm;
        $this->materials = $materials;
        $this->values = $values;
        $this->uniqueMonths = $uniqueMonths;
        $this->vendorCode = $vendorCode;
        $this->qforecast = $qforecast;
        $this->vendorname = $vendorname;
        $this->vendorname = $vendorname;
        $this->contact = $contact;
    }

    public function view(): View
    {
        return view("purchasing.foremind_detail_print_customer_excel", [
            "monthm" => $this->monthm,
            "materials" => $this->materials,
            "values" => $this->values,
            "mon" => $this->uniqueMonths,
            "vendorCode" => $this->vendorCode,
            "qforecast" => $this->qforecast,
            "vendorName" => $this->vendorname,
            "vendorName" => $this->vendorname,
            "contact" => $this->contact,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Apply all borders to all cells
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            "borders" => [
                "allBorders" => [
                    "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    "color" => ["rgb" => "000000"],
                ],
            ],
        ]);
    }
}
