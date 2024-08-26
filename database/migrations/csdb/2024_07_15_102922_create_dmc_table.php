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
    Schema::dropIfExists('dmc');
    Schema::create('dmc', function (Blueprint $table) {
      $table->id();
      $table->string('csdb_id');

      $table->tinyText('modelIdentCode');
      $table->tinyText('systemDiffCode');
      $table->tinyText('systemCode');
      $table->tinyText('subSystemCode');
      $table->tinyText('subSubSystemcode');
      $table->tinyText('assyCode');
      $table->tinyText('disassyCode');
      $table->tinyText('disassyCodeVariant');
      $table->tinyText('infoCode');
      $table->tinyText('infoCodeVariant');
      $table->tinyText('itemLocationCode');
      $table->tinyText('languageIsoCode');
      $table->tinyText('countryIsoCode');
      $table->tinyText('issueNumber');
      $table->tinyText('inWork');

      $table->tinyText('year');
      $table->tinyText('month');
      $table->tinyText('day');
      
      $table->tinyText('techName');
      $table->tinyText('infoName')->nullable();
      $table->tinyText('infoNameVariant')->nullable();      

      $table->string('securityClassification');
      $table->tinyText('responsiblePartnerCompany'); // merujuk ke responsiblePartnerCompany, bisa code atau textnya jika ada
      $table->tinyText('originator'); // merujuk ke originator, bisa code atau textnya jika ada
      $table->tinyText('applicability'); // merujuk ke originator, bisa code atau textnya jika ada
      $table->string('brexDmRef');
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
    Schema::dropIfExists('dmc');
  }
};
