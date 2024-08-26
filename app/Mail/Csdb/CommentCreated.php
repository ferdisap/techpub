<?php

namespace App\Mail\Csdb;

use App\Models\Csdb;
use App\Models\Csdb\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

class CommentCreated extends Mailable
{
  use Queueable, SerializesModels;

  public array $toPerson = [];
  public Address $fromPerson;

  /**
   * Create a new message instance.
   */
  public function __construct(public Comment $COMMENTModel)
  {
    $this->fromPerson = new Address($COMMENTModel->csdb->owner->email, $COMMENTModel->csdb->owner->name());
    $CSDBModels = new Csdb();
    $str = '';
    foreach ($COMMENTModel->commentRefs as $refs){
      $str .= "OR (filename = '{$refs}') ";
    }
    $str = substr($str,3);
    $CSDBModels = $CSDBModels->whereRaw($str);
    foreach($CSDBModels->get(['id', 'storage_id']) as $csdb){
      $this->toPerson[] = new Address($csdb->owner->email, $csdb->owner->name());
    }
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    if(isset($this->fromPerson)){
      return new Envelope(
        subject: 'Comment Created',
        to: $this->toPerson,
        from: $this->fromPerson,
      );
    }
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'mail.comment',
      with: [
        'COMMENTModel' => $this->COMMENTModel
      ],
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array
  {
    return [];
  }
}
