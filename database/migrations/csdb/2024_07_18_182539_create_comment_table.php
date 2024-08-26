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
    Schema::dropIfExists('comment');
    Schema::create('comment', function (Blueprint $table) {
      $table->id();
      
      $table->string('csdb_id');

      $table->tinyText('modelIdentCode'); // merujuk ke @modelIdentCode
      $table->tinyText('senderIdent'); // merujuk ke senderIdent code atau sudah di transform codenya, gunakan file config jika ingin transform
      $table->tinyText('commentType'); // merujuk ke commentType
      $table->tinyText('yearOfDataIssue'); // merujuk ke @yearOfDataIssue
      $table->tinyText('seqNumber'); // merujuk ke @seqNumber

      $table->tinyText('languageIsoCode');
      $table->tinyText('countryIsoCode');

      $table->text('commentTitle');
      $table->tinyText('year');
      $table->tinyText('month');
      $table->tinyText('day');

      $table->string('securityClassification'); // sementara merujuk ke security@securityClassification yang BELUM di terjemahkan codenya. Nanti jika fungsi di CSDBObject class sudah selesai, baru pakai yang SUDAH di terjemahkan
      $table->string('commentPriority'); // sementara merujuk ke commentPriority@commentPriorityCode yang BELUM di terjemahkan codenya. Nanti jika fungsi di CSDBObject class sudah selesai, baru pakai yang SUDAH di terjemahkan
      $table->string('commentResponse')->nullable(); // sementara merujuk ke commentResponse@responseType yang BELUM di terjemahkan codenya. Nanti jika fungsi di CSDBObject class sudah selesai, baru pakai yang SUDAH di terjemahkan
      $table->json('commentRefs'); // jika kosong harus di isi dengan Array
      $table->string('brexDmRef'); // merujuk filename brex yang sama dengan table csdb
      $table->text('remarks')->nullable(); //merujuk ke ddnStatus/remarks

      $table->longText('commentContent')->nullable();

      $table->json('json');
      $table->longText('xml');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('comment');
  }
};
