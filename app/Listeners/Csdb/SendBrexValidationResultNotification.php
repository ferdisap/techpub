<?php

namespace App\Listeners\Csdb;

use App\Events\Csdb\ValidatedByBrex;
use App\Mail\Csdb\BrexValidationResult;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendBrexValidationResultNotification
{
  /**
   * Create the event listener.
   */
  public function __construct()
  {
    //
  }

  /**
   * Handle the event.
   */
  public function handle(ValidatedByBrex $event): void
  {
    Mail::queue(new BrexValidationResult($event->initiator, $event->validator));
  }
}
