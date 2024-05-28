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
        Schema::create('detail_form_overtime', function (Blueprint $table) {
            $table->id();
            $table->integer("NIK");
            $table->string("nama");
            $table->string("is_makan")->default("Y");
            $table->string("job_desc")->nullable();
            $table->date("start_date");
            $table->time("start_time");
            $table->date("end_date");
            $table->time("end_time");
            $table->integer("break");
            $table->string("remarks")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_form_overtime');
    }
};
