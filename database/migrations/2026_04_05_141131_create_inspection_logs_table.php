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
        Schema::create('inspection_logs', function (Blueprint $table) {
            $table->id();
            // I-link natin sa main Purchase Order
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');

            // Sino ang nag-inspect? (Inspector User ID)
            $table->foreignId('inspector_id')->constrained('users');

            // Ano ang desisyon? (Approved / Rejected)
            $table->enum('decision', ['approved', 'rejected']);

            // Bakit na-reject? (Optional notes)
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_logs');
    }
};
