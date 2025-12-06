<?php

namespace App\Helpers;

use Exception;

class PdfHelper
{
    public static function generateBookingPdf($booking)
    {
        try {
            // Include DomPDF manually
            require_once app_path('Libraries/dompdf/autoload.inc.php');
            
            // Reference the Dompdf namespace
            $dompdf = new \Dompdf\Dompdf();
            
            // Generate HTML content
            $html = view('booking.pdf', compact('booking'))->render();
            
            // Load HTML content
            $dompdf->loadHtml($html);
            
            // Set paper size and orientation
            $dompdf->setPaper('A4', 'portrait');
            
            // Render PDF
            $dompdf->render();
            
            return $dompdf;
            
        } catch (Exception $e) {
            throw new Exception('PDF generation failed: ' . $e->getMessage());
        }
    }
}