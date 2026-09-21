<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('distribution_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('distribution_id')->constrained()->cascadeOnDelete();
      $table->unsignedBigInteger('purchase_order_id')->nullable();
      $table->integer('item_index')->nullable();
      $table->string('item_name');
      $table->decimal('unit_cost', 12, 2)->default(0);
      $table->integer('quantity');
      $table->string('storage_location')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('distribution_items');
  }
};
