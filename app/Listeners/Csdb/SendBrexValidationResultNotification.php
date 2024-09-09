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
   * since Brex cannot serialized by Illuminate\Queue\Queue:160 @createObjectPayload jadi diganti pakai Array
   * Handle the event.
   */
  public function handle(ValidatedByBrex $event): void
  {
    // Mail::queue(new BrexValidationResult($event->initiator, $event->validator));
    Mail::queue(new BrexValidationResult($event->initiator, $event->validator->result()));
  }
}
