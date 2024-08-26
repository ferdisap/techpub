<?php

namespace App\Mail\Csdb;

use App\Models\Csdb;
use App\Models\Csdb\Ddn;
use App\Models\Enterprise;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Ptdi\Mpub\Main\CSDBStatic;

class DataDispatchNote extends Mailable
{
  use Queueable, SerializesModels;

  public static bool $mailToPerson = true;
  public static bool $mailToEnterprise = true;

  public array $toPerson = [];
  public Address $fromPerson;
  
  public array $toEnterprise = [];

  /**
   * Create a new message instance.
   */
  public function __construct(public Ddn $DDNModel)
  {
    if(self::$mailToPerson && ($DDNModel->dispatchTo && $DDNModel->dispatchFrom)){
      $this->toPerson[] = new Address($DDNModel->dispatchTo->email, $DDNModel->dispatchTo->name());
      $this->fromPerson = new Address($DDNModel->dispatchFrom->email, $DDNModel->dispatchFrom->name());
    }
    else self::$mailToPerson = false;
    
    if(self::$mailToEnterprise){
      $DDNModel->csdb->loadCSDBObject();
      $enterpriseName = $DDNModel->csdb->CSDBObject->evaluate("string(//dispatchTo/dispatchAddress/enterprise/enterpriseName)");
      $enterprise = Enterprise::where('name', $enterpriseName)->first('address');
      $enterpriseAddress = $enterprise->address;
      $enterpriseEmail = $enterpriseAddress['email'];
      foreach($enterpriseEmail as $email){
        $this->toEnterprise[] = new Address($email, $enterpriseName);
      }
    }
    else self::$mailToEnterprise = false;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    if(isset($this->fromPerson)){
      return new Envelope(
        subject: 'Data Dispatch Note',
        to: array_merge($this->toEnterprise, $this->toPerson),
        from: $this->fromPerson // tetapi value email akan terganti jika pakai smtp, ex smpt.gmail.com
      );
    }
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'mail.ddn',
      with: [
        'DDNModel' => $this->DDNModel,
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
