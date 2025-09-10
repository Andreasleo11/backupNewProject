<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\MasterDataPartPriceLog;

class PartPriceLogImport extends Component
{
    use WithFileUploads;
    public $file;
    public $preview = [];
    public $errorsBag = [];
    public $imported = 0;

    protected $rules = [
        "file" => "required|file|mimes:xlsx,xls,csv,txt|max:2048",
    ];

    public function updatedFile()
    {
        $this->reset(["preview", "errorsBag", "imported"]);
        $this->validate();

        // Load as a collection (first sheet)
        $collection = Excel::toCollection(null, $this->file)->first(); // Collection of rows (arrays)

        if (!$collection || $collection->isEmpty()) {
            $this->addError("file", "The uploaded file appears to be empty.");
            return;
        }

        // Try to detect header row and map indices
        $rows = $collection->toArray();

        // If the first row is header strings, build a header map
        $header = array_map(fn($h) => trim((string) $h), $rows[0]);
        $hasHeaders = $this->looksLikeHeader($header);

        $start = $hasHeaders ? 1 : 0;
        $map = $hasHeaders
            ? $this->makeHeaderMap($header) // returns ['parent_item' => idx, 'description' => idx, 'total' => idx, 'original_currency' => idx]
            : ["parent_item" => 1, "description" => 2, "total" => 3, "currency" => 4]; // fallback by expected positions

        $preview = [];
        $errors = [];

        for ($i = $start; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Guard for short rows
            $parentItem = $row[$map["parent_item"]] ?? null;
            $description = $row[$map["description"]] ?? null;
            $totalRaw = $row[$map["total"]] ?? null;
            $originalCurrency = $row[$map["original_currency"]] ?? null;

            $line = $i + 1; // human line number

            $partCode = $this->normalizePartCode($parentItem);
            $price = $this->normalizePrice($totalRaw);
            $currency = $this->normalizeCurrency($originalCurrency);

            if (!$partCode) {
                $errors[] = "Row {$line}: Missing/invalid Parent Item.";
                continue;
            }
            if ($price === null) {
                $errors[] = "Row {$line} ({$partCode}): Missing/invalid price.";
                continue;
            }

            $preview[] = [
                "part_code" => $partCode,
                "description" => trim((string) $description),
                "price" => $price,
                "currency" => $currency,
            ];
        }

        $this->preview = $preview;
        $this->errorsBag = $errors;
    }

    public function import()
    {
        if (empty($this->preview)) {
            $this->addError("file", "Nothing to import. Please upload a valid file.");
            return;
        }

        $count = 0;
        foreach ($this->preview as $row) {
            MasterDataPartPriceLog::create([
                "report_id" => null, // set if you want to bind to a report
                "detail_id" => null, // set if you want to bind to a detail
                "created_by" => Auth::user()->id,
                "part_code" => $row["part_code"],
                "currency" => $row["currency"],
                "price" => $row["price"], // numeric float
            ]);
            $count++;
        }

        $this->imported = $count;
        $this->dispatch("notify", [
            "type" => "success",
            "message" => "Imported {$count} price log row(s).",
        ]);

        // Optional: clear preview/file
        $this->reset(["file", "preview"]);
    }

    private function looksLikeHeader(array $header): bool
    {
        $joined = strtolower(implode(" ", $header));
        return Str::contains($joined, "parent") ||
            Str::contains($joined, "item") ||
            Str::contains($joined, "total") ||
            Str::contains($joined, "original");
    }

    private function makeHeaderMap(array $header): array
    {
        // Normalize header names
        $normalized = array_map(
            fn($h) => strtolower(preg_replace("/\s+/", " ", trim((string) $h))),
            $header,
        );

        $find = function ($keys) use ($normalized) {
            foreach ($keys as $k) {
                foreach ($normalized as $i => $h) {
                    if (Str::contains($h, $k)) {
                        return $i;
                    }
                }
            }
            return null;
        };

        return [
            "parent_item" =>
                $find(["parent item", "parent", "item code", "part", "part item"]) ?? 1,
            "description" => $find(["item description", "description", "desc"]) ?? 2,
            "total" => $find(["total", "price", "amount"]) ?? 3,
            "original_currency" => $find(["original currency", "original", "currency"]) ?? 4,
        ];
    }

    private function normalizePartCode($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $code = trim((string) $value);
        return $code !== "" ? $code : null;
    }

    private function normalizePrice($value): ?float
    {
        if ($value === null || $value === "") {
            return null;
        }

        // Convert to string and strip spaces
        $str = trim((string) $value);

        // Handle negatives in parentheses e.g. (1,234.56)
        $negative = false;
        if (Str::startsWith($str, "(") && Str::endsWith($str, ")")) {
            $negative = true;
            $str = trim($this->between($str, "(", ")"));
        }

        // If looks like a plain number already
        if (is_numeric($str)) {
            $num = (float) $str;
            return $negative ? -$num : $num;
        }

        // Common CSV/Excel formats: "2,200.00" or "14,693.28"
        // Remove thousands separators, keep decimal point
        $str = str_replace(",", "", $str);

        // Some locales use "2.200,00"
        // If we now have "2200.00" good; if we see multiple dots and a comma, swap
        if (preg_match('/^\d+(\.\d{3})+,\d+$/', str_replace(" ", "", (string) $value))) {
            // original had thousand dots and decimal comma
            $str = str_replace(".", "", (string) $value);
            $str = str_replace(",", ".", $str);
        }

        if (!is_numeric($str)) {
            return null;
        }

        $num = (float) $str;
        return $negative ? -$num : $num;
    }

    private function normalizeCurrency(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $v = strtoupper(trim($value));

        // Map common symbols/names to ISO
        $map = [
            "RP" => "IDR",
            "IDR" => "IDR",
            "RUPIAH" => "IDR",
            '$' => "USD",
            "USD" => "USD",
            "US DOLLAR" => "USD",
            "EUR" => "EUR",
            "€" => "EUR",
            "JPY" => "JPY",
            "¥" => "JPY",
            "SGD" => "SGD",
            "MYR" => "MYR",
            "CNY" => "CNY",
            "RMB" => "CNY",
            "元" => "CNY",
        ];

        // If already a known 3-letter code, keep it
        if (preg_match('/^[A-Z]{3}$/', $v)) {
            return $v;
        }

        // Try map by cleaned token (remove punctuation)
        $clean = preg_replace("/[^A-Z]/", "", $v) ?? "";
        return $map[$v] ?? ($map[$clean] ?? $v); // fallback to raw (stored as-is)
    }

    private function between(string $s, string $start, string $end): string
    {
        $p1 = strpos($s, $start);
        $p2 = strrpos($s, $end);
        if ($p1 === false || $p2 === false || $p2 <= $p1) {
            return $s;
        }
        return substr($s, $p1 + strlen($start), $p2 - $p1 - strlen($start));
    }

    public function render()
    {
        return view("livewire.part-price-log-import");
    }
}
