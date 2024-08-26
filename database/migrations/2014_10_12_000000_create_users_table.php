<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * first_name, middle_name, last_name mengikuti S1000D 5.0
 */
return new class extends Migration
{
  /**
   * Run the migrations.
   * table last_name tidak nullable karena jika dilihat di S1000D 5.0 schema commentXsd
   */
  public function up(): void
  {
    // Schema::connection('sqlite')->create('users', function (Blueprint $table) {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('first_name')->nullable();
      $table->string('middle_name')->nullable();
      $table->string('last_name');
      $table->string('job_title');
      $table->integer('work_enterprise_id')->nullable();
      $table->string('email')->unique();
      $table->timestamp('email_verified_at')->nullable();
      $table->string('password');
      $table->string('storage');
      $table->json('address')->nullable();
      $table->rememberToken();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Schema::connection('users_sqlite')->dropIfExists('users');
    // Schema::connection('sqlite')->dropIfExists('users');
    Schema::dropIfExists('users');
  }
};
