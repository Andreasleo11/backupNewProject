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
        Schema::create('department_compliance_monthlies', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->date('month'); // first day of month
            $t->unsignedTinyInteger('percent')->default(0);
            $t->timestamps();

            $t->unique(['department_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_compliance_monthlies');
    }
};
