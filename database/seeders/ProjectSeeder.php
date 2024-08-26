<?php

namespace Database\Seeders;

use App\Http\Controllers\Controller;
use App\Models\Csdb;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Project::create([
      'name' => 'MALE',
      'description' => '',
    ]);
  }
}
