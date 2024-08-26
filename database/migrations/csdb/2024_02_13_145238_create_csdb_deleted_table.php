<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // $file = new Filesystem;
    // $file->cleanDirectory('storage/csdb_deleted');
    // Schema::create('csdb_deleted', function (Blueprint $table) {
    //   $table->id();
    //   $table->string('filename');
    //   $table->integer('deleter_id');
    //   $table->json('meta');
    //   $table->timestampTz('created_at', 7);
    // });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Schema::dropIfExists('csdb_deleted');
    // $file = new Filesystem;
    // $file->cleanDirectory('storage/csdb_deleted');
  }
};
