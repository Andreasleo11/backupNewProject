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
        Schema::create('verification_item_defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_item_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64)->nullable();
            $table->string('name', 191);
            $table->string('severity', 20)->default('LOW');
            $table->string('source', 20)->default('DAIJO');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_item_defects');
    }
};
