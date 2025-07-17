<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Optional: for queueing notifications
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceUnlockRequested extends Notification // You can implement ShouldQueue if needed
{
    use Queueable;

    public Invoice $invoice;
    public User $requester;

    public function __construct(Invoice $invoice, User $requester)
    {
        $this->invoice = $invoice;
        $this->requester = $requester;
    }

    public function via(object $notifiable): array
    {
        // Define channels: database for in-app, mail for email
        $channels = ['database'];
        if (config('mail.driver') && config('mail.from.address')) { // Only add mail if mail is configured
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('invoices.manage_unlock_request_form', ['invoice' => $this->invoice->id]);

        return (new MailMessage)
                    ->subject('Invoice Edit Unlock Requested: #' . $this->invoice->invoice_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->requester->name . ' has requested to unlock Invoice #' . $this->invoice->invoice_number . ' for editing.')
                    ->line('Reason provided: ' . $this->invoice->unlock_reason)
                    ->action('Manage Request', $url)
                    ->line('Thank you for your attention.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'requester_name' => $this->requester->name,
            'requester_id' => $this->requester->id,
            'message' => $this->requester->name . ' requested unlock for Invoice #' . $this->invoice->invoice_number,
            'action_url' => route('invoices.manage_unlock_request_form', ['invoice' => $this->invoice->id]), // Pass invoice model or ID
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    // public function toArray(object $notifiable): array // For broadcasting or other channels
    // {
    //     return [
    //         'invoice_id' => $this->invoice->id,
    //         'message' => $this->requester->name . ' requested unlock for Invoice #' . $this->invoice->invoice_number,
    //     ];
    // }
}