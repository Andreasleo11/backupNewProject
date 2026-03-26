<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_page_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('route_name', 120);
            $table->unsignedInteger('visit_count')->default(1);
            $table->timestamp('last_visited_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'route_name']);
            $table->index(['user_id', 'visit_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_page_visits');
    }
};
