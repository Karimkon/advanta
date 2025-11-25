<?php

namespace App\Services;

use App\Models\Lpo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class PdfService
{
   public function generateLpoPdf(Lpo $lpo)
{
    try {
        Log::info('Starting PDF generation for LPO', ['lpo_id' => $lpo->id]);

        // Ensure the LPO has all required relationships loaded
        $lpo->load([
            'requisition.project',
            'supplier', 
            'items',
            'preparer'
        ]);

        // Generate PDF with optimized settings for email
        $pdf = PDF::loadView('pdf.lpo', compact('lpo'))
                 ->setPaper('a4', 'portrait')
                 ->setOptions([
                     'defaultFont' => 'helvetica',
                     'isHtml5ParserEnabled' => true,
                     'isRemoteEnabled' => false, // Disable for email
                     'dpi' => 96,
                     'isFontSubsettingEnabled' => true,
                     'isPhpEnabled' => false,
                     'debugCss' => false,
                     'debugLayout' => false,
                     'enable_html5_parser' => true,
                 ]);

        // Generate filename
        $filename = 'LPO_' . $lpo->lpo_number . '_' . now()->format('Ymd') . '.pdf';
        $path = 'lpos/' . $filename;
        
        // Save PDF
        $pdfContent = $pdf->output();
        Storage::disk('local')->put($path, $pdfContent);

        Log::info('PDF generated successfully', [
            'lpo_id' => $lpo->id,
            'path' => $path,
            'size' => strlen($pdfContent)
        ]);

        return [
            'success' => true,
            'path' => $path,
            'filename' => $filename,
            'content' => $pdfContent
        ];
        
    } catch (Exception $e) {
        Log::error('PDF Generation Error: ' . $e->getMessage(), [
            'lpo_id' => $lpo->id,
            'trace' => $e->getTraceAsString()
        ]);
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

    public function getLpoPdfContent(Lpo $lpo)
    {
        try {
            Log::info('Generating PDF content for LPO', ['lpo_id' => $lpo->id]);

            // Ensure relationships are loaded
            $lpo->load([
                'requisition.project',
                'supplier', 
                'items'
            ]);

            $pdf = PDF::loadView('pdf.lpo', compact('lpo'))
                     ->setPaper('a4', 'portrait')
                     ->setOptions([
                         'defaultFont' => 'sans-serif',
                         'isHtml5ParserEnabled' => true,
                         'isRemoteEnabled' => true,
                         'chroot' => public_path(),
                         'dpi' => 96,
                     ]);

            $content = $pdf->output();
            
            Log::info('PDF content generated successfully', [
                'lpo_id' => $lpo->id,
                'pdf_size' => strlen($content)
            ]);

            return $content;

        } catch (Exception $e) {
            Log::error('Failed to generate PDF content: ' . $e->getMessage(), [
                'lpo_id' => $lpo->id,
                'error' => $e->getTraceAsString()
            ]);
            
            // Return a simple fallback PDF
            return $this->generateFallbackPdf($lpo);
        }
    }

    private function generateFallbackPdf(Lpo $lpo)
    {
        try {
            $simpleHtml = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='utf-8'>
                    <title>LPO {$lpo->lpo_number}</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.4; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .section { margin-bottom: 20px; }
                        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f5f5f5; }
                    </style>
                </head>
                <body>
                    <div class='header'>
                        <h1>ADVANTA UGANDA LIMITED</h1>
                        <h2>LOCAL PURCHASE ORDER</h2>
                        <h3>{$lpo->lpo_number}</h3>
                    </div>
                    
                    <div class='section'>
                        <p><strong>Supplier:</strong> {$lpo->supplier->name}</p>
                        <p><strong>Date:</strong> " . $lpo->created_at->format('M d, Y') . "</p>
                        <p><strong>Delivery Date:</strong> " . $lpo->delivery_date->format('M d, Y') . "</p>
                        <p><strong>Total Amount:</strong> UGX " . number_format($lpo->total, 2) . "</p>
                    </div>
                    
                    <div class='section'>
                        <p><strong>Items:</strong></p>
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>";

            foreach ($lpo->items as $item) {
                $simpleHtml .= "
                                <tr>
                                    <td>{$item->description}</td>
                                    <td>" . number_format($item->quantity, 3) . " {$item->unit}</td>
                                    <td>UGX " . number_format($item->unit_price, 2) . "</td>
                                    <td>UGX " . number_format($item->total_price, 2) . "</td>
                                </tr>";
            }

            $simpleHtml .= "
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan='3'><strong>Grand Total:</strong></td>
                                    <td><strong>UGX " . number_format($lpo->total, 2) . "</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class='section'>
                        <p><em>Note: This is a simplified version. Full LPO details are available in the email body.</em></p>
                    </div>
                </body>
                </html>";

            return PDF::loadHTML($simpleHtml)
                     ->setPaper('a4', 'portrait')
                     ->setOptions(['defaultFont' => 'sans-serif'])
                     ->output();

        } catch (Exception $e) {
            Log::error('Even fallback PDF failed: ' . $e->getMessage());
            
            // Return absolutely minimal PDF as last resort
            $minimalHtml = "
                <html>
                <body>
                    <h1>LPO: {$lpo->lpo_number}</h1>
                    <p>Supplier: {$lpo->supplier->name}</p>
                    <p>Total: UGX " . number_format($lpo->total, 2) . "</p>
                    <p>Please refer to email for full details.</p>
                </body>
                </html>
            ";
            
            return PDF::loadHTML($minimalHtml)
                     ->setPaper('a4', 'portrait')
                     ->output();
        }
    }

    /**
     * Clean up old PDF files (optional maintenance method)
     */
    public function cleanupOldPdfs($days = 1)
    {
        try {
            $files = Storage::disk('local')->files('lpos');
            $deletedCount = 0;
            
            foreach ($files as $file) {
                $lastModified = Storage::disk('local')->lastModified($file);
                if (time() - $lastModified > ($days * 24 * 60 * 60)) {
                    Storage::disk('local')->delete($file);
                    $deletedCount++;
                }
            }
            
            Log::info("Cleaned up {$deletedCount} old PDF files");
            return $deletedCount;
            
        } catch (Exception $e) {
            Log::error('PDF cleanup error: ' . $e->getMessage());
            return 0;
        }
    }
}