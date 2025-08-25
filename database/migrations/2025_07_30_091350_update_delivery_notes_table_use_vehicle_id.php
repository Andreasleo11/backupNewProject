<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            // Step 1: Add vehicle_id column (nullable for now)
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->foreignId('vehicle_id')->after('id')->nullable()->constrained('vehicles');
            });

            // Step 2: Backfill vehicle_id based on vehicle_number and driver_name
            \App\Models\DeliveryNote::withTrashed()->chunkById(100, function ($notes) {
                foreach ($notes as $note) {
                    $vehicle = \App\Models\Vehicle::where('plate_number', $note->vehicle_number)
                        ->where('driver_name', $note->driver_name)
                        ->first();

                    if ($vehicle) {
                        $note->vehicle_id = $vehicle->id;
                        $note->saveQuietly(); // avoids triggering model events
                    }
                }
            });

            // ✅ Step 3: Only proceed to make it NOT NULL if there are no NULLs
            $nullCount = \App\Models\DeliveryNote::whereNull('vehicle_id')->count();

            if ($nullCount === 0) {
                Schema::table('delivery_notes', function (Blueprint $table) {
                    $table->unsignedBigInteger('vehicle_id')->nullable(false)->change();
                });
            } else {
                // Optional: log or notify that some records could not be matched
                logger()->warning("vehicle_id not updated for $nullCount delivery note(s). Still nullable.");
            }

            // Step 4: Drop old columns
            Schema::table('delivery_notes', function (Blueprint $table) {
                $table->dropColumn(['vehicle_number', 'driver_name']);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->string('vehicle_number')->nullable()->after('branch');
            $table->string('driver_name')->nullable()->after('vehicle_number');
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });
    }
};
