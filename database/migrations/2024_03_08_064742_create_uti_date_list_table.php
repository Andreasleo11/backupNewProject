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
        Schema::create('uti_date_list', function (Blueprint $table) {
            $table->id();
            $table->string("description")->nullable();
			$table->dateTime("last_update")->nullable();			
			$table->date("start_date")->nullable();
			$table->date("end_date")->nullable();
			$table->integer("date_interval")->nullable();
			$table->integer("additional_value")->nullable();
			$table->decimal("additional_value_dec",7,2)->nullable();
			$table->integer("start_day")->nullable();
			$table->integer("start_month")->nullable();
			$table->integer("start_year")->nullable();
			$table->integer("end_day")->nullable();
			$table->integer("end_month")->nullable();
			$table->integer("end_year")->nullable();
			$table->integer("max_man_power")->nullable();
			$table->integer("max_mould_change")->nullable();
			$table->integer("lead_time_fg")->nullable();
			$table->integer("lead_time_wip")->nullable();				
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uti_date_list');
    }
};
