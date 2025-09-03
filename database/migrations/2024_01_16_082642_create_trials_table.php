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
        Schema::create("trials", function (Blueprint $table) {
            $table->id();
            $table->string("customer");
            $table->string("part_name");
            $table->string("part_no");
            $table->string("model");
            $table->string("cavity");
            $table->string("status_trial");
            $table->string("material");
            $table->string("status_material");
            $table->string("color");
            $table->string("material_consump");
            $table->string("dimension_tooling");
            $table->string("member_trial");
            $table->date("request_trial");
            $table->date("trial_date");
            $table->time("time_set_up_tooling");
            $table->time("time_setting_tooling");
            $table->time("time_finish_inject");
            $table->time("time_set_down_tooling");
            $table->string("trial_cost");
            $table->string("tonage")->default("");
            $table->string("qty");
            $table->string("adjuster");
            $table->string("autograph_1");
            $table->string("autograph_user_1");
            $table->string("autograph_2");
            $table->string("autograph_user_2");
            $table->string("autograph_3");
            $table->string("autograph_user_3");
            $table->string("autograph_4");
            $table->string("autograph_user_4");
            $table->string("autograph_5");
            $table->string("autograph_user_5");
            $table->string("autograph_6");
            $table->string("autograph_user_6");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("trials");
    }
};
