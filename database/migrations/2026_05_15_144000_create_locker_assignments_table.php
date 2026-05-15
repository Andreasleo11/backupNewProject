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
        Schema::create('locker_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locker_id')->constrained('lockers')->onDelete('cascade');
            $table->string('employee_id'); // nik
            $table->foreign('employee_id')->references('nik')->on('employees')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locker_assignments');
    }
};
