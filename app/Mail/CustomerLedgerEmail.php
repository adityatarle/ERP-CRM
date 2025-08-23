<?php

namespace App\Mail;

use App\Models\Customer;
use App\Exports\CustomerLedgerExport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class CustomerLedgerEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Account Statement from [Your Company Name]',
        );
    }

    public function content(): Content
    {
        // You can create a simple blade view for the email body
        return new Content(
            view: 'emails.customer.ledger',
        );
    }

    public function attachments(): array
    {
        $fileName = 'Statement-' . $this->customer->name . '-' . now()->format('Y-m-d') . '.xlsx';

        return [
            Attachment::fromData(fn () => Excel::raw(new CustomerLedgerExport($this->customer), \Maatwebsite\Excel\Excel::XLSX), $fileName)
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}