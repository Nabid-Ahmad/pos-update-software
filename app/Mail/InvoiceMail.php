<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $sale;
    public $company;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sale, $company)
    {
        $this->sale = $sale;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('emails.invoice-pdf', [
            'sale' => $this->sale,
            'company' => $this->company
        ]);

        return $this->subject('Invoice #' . $this->sale->sale_invoice_id . ' from ' . $this->company->name)
                    ->view('emails.invoice')
                    ->attachData($pdf->output(), 'Invoice_' . $this->sale->sale_invoice_id . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
