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
    Schema::connection('sqlite_ietm')->create('repo', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->string('path');
      $table->string('project_name');
      $table->string('token');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::connection('sqlite_ietm')->dropIfExists('repo');
  }
};
