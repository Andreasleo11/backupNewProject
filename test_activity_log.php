<?php

/**
 * Simple test script to verify Spatie Activity Log is working
 * Run with: php test_activity_log.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Spatie Activity Log Integration ===\n\n";

// Test 1: Create a Detail record
echo "Test 1: Creating a Detail record...\n";
$detail = App\Models\Detail::create([
    'report_id' => 1,
    'part_name' => 'Test Part' . time(),
    'rec_quantity' => 100,
    'verify_quantity' => 95,
    'can_use' => 95,
    'cant_use' => 5,
    'price' => 1000,
]);
echo "✓ Detail created with ID: {$detail->id}\n\n";

// Test 2: Check if activity was logged
echo "Test 2: Checking activity log...\n";
$activity = Spatie\Activitylog\Models\Activity::latest()->first();
if ($activity) {
    echo "✓ Latest activity found:\n";
    echo "  - Description: {$activity->description}\n";
    echo "  - Subject: {$activity->subject_type} (ID: {$activity->subject_id})\n";
    echo "  - Event: {$activity->event}\n";
    echo "  - Causer: {$activity->causer_type} (ID: {$activity->causer_id})\n";
    echo "  - Created at: {$activity->created_at}\n\n";
} else {
    echo "✗ No activity found\n\n";
}

// Test 3: Update the Detail record
echo "Test 3: Updating Detail record...\n";
$detail->update(['price' => 1500]);
echo "✓ Detail updated\n\n";

// Test 4: Check activities for this specific model
echo "Test 4: Checking all activities for this Detail...\n";
$activities = Spatie\Activitylog\Models\Activity::forSubject($detail)->get();
echo "✓ Found {$activities->count()} activity/activities:\n";
foreach ($activities as $act) {
    echo "  - {$act->description} at {$act->created_at}\n";
}
echo "\n";

// Test 5: Delete the test record
echo "Test 5: Deleting test Detail record...\n";
$detail->delete();
echo "✓ Detail deleted\n\n";

// Test 6: Final activity count
echo "Test 6: Final activity check...\n";
$finalActivities = Spatie\Activitylog\Models\Activity::forSubject($detail)->get();
echo "✓ Total activities for deleted model: {$finalActivities->count()}\n";
foreach ($finalActivities as $act) {
    echo "  - {$act->description} at {$act->created_at}\n";
}

echo "\n=== All Tests Completed Successfully! ===\n";
