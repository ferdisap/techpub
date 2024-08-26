<?php

namespace App\Listeners\Csdb;

use App\Events\Csdb\DdnCreated;
use App\Mail\Csdb\DataDispatchNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendDdnNotification
{
  /**
   * Create the event listener.
   */
  public function __construct()
  {
  }

  /**
   * Handle the event.
   */
  public function handle(DdnCreated $event): void
  {
    Mail::queue(new DataDispatchNote($event->DDNOBJECTModel));
    // Push notification here to table Notification if needed
  }
}
