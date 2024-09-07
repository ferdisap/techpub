<?php

namespace App\Providers;

use App\Events\Csdb\CommentCreated;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Listeners\Csdb\SendDdnNotification;
use App\Events\Csdb\DdnCreated;
use App\Events\Csdb\ValidatedByBrex;
use App\Listeners\Csdb\SendBrexValidationResultNotification;
use App\Listeners\Csdb\SendCommentNotification;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event to listener mappings for the application.
   *
   * @var array<class-string, array<int, class-string>>
   */
  protected $listen = [
    Registered::class => [
      SendEmailVerificationNotification::class,
    ],
    DdnCreated::class => [
      SendDdnNotification::class,
    ],
    CommentCreated::class => [
      SendCommentNotification::class,
    ],
    ValidatedByBrex::class => [
      SendBrexValidationResultNotification::class,
    ]
  ];

  /**
   * Register any events for your application.
   */
  public function boot(): void
  {    
  }

  /**
   * Determine if events and listeners should be automatically discovered.
   */
  public function shouldDiscoverEvents(): bool
  {
    return false;
  }
}
