<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfController extends Controller
{
    public function download(Invoice $invoice)
    {
        $invoice->load('customer', 'package', 'payments');
        $company = [
            'name' => Setting::get('company_name', 'My ISP'),
            'phone' => Setting::get('company_phone', ''),
            'email' => Setting::get('company_email', ''),
            'address' => Setting::get('company_address', ''),
        ];
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'company'));
        return $pdf->download('Invoice-' . $invoice->invoice_no . '.pdf');
    }
}
