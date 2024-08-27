<?php

namespace App\Console\Commands;

use Database\Seeders\CodeSeeder;
use Illuminate\Console\Command;

class SeedingDB extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'db:seed-default';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    // Seeding Code
    $codeSeeder = new CodeSeeder();
    $codeSeeder->run();
  }
}
