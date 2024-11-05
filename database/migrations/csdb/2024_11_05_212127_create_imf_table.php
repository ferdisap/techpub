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
    Schema::dropIfExists('imf');
    Schema::create('imf', function (Blueprint $table) {
      $table->id();
      $table->string('csdb_id');
      
      $table->tinyText('imfIdentIcn');
      $table->string('icnTitle');
      $table->json('legacyIdents')->nullable(); //[{legacyOrigin: $value},{}] ref legacyIdentGroup
      $table->json('icnKeywords')->nullable(); //['foo','bar'] ref icnKeywordGroup

      $table->tinyText('year')->nullable();
      $table->tinyText('month')->nullable();
      $table->tinyText('day')->nullable();
        
      $table->string('securityClassification');
      $table->tinyText('responsiblePartnerCompany'); // merujuk ke responsiblePartnerCompany, bisa code atau textnya jika ada
      $table->string('brexDmRef')->nullable(); // merujuk filename brex yang sama dengan table csdb
      $table->text('qa'); // isi last QA: 'unverified', 'first-...', 'second-...'
      $table->text('remarks')->nullable();

      $table->json('json');
      $table->longText('xml');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('imf');
  }
};
