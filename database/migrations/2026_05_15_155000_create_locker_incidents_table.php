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
        Schema::create('locker_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locker_assignment_id')->constrained()->onDelete('cascade');
            $table->string('type')->default('lost_key');
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamp('reported_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locker_incidents');
    }
};
