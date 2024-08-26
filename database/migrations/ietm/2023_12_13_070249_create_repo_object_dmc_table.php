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
    Schema::connection('sqlite_ietm')->create('repo_object_dmc', function (Blueprint $table) {
      $table->id();
      $table->string('repo_id');
      $table->string('filename');
      $table->string('title');
      $table->string('issuedate');
      $table->string('schema');
      $table->string('sc');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::connection('sqlite_ietm')->dropIfExists('repo_object_dmc');
  }
};
