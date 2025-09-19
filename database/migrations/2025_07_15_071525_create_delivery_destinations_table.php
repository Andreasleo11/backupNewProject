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
        Schema::create("delivery_destinations", function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId("delivery_note_id")
                ->constrained("delivery_notes")
                ->onDelete("cascade");
            $table->string("destination");
            $table->string("delivery_order_number");
            $table->string("remarks")->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("delivery_destinations");
    }
};
