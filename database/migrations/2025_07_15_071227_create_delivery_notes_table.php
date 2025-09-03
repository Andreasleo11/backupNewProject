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
        Schema::create("delivery_notes", function (Blueprint $table) {
            $table->id();
            $table->enum("branch", ["JAKARTA", "KARAWANG"]);
            $table->tinyInteger("ritasi")->unsigned();
            $table->date("delivery_note_date");
            $table->time("departure_time");
            $table->time("return_time")->nullable();
            $table->string("vehicle_number");
            $table->string("driver_name");
            $table->foreignId("approval_flow_id")->constrained("approval_flows");
            $table->enum("status", ["draft", "submitted"]);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("delivery_notes");
    }
};
