<?php

use App\Models\File;
use App\Models\PurchaseRequest;
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
        // Step 1: Migrate attachment_pr data to File model
        PurchaseRequest::whereNotNull('attachment_pr')
            ->whereNot('attachment_pr', '')
            ->each(function ($pr) {
                // Only create if doc_num exists and file doesn't already exist
                if ($pr->doc_num && !File::where('doc_id', $pr->doc_num)->where('filename', $pr->attachment_pr)->exists()) {
                    try {
                        File::create([
                            'doc_id' => $pr->doc_num,
                            'filename' => $pr->attachment_pr,
                            'path' => 'pr_attachments/' . $pr->attachment_pr,
                            'type' => 'attachment',
                            'uploaded_by' => $pr->user_id_create,
                            'created_at' => $pr->created_at,
                        ]);
                    } catch (\Exception $e) {
                        // Log error but don't fail migration
                        \Log::warning("Failed to migrate attachment for PR {$pr->id}: " . $e->getMessage());
                    }
                }
            });

        // Step 2: Drop description column (unused)
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        // Step 3: Drop attachment_pr column (migrated to File model)
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn('attachment_pr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add columns
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('description')->nullable()->after('supplier');
            $table->string('attachment_pr')->nullable()->after('description');
        });

        // Note: We cannot reliably reverse the File model migration
        // Manual intervention required if rollback is needed
    }
};
