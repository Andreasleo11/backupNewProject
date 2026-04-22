<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('reporter_id')->comment('FK to employees (nik)');
            $table->foreignId('assigned_to')->nullable()->comment('FK to users table. Single PIC.');
            $table->foreignId('category_id')->constrained('ticket_categories');

            $table->string('title');
            $table->text('description');

            $table->string('status')->default('Open')->comment('Enum: Open, In Progress, On Hold, Resolved, Closed');
            $table->string('priority')->default('Medium')->comment('Enum: Low, Medium, High, Critical');

            // SLA and Tracking timestamps
            $table->timestamp('first_response_at')->nullable()->comment('When PIC first interacts');
            $table->timestamp('resolved_at')->nullable()->comment('When ticket is marked resolved');
            $table->timestamp('on_hold_since')->nullable()->comment('When ticket entered On Hold state');

            $table->integer('total_hold_time_minutes')->default(0)->comment('Total time spent on hold in minutes');
            $table->integer('reopen_count')->default(0)->comment('Number of times transitioned from Resolved to Open');

            $table->timestamps();
            $table->softDeletes();

            // Indices for fast dashboards
            $table->index('status');
            $table->index('priority');
            $table->index('assigned_to');
            $table->index('reporter_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
