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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('vendor_code')->after('vendor_name')->nullable();
            $table->date('posting_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->text('bill_to')->nullable();
            $table->text('ship_to')->nullable();
            $table->double('total_tax')->after('total_before_tax')->default(0);
            $table->string('payment_terms')->nullable();
            $table->text('remarks')->nullable();
            $table->string('contact_person_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('vendor_code');
            $table->dropColumn('posting_date');
            $table->dropColumn('delivery_date');
            $table->dropColumn('bill_to');
            $table->dropColumn('ship_to');
            $table->dropColumn('total_tax');
            $table->dropColumn('payment_terms');
            $table->dropColumn('remarks');
            $table->dropColumn('contact_person_name');
        });
    }
};
