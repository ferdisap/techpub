<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Enterprise;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnterpriseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // \App\Models\User::factory(10)->create();

    // Schema::connection('sqlite')->dropIfExists('enterprises');

    // Schema::connection('sqlite')->create('enterprises', function (Blueprint $table) {
    //   $table->id();
    //   $table->string('name');
    //   $table->string('code');
    //   $table->json('address');
    //   $table->json('remarks')->nullable();
    // });

    // Schema::dropIfExists('enterprises');
    // Schema::create('enterprises', function (Blueprint $table) {
    //   $table->id();
    //   $table->string('name');
    //   $table->string('code')->unique();
    //   $table->json('address');
    //   $table->json('remarks')->nullable();
    // });

    try {
      Enterprise::create([
        'name' => 'PT Dirgantara Indonesia',
        // 'code' => '0001Z',
        'code_id' => 1,
        'address' => json_encode([
          "city" => 'Bandung',
          "country" => "Indonesia",
          'department' => '',
          'street' => '',
          'postOfficeBox' => '',
          'postalZipCode' => '',
          'state' => '',
          'province' => '',
          'building' => '',
          'room' => '',
          'phoneNumber' => [],
          'faxNumber' => [],
          'email' => [],
          'internet' => [],
          'SITA' => '',
        ]),
      ]);
    } catch (\Throwable $e) {
      # code...
    }

    \App\Models\Enterprise::factory()->count(3)->create();
  }

  /**
   * function seed dipakai untuk production juga
   */
  public static function seed(
    string $name, int $code_id, array $address = []
  )
  {
    return Enterprise::create([
      'name' => $name,
      'code_id' => $code_id,
      'address' => json_encode([
        "city" => $address['city'] ?? 'default-city',
        "country" => $address['country'] ?? "default-country",
        'department' => '',
        'street' => '',
        'postOfficeBox' => '',
        'postalZipCode' => '',
        'state' => '',
        'province' => '',
        'building' => '',
        'room' => '',
        'phoneNumber' => [],
        'faxNumber' => [],
        'email' => [],
        'internet' => [],
        'SITA' => '',
      ]),
    ]);
  }
}
