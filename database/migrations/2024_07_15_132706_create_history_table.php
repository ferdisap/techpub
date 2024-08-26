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
    Schema::create('history', function (Blueprint $table) {
      $table->id();
      $table->string('code');
      $table->string('description')->nullable();
      $table->string('owner_id');
      $table->string('owner_class');
      $table->timestamp('created_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('history');
  }
};
