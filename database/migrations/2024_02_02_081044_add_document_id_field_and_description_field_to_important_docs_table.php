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
        Schema::table("important_docs", function (Blueprint $table) {
            $table->string("document_id")->after("expired_date")->nullable();
            $table->string("description")->after("document_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("important_docs", function (Blueprint $table) {
            $table->dropColumn("document_id");
            $table->dropColumn("description");
        });
    }
};
