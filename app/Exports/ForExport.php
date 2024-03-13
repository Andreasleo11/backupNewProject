<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;


class ForExport implements FromView, ShouldAutoSize
{
    use Exportable;

    private $monthm;
    private $materials;
    private $values;
    private $uniqueMonths;
    private $vendorCode;
    private $qforecast;
    private $vendorname;

    public function __construct($monthm, $materials, $values, $uniqueMonths, $vendorCode, $qforecast,$vendorname)
    {
        $this->monthm = $monthm;
        $this->materials = $materials;
        $this->values = $values;
        $this->uniqueMonths = $uniqueMonths;
        $this->vendorCode = $vendorCode;
        $this->qforecast = $qforecast;
        $this->vendorname = $vendorname;
    }

    public function view(): View
    {
        return view('purchasing.foremind_detail_print_excel', [
            'monthm' => $this->monthm,
            'materials' => $this->materials,
            'values' => $this->values,
            'mon' => $this->uniqueMonths,
            'vendorCode' => $this->vendorCode,
            'qforecast' => $this->qforecast,
            'vendorName' => $this->vendorname,
        ]);
    }
}
