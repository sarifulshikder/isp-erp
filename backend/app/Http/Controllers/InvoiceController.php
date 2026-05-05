<?php
namespace App\Http\Controllers;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
class InvoiceController extends Controller {
    public function index(Request $request) {
        $query = Invoice::with('customer', 'package');
        if ($request->status) $query->where('status', $request->status);
        if ($request->customer_id) $query->where('customer_id', $request->customer_id);
        return response()->json($query->latest()->paginate(20));
    }
    public function store(Request $request) {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'package_id' => 'required|exists:packages,id',
            'amount' => 'required|numeric',
            'due_date' => 'required|date',
        ]);
        $data = $request->all();
        $data['invoice_no'] = 'INV-'.date('Ymd').'-'.rand(1000,9999);
        $data['issue_date'] = now()->toDateString();
        $data['discount'] = $request->discount ?? 0;
        $data['total'] = $data['amount'] - $data['discount'];
        $invoice = Invoice::create($data);
        return response()->json(['status' => 'success', 'data' => $invoice->load('customer', 'package')], 201);
    }
    public function show(Invoice $invoice) {
        return response()->json($invoice->load('customer', 'package', 'payments'));
    }
    public function update(Request $request, Invoice $invoice) {
        $invoice->update($request->all());
        return response()->json(['status' => 'success', 'data' => $invoice]);
    }
    public function destroy(Invoice $invoice) {
        $invoice->delete();
        return response()->json(['status' => 'success', 'message' => 'Invoice deleted']);
    }
}
