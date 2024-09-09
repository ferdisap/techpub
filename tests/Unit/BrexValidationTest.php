<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;

use App\Events\Csdb\ValidatedByBrex;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Ptdi\Mpub\Main\CSDBObject;
use Ptdi\Mpub\Validation\CSDBValidatee;
use Ptdi\Mpub\Validation\CSDBValidator;
use Ptdi\Mpub\Validation\Validator\Brex;
use Tests\TestCase;

class BrexValidationTest extends TestCase
{
  public static CSDBValidator $validator;
  public static CSDBValidatee $validatee;
  public static Brex $brexValidator;

  public function test_construct()
  {
    $validator = new CSDBObject();
    $validator->load(storage_path('test/csdb/DMC-S1000D-G-04-10-0301-00A-022A-D_001-00_EN-US.XML'));
    self::$validator = new CSDBValidator($validator);    

    $validatee = new CSDBObject();
    $validatee->load(storage_path("test/csdb/DMC-MALE-A-15-00-01-00A-018A-A_000-01_EN-EN.xml"));
    $validateeDua = new CSDBObject();
    $validateeDua->load(storage_path("test/csdb/DMC-MALE-A-16-00-01-00A-018A-A_000-01_EN-EN.xml"));
    self::$validatee = new CSDBValidatee([$validatee, $validateeDua]);
    // $validatee = new CSDBValidatee([$validateeDua]);

    if(!(self::$brexValidator = new Brex(self::$validator, self::$validatee))) {
      $this->assertFalse(false);
      return;
    }

    ((!empty(self::$validator) && !empty(self::$validatee))) ? $this->assertTrue(true) : $this->assertFalse(true);
  }

  /**
   * A basic unit test example.
   */
  public function test_validate(): void
  {
    $validation = self::$brexValidator;
    $validation->validate();
    if (count($validation->result()) == 2) {
      $this->assertTrue(true);
    } else {
      $this->assertFalse(true);
    }
  }

  public function test_send_notification():void
  {
    Event::fake();
    $user = \App\Models\User::factory()->create();
    Event::assertDispatched(ValidatedByBrex::class,$user, self::$brexValidator);
  }
}
