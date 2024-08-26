<?php

namespace Database\Seeders;

use App\Models\Code;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CodeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Schema::dropIfExists('code');
    Schema::create('code', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->string('type')->nullable();
      $table->text('description')->nullable();
    });

    foreach($this->getCode() as $codeArray){
      Code::create([
        'name' => $codeArray[0],
        'description' => $codeArray[1],
        'type' => $codeArray[2],
      ]);
    }
  }

  public function getCode()
  {
    
    // PTDI CAGE Code
    $CAGECode = [
      "0001Z|Cage code for PTDI|ENTERPRISE-code"
    ];

    // CSDB history code
    $CSDBHistoryCode = [
      "CSDB-DELL|Delete CSDB Object|CSDB-code",
      "CSDB-PDEL|Permanent Delete CSDB Object|CSDB-code", // there is no storage disk and cannot restore
      "CSDB-CRBT|Create CSDB Object|CSDB-code",
      "CSDB-UPDT|Update CSDB Object|CSDB-code",
      "CSDB-RSTR|Restore CSDB Object|CSDB-code",
      "CSDB-PATH|Update path CSDB Object|CSDB-code",
      "CSDB-STRG|Update storage CSDB Object|CSDB-code",
      "CSDB-IMPT|CSDB is imported Object|CSDB-code",
    ];

    // User history code
    $USERHistoryCode = [
      "USER-DELL|User delete CSDB Object|USER-code",
      "USER-PDEL|User permanent delete CSDB Object|USER-code",
      "USER-CRBT|User create CSDB Object|USER-code",
      "USER-UPDT|User update CSDB Object|USER-code",
      "USER-RSTR|User restore CSDB Object|USER-code",
      "USER-PATH|User update path CSDB Object|USER-code",
      "USER-IMPT|User import CSDB Object|USER-code",
    ];

    // fakeCode
    $fakeCode = [];
    for ($i=0; $i < 10; $i++) { 
      $fakeCode[] = Str::random(5) ."|". fake()->text(100) . "|". fake()->text(10);
    }
    
    $arr = array_merge($CAGECode, $CSDBHistoryCode, $USERHistoryCode, $fakeCode);
    array_walk($arr, fn(&$v) => $v = explode("|", $v));
    return $arr;
  }
}
