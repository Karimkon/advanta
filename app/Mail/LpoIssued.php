<?php

namespace App\Mail;

use App\Models\Lpo;
use App\Services\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LpoIssued extends Mailable
{
    use Queueable, SerializesModels;

    public $lpo;
    protected $pdfService;

    public function __construct(Lpo $lpo)
    {
        $this->lpo = $lpo;
        $this->pdfService = new PdfService();
    }

    public function build()
    {
        Log::info('Starting LPO email build process', ['lpo_id' => $this->lpo->id]);

        try {
            // Generate PDF content directly (no file saving)
            $pdfContent = $this->pdfService->getLpoPdfContent($this->lpo);
            $filename = 'LPO_' . $this->lpo->lpo_number . '.pdf';

            Log::info('Attaching PDF data directly', [
                'lpo_id' => $this->lpo->id,
                'pdf_size' => strlen($pdfContent)
            ]);

            return $this->subject('Local Purchase Order: ' . $this->lpo->lpo_number . ' - Advanta Uganda Limited')
                        ->view('emails.lpo_issued')
                        ->with(['lpo' => $this->lpo])
                        ->attachData($pdfContent, $filename, [
                            'mime' => 'application/pdf',
                        ]);

        } catch (\Exception $e) {
            Log::error('Failed to send LPO email with PDF: ' . $e->getMessage(), [
                'lpo_id' => $this->lpo->id,
                'error' => $e->getTraceAsString()
            ]);
            
            // Fallback: Send email without attachment
            return $this->subject('Local Purchase Order: ' . $this->lpo->lpo_number . ' - Advanta Uganda Limited')
                        ->view('emails.lpo_issued')
                        ->with(['lpo' => $this->lpo]);
        }
    }
}