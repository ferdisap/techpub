<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class HistorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Schema::dropIfExists('history');
    Schema::create('history', function (Blueprint $table) {
      $table->id();
      $table->string('code');
      $table->string('description')->nullable();
      $table->string('owner_id');
      $table->string('owner_class');
      $table->timestamp('created_at');
    });
  }
}
