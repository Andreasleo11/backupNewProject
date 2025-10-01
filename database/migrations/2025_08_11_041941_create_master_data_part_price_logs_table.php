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
        Schema::create('master_data_part_price_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->nullable()->constrained('reports')->nullOnDelete();
            $table->foreignId('detail_id')->nullable()->constrained('details')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('part_code', 100);
            $table->string('currency')->nullable();
            $table->decimal('price', 15, 2)->unsigned();
            $table->timestamps();
            $table->index(['part_code', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data_part_price_logs');
    }
};
