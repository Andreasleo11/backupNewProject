<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('evaluation_data_weekly');
    }

    public function down(): void
    {
        // evaluation_data_weekly is superseded by attendance_records; intentionally not restored
    }
};
