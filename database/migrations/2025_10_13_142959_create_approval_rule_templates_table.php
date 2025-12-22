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
        Schema::create('approvals_rule_templates', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');               // e.g. App\...\VerificationReportModel
            $table->string('code')->nullable();         // e.g. "VR-GENERAL"
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('priority')->default(100); // lower = higher priority
            $table->json('match_expr')->nullable();     // {"department":"FIN","amount_gt":100000000}
            $table->timestamps();
            $table->index(['model_type', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_rule_templates');
    }
};
