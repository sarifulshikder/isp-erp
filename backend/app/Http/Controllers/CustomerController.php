<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class CustomerController extends Controller {
    public function index(Request $request) {
        $query = Customer::with('package');
        if ($request->status) $query->where('status', $request->status);
        if ($request->search) $query->where('name', 'like', '%'.$request->search.'%')
            ->orWhere('phone', 'like', '%'.$request->search.'%');
        return response()->json($query->latest()->paginate(20));
    }
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|unique:customers',
            'username' => 'required|unique:customers',
            'password' => 'required|min:6',
            'package_id' => 'required|exists:packages,id',
            'connection_date' => 'required|date',
            'expire_date' => 'required|date',
        ]);
        $data = $request->all();
        $data['password'] = Hash::make($request->password);
        $customer = Customer::create($data);
        return response()->json(['status' => 'success', 'data' => $customer->load('package')], 201);
    }
    public function show(Customer $customer) {
        return response()->json($customer->load('package', 'invoices', 'payments'));
    }
    public function update(Request $request, Customer $customer) {
        $data = $request->all();
        if ($request->password) $data['password'] = Hash::make($request->password);
        $customer->update($data);
        return response()->json(['status' => 'success', 'data' => $customer]);
    }
    public function destroy(Customer $customer) {
        $customer->delete();
        return response()->json(['status' => 'success', 'message' => 'Customer deleted']);
    }
}
