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
        // verification_reports
        Schema::table('verification_reports', function (Blueprint $t) {
            // add
            $t->date('rec_date')->after('creator_id');
            $t->date('verify_date')->after('rec_date');
            $t->string('customer', 191)->after('verify_date');
            $t->string('invoice_number', 191)->after('customer');

            // drop old
            $t->dropColumn(['title', 'description']);
        });

        // verification_items
        Schema::table('verification_items', function (Blueprint $t) {
            // add
            $t->string('part_name')->after('verification_report_id');
            $t->decimal('rec_quantity', 18, 4)->after('part_name');
            $t->decimal('verify_quantity', 18, 4)->after('rec_quantity');
            $t->decimal('can_use', 18, 4)->after('verify_quantity');
            $t->decimal('cant_use', 18, 4)->after('can_use');
            $t->decimal('price', 18, 2)->after('cant_use');
            $t->string('currency', 10)->after('price');

            // drop old
            $t->dropColumn(['notes', 'amount', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // reverse (best-effort)
        Schema::table('verification_reports', function (Blueprint $t) {
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->dropColumn(['rec_date', 'verify_date', 'customer', 'invoice_number']);
        });

        Schema::table('verification_items', function (Blueprint $t) {
            $t->text('notes')->nullable();
            $t->decimal('amount', 18, 2)->default(0);
            $t->dropColumn(['rec_quantity', 'verify_quantity', 'can_use', 'cant_use', 'price', 'currency']);
        });
    }
};
