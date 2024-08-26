<?php

namespace App\Jobs\Csdb;

use App\Events\Csdb\CommentCreated;
use App\Events\Csdb\DdnCreated;
use App\Mail\Csdb\DataDispatchNote;
use App\Models\Csdb;
use App\Models\Csdb\Comment;
use App\Models\Csdb\Ddn;
use App\Models\Csdb\Dmc;
use App\Models\Csdb\Dml;
use App\Models\Csdb\Pmc;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class FillObjectTable implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   */
  public function __construct(public User $initiator, public Csdb $CSDBModel, public bool $mailTo)
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(): void
  {
    $previousStatic_storage_user_id = Csdb::$storage_user_id;
    Csdb::$storage_user_id = $this->initiator->id;
    $this->CSDBModel->loadCSDBObject();
    if ($this->CSDBModel->CSDBObject->document instanceof \DOMDocument) {
      $doctype = $this->CSDBModel->CSDBObject->document->doctype->nodeName;
      $csdbobject = false;
      switch ($doctype) {
        case 'dmodule':
          $csdbobject = Dmc::fillTable($this->CSDBModel->id, $this->CSDBModel->CSDBObject);
          break;
        case 'pm':
          $csdbobject = Pmc::fillTable($this->CSDBModel->id, $this->CSDBModel->CSDBObject);
          break;
        case 'dml':
          $csdbobject = Dml::fillTable($this->CSDBModel->id, $this->CSDBModel->CSDBObject);
          break;
        case 'ddn':
          $csdbobject = Ddn::fillTable($this->CSDBModel->id, $this->CSDBModel->CSDBObject);          
          if($csdbobject && $this->mailTo) event(new DdnCreated($csdbobject));
          break;
        case 'comment':
          $csdbobject = Comment::fillTable($this->CSDBModel->id, $this->CSDBModel->CSDBObject);
          if($csdbobject && $this->mailTo) event(new CommentCreated($csdbobject));
          break;
      }
    }
    Csdb::$storage_user_id = $previousStatic_storage_user_id;
  }
}
