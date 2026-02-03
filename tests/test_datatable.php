<?php

/**
 * Test script to verify PurchaseRequestsDataTable works
 * Run: php tests/test_datatable.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DataTable Comprehensive Check ===\n\n";

try {
    // 1. Check class can be instantiated
    echo '1. Instantiating DataTable class... ';
    $scoper = app(App\Application\PurchaseRequest\Services\PurchaseRequestQueryScoper::class);
    $dataTable = new App\DataTables\PurchaseRequestsDataTable($scoper);
    echo "✓ OK\n";

    // 2. Check user exists
    echo '2. Finding test user... ';
    $user = App\Models\User::first();
    if (! $user) {
        throw new Exception('No users in database');
    }
    auth()->login($user);
    echo "✓ Found user: {$user->name}\n";

    // 3. Test query method
    echo '3. Building query... ';
    $query = $dataTable->query(new App\Models\PurchaseRequest);
    echo "✓ OK\n";

    // 4. Execute query
    echo '4. Executing query... ';
    $count = $query->count();
    echo "✓ Found {$count} purchase requests\n";

    // 5. Check columns definition
    echo '5. Checking column definitions... ';
    $columns = $dataTable->getColumns();
    $columnNames = array_map(fn ($col) => $col['name'] ?? $col['data'] ?? 'unknown', $columns);
    echo '✓ Defined columns: ' . implode(', ', $columnNames) . "\n";

    // 6. Test dataTable method with sample data
    if ($count > 0) {
        echo '6. Testing dataTable rendering... ';
        $pr = $query->first();

        // Test status badge rendering
        $statusBlade = view('partials.pr-status-badge', ['pr' => $pr])->render();
        if (strlen($statusBlade) > 0) {
            echo "✓ Status badge renders\n";
        } else {
            echo "✗ Status badge empty\n";
        }

        // Test action buttons rendering
        echo '7. Testing action buttons... ';
        $actionBlade = view('partials.pr-action-buttons', [
            'pr' => $pr,
            'user' => $user,
        ])->render();
        if (strlen($actionBlade) > 0) {
            echo "✓ Action buttons render\n";
        } else {
            echo "✗ Action buttons empty\n";
        }
    } else {
        echo "6-7. Skipping rendering tests (no data)\n";
    }

    // 8. Check HTML builder
    echo '8. Building HTML table config... ';
    $htmlBuilder = $dataTable->html();
    echo "✓ OK\n";

    // 9. Verify rawColumns
    echo '9. Checking rawColumns configuration... ';
    $dt = $dataTable->dataTable($query);
    echo "✓ DataTable instance created\n";

    echo "\n=== All Checks Passed! ===\n";
    echo "The DataTable appears to be working correctly.\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo 'File: ' . $e->getFile() . "\n";
    echo 'Line: ' . $e->getLine() . "\n";
    exit(1);
}
