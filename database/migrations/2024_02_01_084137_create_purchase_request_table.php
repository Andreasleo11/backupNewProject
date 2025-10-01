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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id_create')->constrained();
            $table->date('date_pr')->nullable();
            $table->date('date_required')->nullable();
            $table->string('remark')->nullable();
            $table->string('to_department')->nullable();
            $table->string('autograph_1')->nullable();
            $table->string('autograph_2')->nullable();
            $table->string('autograph_3')->nullable();
            $table->string('autograph_4')->nullable();
            $table->string('autograph_user_1')->nullable();
            $table->string('autograph_user_2')->nullable();
            $table->string('autograph_user_3')->nullable();
            $table->string('autograph_user_4')->nullable();
            $table->binary('attachment_pr')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
