<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\StockReportMail;
use App\Models\SapDelsched;
use App\Models\SapReject;
use App\Models\SapInventoryFg;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Exports\StockReportExport;
use App\Models\User;

class SendDailyStockReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "email:daily-stock-report";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Send daily stock report email with Excel file attachment";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now();
        $currentMonth = $today->format("Y-m");

        // Fetch delivery schedule data for the current month
        $data = SapDelsched::whereMonth("delivery_date", $today->month)
            ->whereYear("delivery_date", $today->year)
            ->get();

        // Fetch reject data
        $rejectdatas = SapReject::all();

        // Group data by month and item_code, then count occurrences
        $itemCounts = $data
            ->groupBy(function ($item) {
                return Carbon::parse($item->delivery_date)->format("Y-m");
            })
            ->map(function ($group) {
                return $group->groupBy("item_code")->map(function ($itemGroup) {
                    return $itemGroup->count();
                });
            });

        // Fetch inventory data
        $inventoryData = SapInventoryFg::all();
        $inventoryMap = $inventoryData->keyBy("item_code");

        // Combine data with inventory info
        $result = $itemCounts->map(function ($group) use ($inventoryMap, $rejectdatas) {
            return $group->map(function ($count, $itemCode) use ($inventoryMap, $rejectdatas) {
                $inventory = $inventoryMap->get($itemCode);
                $inventoryInfo = [
                    "in_stock" => null,
                    "item_name" => null,
                    "warehouse" => null,
                ];

                if ($inventory) {
                    $inventoryInfo["in_stock"] = $inventory->stock;
                    $inventoryInfo["item_name"] = $inventory->item_name;
                    $inventoryInfo["warehouse"] = $inventory->warehouse;

                    foreach ($rejectdatas as $reject) {
                        if ($reject->item_no === $inventory->item_code) {
                            $inventoryInfo["in_stock"] -= $reject->in_stock;
                        }
                    }
                }
                return $inventoryInfo;
            });
        });

        // Calculate total quantities for the current month
        $totalQuantities = $data
            ->groupBy(function ($item) {
                return Carbon::parse($item->delivery_date)->format("Y-m");
            })
            ->map(function ($group) {
                return $group->groupBy("item_code")->map(function ($itemGroup) {
                    return $itemGroup->sum("delivery_qty");
                });
            });

        // Generate the Excel file
        $filePath = storage_path("app/public/daily_stock_report.xlsx");
        Excel::store(
            new StockReportExport($totalQuantities, $itemCounts, $result),
            "public/daily_stock_report.xlsx",
        );

        // List of recipients
        $recipients = [
            User::where("name", "raditya")->first()->email,
            User::where("name", "budiman")->first()->email,
        ];

        // Send email to multiple users
        Mail::to($recipients)->send(new StockReportMail($filePath));

        $this->info("Daily stock report email sent successfully!");
    }
}
