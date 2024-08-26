<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * enterprise bisa berupa company, manufacturer, authority, else.
 */
return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('enterprises', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->integer('code_id')->unique();
      $table->json('address');
      $table->json('remarks')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('enterprises');
  }
};
