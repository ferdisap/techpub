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
    Schema::dropIfExists('ddn');
    Schema::create('ddn', function (Blueprint $table) {
      $table->id();
      $table->string('csdb_id');
      
      $table->tinyText('modelIdentCode'); // merujuk ke @modelIdentCode
      $table->tinyText('senderIdent'); // merujuk ke senderIdent code atau sudah di transform codenya, gunakan file config jika ingin transform
      $table->tinyText('receiverIdent'); // merujuk ke receiver code atau sudah di transform codenya, gunakan file config jika ingin transform
      $table->tinyText('yearOfDataIssue'); // merujuk ke @yearOfDataIssue
      $table->tinyText('seqNumber'); // merujuk ke @seqNumber

      $table->tinyText('year');
      $table->tinyText('month');
      $table->tinyText('day');
        
      $table->bigInteger('dispatchTo_id');
      $table->bigInteger('dispatchFrom_id');
      
      $table->string('securityClassification');
      $table->string('brexDmRef'); // merujuk filename brex yang sama dengan table csdb
      $table->text('authorization'); //merujuk ke ddnStatus/authorization
      $table->text('remarks')->nullable(); //merujuk ke ddnStatus/remarks
      /**
       * merujuk ke dmlEntry. cara tulis: 
       * { "deliveryList: [filename1, filename2, filename3, ...], "mediaIdent" : {$label} }
       * jika tidak ada, isi dengan null
       */
      $table->json('ddnContent')->nullable(); 

      $table->json('json');
      $table->longText('xml');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('ddn');
  }
};
