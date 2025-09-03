<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

use Illuminate\Support\Collection;

class DefectReportSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $defectData;

    public function __construct($defectData)
    {
        $this->defectData = $defectData;
    }

    public function collection()
    {
        $data = [];

        // Extract unique defect categories
        $defectCategories = [];
        foreach ($this->defectData as $item) {
            foreach ($item["defects"] as $defect) {
                $defectCategories[$defect["category_name"]] = true;
            }
        }

        // Generate rows with dynamic defect category columns
        foreach ($this->defectData as $item) {
            $row = [
                "Part Name" => $item["part_name"],
                "Quantity" => $item["rec_quantity"],
            ];

            foreach ($defectCategories as $category => $_) {
                $row[$category] = 0;
            }

            foreach ($item["defects"] as $defect) {
                $row[$defect["category_name"]] = $defect["quantity"];
            }

            $data[] = $row;
        }

        return new Collection($data);
    }

    public function headings(): array
    {
        // Extract unique defect categories
        $defectCategories = [];
        foreach ($this->defectData as $item) {
            foreach ($item["defects"] as $defect) {
                $defectCategories[$defect["category_name"]] = true;
            }
        }

        // Generate headings with dynamic defect category columns
        $headings = ["Part Name", "Quantity"];
        foreach ($defectCategories as $category => $_) {
            $headings[] = $category;
        }

        return $headings;
    }

    public function title(): string
    {
        return "Defects";
    }
}
