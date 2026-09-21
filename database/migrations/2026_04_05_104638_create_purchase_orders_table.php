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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // Relationship IDs
            $table->unsignedBigInteger('management_id')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();

            // PO Details
            $table->string('po_number')->unique();
            $table->date('po_date');
            $table->string('supplier')->nullable();

            // Item Details (Single Item Focus)
            // Note: If a PO has MULTIPLE items, keeping them in the JSON 'items' column is better.
            // But if you need these specific fields for the primary item:
            $table->string('stock_no')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 12, 2)->default(0);

            // Totals and Payload
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->json('items'); // This remains for your multi-row array support

            $table->string('po_attachment')->nullable();
            $table->enum('status', ['pending_inspection', 'approved', 'rejected'])->default('pending_inspection');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};