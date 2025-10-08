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
        Schema::create('department_compliance_snapshots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('department_id')->constrained()->cascadeOnDelete();
            $t->unsignedTinyInteger('percent')->default(0); // 0..100
            $t->unsignedInteger('complete_requirements')->default(0);
            $t->unsignedInteger('total_requirements')->default(0);
            $t->timestamp('generated_at')->useCurrent();
            $t->timestamps();

            $t->unique(['department_id']); // keep latest current snapshot per dept
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_compliance_snapshots');
    }
};
