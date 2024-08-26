<?php

namespace Tests\Csdb;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Events\Csdb\DdnCreated;
use App\Models\Csdb;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExampleTest extends TestCase
{
  /**
   * A basic test example.
   */
  // public function test_the_application_returns_a_successful_response(): void
  // {
  //   $response = $this->get('/');

  //   $response->assertStatus(200);
  // }

  public function test_send_ddn_notification(): void
  {
    // Event::fake();

    DdnCreated::dispatch(Csdb::find(1));



    // Perform order shipping...

    // // Assert that an event was dispatched...
    // Event::assertDispatched(DdnCreated::class);
 
    // // Assert an event was dispatched twice...
    // Event::assertDispatched(DdnCreated::class, 2);

    // // Assert that no events were dispatched...
    // Event::assertNothingDispatched();
  }
}
