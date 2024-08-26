<?php

namespace Database\Seeders;

use App\Http\Controllers\Controller;
use App\Models\Csdb;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CsdbSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Schema::connection('sqlite')->dropIfExists('csdb');
    // $lists = Controller::get_file(storage_path('app/csdb/MALE'));
    // $lists = (array_filter($lists, fn ($v) => str_contains($v, '.')));
    // foreach ($lists as $obj) {
    //   if (!Csdb::where('path', "csdb/{$obj}")->latest('updated_at')->first('id')) {
    //     $model = Csdb::create([
    //       'filename' => $obj,
    //       'path' => "csdb/MALE",
    //       'status' => 'new',
    //       'description' => '',
    //       'initiator_id' => 1,
    //       'project_name' => 'MALE',
    //     ]);
    //     $model->setRemarks('title');
    //   }
    // }

    // $icn = Csdb::where('filename', 'like', 'ICN%')->get();
    // foreach($icn as $file){
    //   $file->setRemarks('stage', 'unstaged');
    // }
    // for ($i = 1000; $i < 10000; $i++) {

    // $fn = function($seqNumber){
    //   $seqNumber = str_pad($seqNumber, 3, '0', STR_PAD_LEFT);
    //   return "DMC-MALE-A-00-00-00-00A-00PA-D_$seqNumber-01_EN-EN.xml";      
    // };

    // try {
    //   Csdb::create([
    //     'filename' => "DMC-MALE-A-00-00-00-00A-001A-A_000-02_EN-EN.xml",
    //     'path' => 'csdb',
    //     'editable' => rand(0,1),
    //     'initiator_id' => 1,
    //     'remarks' => json_encode([
    //       'aaaaaaaaaaa' => 'foooooooo',
    //       'bbbb' => 'foooooooo',
    //       'cccc' => 'foooooooo',
    //     ]),
    //   ]);
    // } catch (\Throwable $e) {
    // }

    // for ($i = 1; $i < 100; $i++) {
    //   if($i < 100 ){
    //     $path = "csdb/";
    //   }
    //   else if($i < 200 ){
    //     $path = "csdb/n219/";
    //   }
    //   else if($i < 300){
    //     $path = "csdb/male/";
    //   }
    //   else if($i < 400){
    //     $path = "csdb/n219/amm/";
    //   }
    //   else if($i < 500){
    //     $path = "csdb/male/amm/";
    //   }
    //   else {
    //     $path = "csdb/";
    //   }
    //   try {
    //     Csdb::create([
    //       'filename' => "DMC-{$i}_foo.xml",
    //       'path' => $path,
    //       'editable' => rand(0,1),
    //       'initiator_id' => 1,
    //       'remarks' => json_encode([
    //         'aaaaaaaaaaa' => 'foooooooo',
    //         'bbbb' => 'foooooooo',
    //         'cccc' => 'foooooooo',
    //       ]),
    //     ]);
    //   } catch (\Throwable $e) {
    //     # code...
    //   }
    // }

    // Schema::connection('sqlite')->create('csdb', function (Blueprint $table) {
    //   $table->id();
    //   $table->string('filename')->unique();
    //   $table->string('path');
    //   $table->integer('initiator_id');
    //   $table->integer('deleter_id');
    //   $table->timestampsTz(7);
    // });

    Csdb::create([
      'filename' => "DMC-MALE-A-00-00-00-00A-001A-A_000-02_EN-US.xml",
      'path' => 'csdb',
      'initiator_id' => 1,
      // 'deleter_id' => 1,
    ]);
  }
}
