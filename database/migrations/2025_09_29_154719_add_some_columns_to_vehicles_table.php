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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('driver_name');
            $table->string('model')->nullable()->after('brand');
            $table->year('year')->after('model')->nullable();
            $table->string('vin')->nullable()->after('year');
            $table->unsignedInteger('odometer')->default(0)->after('vin'); // in km
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active')->after('odometer');
            $table->softDeletes();
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = [
            'brand',
            'model',
            'year',
            'vin',
            'odometer',
            'status',
        ];

        Schema::table('vehicles', function (Blueprint $table) use ($columnsToDrop) {
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('vehicles', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Drop softDeletes column if it exists
            if (Schema::hasColumn('vehicles', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
