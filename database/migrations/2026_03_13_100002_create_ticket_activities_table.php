<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->comment('Who made the change. Nullable for system actions.');

            $table->string('type')->comment('Enum: status_change, assignment, comment, attachment');

            $table->string('old_state')->nullable()->comment('E.g. Open');
            $table->string('new_state')->nullable()->comment('E.g. In Progress');
            $table->text('reason')->nullable()->comment('Used for comments or hold/reopen reasons');

            $table->timestamps(); // created_at serves as immutable audit timestamp

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_activities');
    }
};
