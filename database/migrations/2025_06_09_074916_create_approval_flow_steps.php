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
        Schema::create("approval_flow_steps", function (Blueprint $table) {
            $table->id();
            $table->foreignId("approval_flow_id")->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger("step_order");
            $table->string("role_slug");
            $table->boolean("mandatory")->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("approval_flow_steps");
    }
};
