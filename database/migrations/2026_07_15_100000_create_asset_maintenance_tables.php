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
        Schema::create('maintenance_checklist_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('maintenance_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('maintenance_checklist_groups')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('asset_maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->unsignedTinyInteger('period'); // 1, 2, or 3 (caturwulan)
            $table->unsignedSmallInteger('year');
            $table->date('revision_date')->nullable();
            $table->timestamps();

            $table->unique(['asset_id', 'period', 'year']);
        });

        Schema::create('maintenance_report_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('asset_maintenance_reports')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('maintenance_checklist_items')->cascadeOnDelete();
            $table->enum('condition', ['good', 'bad']);
            $table->text('remark')->nullable();
            $table->string('checked_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_report_details');
        Schema::dropIfExists('asset_maintenance_reports');
        Schema::dropIfExists('maintenance_checklist_items');
        Schema::dropIfExists('maintenance_checklist_groups');
    }
};
