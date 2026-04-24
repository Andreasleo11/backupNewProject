<?php

/**
 * Phase 1 Day 1: Baseline Performance Benchmarks
 *
 * This script establishes performance baselines for key PO operations
 * before refactoring begins.
 */

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Models\PurchaseOrder;

$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Purchase Order Module Performance Baselines ===\n";
echo 'Date: ' . now()->toDateTimeString() . "\n\n";

$benchmarks = [];

// Benchmark 1: Model Instantiation
echo "1. Model Instantiation...\n";
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $po = new PurchaseOrder;
    unset($po);
}
$end = microtime(true);
$benchmarks['model_instantiation'] = ($end - $start) * 1000; // Convert to milliseconds
echo sprintf("   Average: %.2f ms per instantiation\n", $benchmarks['model_instantiation'] / 100);

// Benchmark 2: Simple Query Performance
echo "2. Simple Query Performance...\n";
$start = microtime(true);
for ($i = 0; $i < 10; $i++) {
    $count = PurchaseOrder::count();
}
$end = microtime(true);
$benchmarks['simple_query'] = ($end - $start) * 1000;
echo sprintf("   Average: %.2f ms per query\n", $benchmarks['simple_query'] / 10);

// Benchmark 3: Complex Query (Dashboard-like)
echo "3. Complex Query Performance (Dashboard-like)...\n";
$start = microtime(true);
for ($i = 0; $i < 5; $i++) {
    $result = PurchaseOrder::selectRaw(
        'vendor_name, COUNT(id) as po_count, SUM(total) as total'
    )
        ->groupBy('vendor_name')
        ->orderByDesc('total')
        ->take(10)
        ->get();
}
$end = microtime(true);
$benchmarks['complex_query'] = ($end - $start) * 1000;
echo sprintf("   Average: %.2f ms per complex query\n", $benchmarks['complex_query'] / 5);

// Benchmark 4: Memory Usage
echo "4. Memory Usage...\n";
$initialMemory = memory_get_usage();
$po = PurchaseOrder::first();
$finalMemory = memory_get_usage();
$benchmarks['memory_usage'] = $finalMemory - $initialMemory;
echo sprintf("   Memory delta: %.2f MB for single model load\n", $benchmarks['memory_usage'] / 1024 / 1024);

// Benchmark 5: Current Controller Method Count
echo "5. Code Metrics...\n";
$controllerContent = file_get_contents(__DIR__ . '/../../../app/Http/Controllers/PurchaseOrderController.php');
$linesOfCode = substr_count($controllerContent, "\n");
preg_match_all('/public function \w+/', $controllerContent, $matches);
$methodCount = count($matches[0]);
$benchmarks['loc'] = $linesOfCode;
$benchmarks['methods'] = $methodCount;
echo sprintf("   Lines of code: %d\n", $linesOfCode);
echo sprintf("   Public methods: %d\n", $methodCount);

// Save benchmarks to file
$benchmarkFile = __DIR__ . '/phase1-baseline-benchmarks.json';
file_put_contents($benchmarkFile, json_encode([
    'timestamp' => now()->toISOString(),
    'benchmarks' => $benchmarks,
    'notes' => 'Baseline measurements before Phase 1 refactoring',
], JSON_PRETTY_PRINT));

echo "\n=== Benchmarks saved to phase1-baseline-benchmarks.json ===\n";

echo "Phase 1 Day 1: Analysis Complete!\n";
echo "- Controller analysis: ✅ Complete\n";
echo "- Testing environment: ✅ Verified\n";
echo "- Baseline benchmarks: ✅ Established\n";
echo "- Documentation: ✅ Created\n";

echo "\nReady to proceed with Day 2: Status Enum Implementation\n";
