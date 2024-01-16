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
        Schema::create('trials', function (Blueprint $table) {
            $table->id();
            $table->string('customer');
            $table->string('part_name');
            $table->string('part_no');
            $table->string('model');
            $table->string('cavity');
            $table->string('status_trial');
            $table->string('material');
            $table->string('status_material');
            $table->string('color');
            $table->string('material_consump');
            $table->string('dimension_tooling');
            $table->string('member_trial');
            $table->date('request_trial');
            $table->date('trial_date');
            $table->time('time_set_up_tooling');
            $table->time('time_setting_tooling');
            $table->time('time_finish_inject');
            $table->time('time_set_down_tooling');
            $table->string('trial_cost');
            $table->string('tonage')->default('');
            $table->string('qty');
            $table->string('adjuster');
            $table->string('requested_by');
            $table->string('requested_by_name');
            $table->string('verify_by');
            $table->string('verify_by_name');
            $table->string('confirmed_by_1');
            $table->string('confirmed_by_name_1');
            $table->string('confirmed_by_2');
            $table->string('confirmed_by_name_2');
            $table->string('confirmed_by_3');
            $table->string('confirmed_by_name_3');
            $table->string('approved_by');
            $table->string('approved_by_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trials');
    }
};
