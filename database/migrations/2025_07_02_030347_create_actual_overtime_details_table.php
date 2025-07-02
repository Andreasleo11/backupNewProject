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
        Schema::create('actual_overtime_details', function (Blueprint $table) {
            $table->id();
            $table->integer('key');
            $table->string('voucher');
            $table->date('in_date')->nullable();
            $table->time('in_time')->nullable();
            $table->date('out_date')->nullable();
            $table->time('out_time')->nullable();
            $table->decimal('nett_overtime', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actual_overtime_details');
    }
};
