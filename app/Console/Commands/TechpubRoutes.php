<?php

namespace App\Console\Commands;

use App\Http\Controllers\Controller;
use Illuminate\Console\Command;

class TechpubRoutes extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'techpubroutes:write';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create JSON contained routes named that will be used for Javascript Frontend';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $allRoutes = Controller::getAllRoutesNamed();
    // write routes
    $path = resource_path('others'.DIRECTORY_SEPARATOR.'routes.json');
    $file = file_put_contents($path, json_encode($allRoutes));
    if($file){
      echo "\r\n INFO Console command [$path] created sucessfully \r\n";
    } else {
      echo "failed to cache";
    }
  }
}
