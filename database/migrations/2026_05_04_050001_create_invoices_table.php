<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->cascadeOnDelete();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->string('total_currency', 10)->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Performance index for the FK
            $table->index('purchase_order_id', 'invoices_po_id_index');

            // One invoice_number per PO (NULLs are permitted — MySQL treats NULL ≠ NULL in unique indexes)
            $table->unique(['purchase_order_id', 'invoice_number'], 'invoices_po_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
