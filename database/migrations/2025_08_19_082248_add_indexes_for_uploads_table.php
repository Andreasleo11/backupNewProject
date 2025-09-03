<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("uploads", function (Blueprint $table) {
            $table->index("created_at");
            $table->index("size");
            $table->index("mime_type");
            $table->index("uploaded_by");
            $table->index("original_name");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("uploads", function (Blueprint $table) {
            $table->dropIndex("created_at");
            $table->dropIndex("size");
            $table->dropIndex("mime_type");
            $table->dropIndex("uploaded_by");
            $table->dropIndex("original_name");
        });
    }
};
