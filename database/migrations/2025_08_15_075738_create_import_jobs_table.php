<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("import_jobs", function (Blueprint $table) {
            $table->id();
            $table->string("type");
            $table->unsignedBigInteger("total_rows")->default(0);
            $table->unsignedBigInteger("processed_rows")->default(0);
            $table
                ->enum("status", ["pending", "running", "completed", "failed"])
                ->default("pending");
            $table->text("error")->nullable();
            $table->timestamp("started_at")->nullable();
            $table->timestamp("finished_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("import_jobs");
    }
};
