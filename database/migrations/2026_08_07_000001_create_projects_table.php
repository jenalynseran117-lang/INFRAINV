<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    Schema::create('projects', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('location')->nullable();
      $table->decimal('budget', 14, 2)->nullable(); // target value, basis ng Progress %
      $table->string('status')->default('active');  // active, completed, on-hold
      $table->unsignedBigInteger('created_by')->nullable();
      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('projects');
  }
};
