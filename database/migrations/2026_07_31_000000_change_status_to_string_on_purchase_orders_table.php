<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE purchase_orders MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending_inspection'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_orders MODIFY status ENUM('pending_inspection', 'ready_for_inspection', 'approved', 'rejected') NOT NULL DEFAULT 'pending_inspection'");
    }
};