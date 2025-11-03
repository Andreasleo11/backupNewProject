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
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained(); // Foreign key to link with reports table
            $table->string('part_name');
            $table->integer('rec_quantity');
            $table->integer('verify_quantity');
            $table->date('prod_date');
            $table->string('shift');
            $table->string('can_use');
            $table->string('customer_defect');
            $table->string('daijo_defect');
            $table->text('customer_defect_detail')->nullable();
            $table->text('daijo_defect_detail')->nullable();
            $table->text('remark_daijo')->nullable();
            $table->text('remark_customer')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('details');
    }
};
