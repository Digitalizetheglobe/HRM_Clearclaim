<?php

namespace App\Exports;

use App\Models\BookingForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class BookingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function title(): string
    {
        return 'Bookings Export';
    }

    public function headings(): array
    {
        return [
            'Booking ID',
            'Booking Date',
            'Project Name',
            'Unit Name',
            'Primary Applicant Name',
            'Primary Applicant Contact No',
            'Status',
            'Agreement Status',
            'Remaining Amount'
        ];
    }

    public function map($booking): array
    {
        // Determine status
        $status = 'Active';
        if ($booking->is_cancelled == 1) {
            $status = 'Cancelled';
        } elseif ($booking->remaining <= 0) {
            $status = 'Completed';
        }

        return [
            $booking->id,
            $booking->booking_date,
            $booking->project->project_name ?? 'N/A',
            $booking->unit->unit_name ?? 'N/A',
            $booking->primary_applicant_name,
            $booking->primary_applicant_contact_no,
            $status,
            $booking->agreement ?? 'N/A',
            $booking->remaining ?? 0
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }
}