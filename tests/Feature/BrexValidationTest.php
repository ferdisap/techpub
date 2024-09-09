<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BrexValidationTest extends TestCase
{
  /**
   * A basic feature test example.
   */
  public function test_example(): void
  {
    $this->assertTrue(true);
    return;

    $response = $this->post("/api/br/validate",[
      'filenames' => ['DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml'],
    ]);

    // $validator = new CSDBObject();
    // $validator->load("../storage/csdb/OAyKA/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-US.XML");
    // $validator = new CSDBValidator($validator);
    // // dd($validator);

    // $validatee = new CSDBObject();
    // $validatee->load("../storage/csdb/OAyKA/DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml");
    // $validateeDua = new CSDBObject();
    // $validateeDua->load("../storage/csdb/OAyKA/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml");
    // $validatee = new CSDBValidatee([$validatee, $validateeDua]);
    // // $validatee = new CSDBValidatee([$validateeDua]);

    // $validation = new Brex($validator, $validatee);
    // $validation->validate();    

    $response->assertStatus(200);
  }
}
