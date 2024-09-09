<?php

namespace App\Mail\Csdb;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Ptdi\Mpub\Validation\Validator\Brex;

class BrexValidationResult extends Mailable
{
  use Queueable, SerializesModels;

  /**
   * since Brex cannot serialized by Illuminate\Queue\Queue:160 @createObjectPayload jadi diganti pakai Array
   * Create a new message instance.
   */
  // public function __construct(public User $initiator,  public Brex $validation)
  public function __construct(public User $initiator,  public Array $validationResult)
  {
    //
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Send Brex Validation Result',
      to: $this->initiator->email
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    $result = $this->validationResult;
    return new Content(
      view: 'mail.brexValidationResult',
      with: [
        // 'results' => $this->validation->result()
        'results' => $result
      ]
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
