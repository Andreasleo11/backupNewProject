<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("files", function (Blueprint $table) {
            $table->id();
            $table->string("doc_id");
            $table->string("name");
            $table->string("mime_type")->nullable();
            $table->binary("data");
            $table->unsignedBigInteger("size")->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE files MODIFY data LONGBLOB");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("files");
    }
};
