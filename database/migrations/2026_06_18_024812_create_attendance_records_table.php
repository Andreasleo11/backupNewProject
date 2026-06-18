<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->date('shift_date');
            $table->unsignedTinyInteger('alpha')->default(0);
            $table->unsignedTinyInteger('telat')->default(0);
            $table->unsignedTinyInteger('izin')->default(0);
            $table->unsignedTinyInteger('sakit')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['nik', 'shift_date']);
            $table->index('shift_date');
            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
