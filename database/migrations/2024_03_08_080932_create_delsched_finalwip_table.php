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
        Schema::create('delsched_finalwip', function (Blueprint $table) {
            $table->id();
            $table->integer('fglink_id')->nullable();
			$table->date('delivery_date')->nullable();
			$table->string('so_number')->nullable();
			$table->string('customer_code')->nullable();
			$table->string('customer_name')->nullable();
			$table->string('item_code')->nullable();
			$table->string('item_name')->nullable();
			$table->integer('outstanding_del')->nullable();
			$table->string('wip_code')->nullable();
			$table->string('wip_name')->nullable();
			$table->integer('departement')->nullable();
			$table->integer('bom_level')->nullable();
			$table->integer('bom_quantity')->nullable();
			$table->integer('req_quantity')->nullable();
			$table->integer('stock_wip')->nullable();
			$table->integer('balance_wip')->nullable();
			$table->integer('outstanding_wip')->nullable();
			$table->string('packaging_code')->nullable();
			$table->integer('standar_pack')->nullable();
			$table->integer('packaging_qty')->nullable();
			$table->string('doc_status')->nullable();
			$table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delsched_finalwip');
    }
};
