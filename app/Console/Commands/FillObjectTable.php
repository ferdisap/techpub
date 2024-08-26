<?php

namespace App\Console\Commands;

use App\Models\Csdb;
use App\Models\User;
use Illuminate\Console\Command;
use Ptdi\Mpub\Main\CSDBObject;

class FillObjectTable extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'csdb:fill-object-table {objectClass} {csdbId} {storage} {filename}';

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
    $objectClass = $this->argument('objectClass');
    $csdbId = $this->argument('csdbId');
    $storage = $this->argument('storage');
    $filename = $this->argument('filename');

    // load xml from disk
    $CSDBObject = new CSDBObject();
    $CSDBObject->load(CSDB_STORAGE_PATH. DIRECTORY_SEPARATOR . $storage . DIRECTORY_SEPARATOR . $filename);
    
    // set user id
    Csdb::$storage_user_id = User::where('storage', $storage)->first()->id;

    // execute fillTable
    if($CSDBObject->document && ($CSDBObject->document instanceof \DOMDocument)){
      if(call_user_func_array($objectClass.'::fillTable',[$csdbId, $CSDBObject])){
        echo 'success';
        return true;
      } else {
        echo 'fail';
        return false;
      }
    }
    echo 'does not support with document other than DOMDocument';
    return false;
  }
}
