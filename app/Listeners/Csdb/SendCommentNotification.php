<?php

namespace App\Listeners\Csdb;

use App\Events\Csdb\CommentCreated;
use App\Mail\Csdb\CommentCreated as CsdbCommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendCommentNotification
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
  public function handle(CommentCreated $event): void
  {
    Mail::queue(new CsdbCommentCreated($event->COMMENTOBJECTModel));
    // Push notification here to table Notification if needed
  }
}
