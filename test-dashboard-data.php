<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Testing dashboard data format...\n";

    // Test the service data
    $service = app(\App\Services\PurchaseOrderService::class);
    $data = $service->getDashboardData('2024-01');

    echo 'Service data keys: ' . implode(', ', array_keys($data)) . "\n";

    foreach ($data as $key => $value) {
        if (is_object($value) && method_exists($value, 'count')) {
            echo "$key: Collection with " . $value->count() . " items\n";
            if ($value->count() > 0) {
                echo '  First item: ' . json_encode($value->first()) . "\n";
            }
        } elseif (is_array($value)) {
            echo "$key: Array with " . count($value) . " items\n";
            if (count($value) > 0) {
                echo '  First item: ' . json_encode(array_slice($value, 0, 1)) . "\n";
            }
        } else {
            echo "$key: " . json_encode($value) . "\n";
        }
    }

    // Test the component
    $dashboard = new \App\Livewire\PurchaseOrderDashboard;
    $dashboard->selectedMonth = '2024-01';
    $dashboard->loadDashboardData();

    echo "\nComponent properties after load:\n";
    echo 'monthlyTotals: ' . (is_object($dashboard->monthlyTotals) ? 'Collection(' . $dashboard->monthlyTotals->count() . ')' : gettype($dashboard->monthlyTotals)) . "\n";
    echo 'statusCounts: ' . json_encode($dashboard->statusCounts) . "\n";
    echo 'categoryChartData: ' . (is_object($dashboard->categoryChartData) ? 'Collection(' . $dashboard->categoryChartData->count() . ')' : gettype($dashboard->categoryChartData)) . "\n";

    echo "\n✅ Data format testing complete\n";

} catch (\Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . "\n";
    echo 'Line: ' . $e->getLine() . "\n";
}
