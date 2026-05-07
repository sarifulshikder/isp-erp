<?php
namespace App\Http\Controllers;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        // Plain text password — FreeRADIUS PPPoE authentication এর জন্য
        $customer = Customer::create($data);

        // Radius এ user যোগ করো
        $this->syncRadiusUser($customer->username, $request->password);

        return response()->json(['status' => 'success', 'data' => $customer->load('package')], 201);
    }

    public function show(Customer $customer) {
        return response()->json($customer->load('package', 'invoices', 'payments'));
    }

    public function update(Request $request, Customer $customer) {
        $data = $request->all();
        $customer->update($data);

        // Password পরিবর্তন হলে radius আপডেট করো
        if ($request->password) {
            $this->syncRadiusUser($customer->username, $request->password);
        }

        return response()->json(['status' => 'success', 'data' => $customer]);
    }

    public function destroy(Customer $customer) {
        // Radius থেকে user মুছে দাও
        DB::connection('radius')->table('radcheck')
            ->where('username', $customer->username)->delete();
        DB::connection('radius')->table('radusergroup')
            ->where('username', $customer->username)->delete();

        $customer->delete();
        return response()->json(['status' => 'success', 'message' => 'Customer deleted']);
    }

    private function syncRadiusUser(string $username, string $password): void
    {
        try {
            DB::connection('radius')->table('radcheck')->updateOrInsert(
                ['username' => $username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $password]
            );
        } catch (\Exception $e) {
            \Log::warning('Radius sync error: ' . $e->getMessage());
        }
    }
}
