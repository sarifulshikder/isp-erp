<?php
namespace App\Http\Controllers;

use App\Models\MikrotikDevice;
use App\Models\Customer;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class MikrotikController extends Controller
{
    private MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->mikrotik = $mikrotik;
    }

    public function index()
    {
        return response()->json(MikrotikDevice::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $device = MikrotikDevice::create($request->all());
        return response()->json(['status' => 'success', 'data' => $device], 201);
    }

    public function show(MikrotikDevice $mikrotikDevice)
    {
        return response()->json($mikrotikDevice);
    }

    public function update(Request $request, MikrotikDevice $mikrotikDevice)
    {
        $mikrotikDevice->update($request->all());
        return response()->json(['status' => 'success', 'data' => $mikrotikDevice]);
    }

    public function destroy(MikrotikDevice $mikrotikDevice)
    {
        $mikrotikDevice->delete();
        return response()->json(['status' => 'success', 'message' => 'Device deleted']);
    }

    public function testConnection(MikrotikDevice $mikrotikDevice)
    {
        $connected = $this->mikrotik->connect($mikrotikDevice);
        if (!$connected) {
            return response()->json(['status' => 'error', 'message' => 'Connection failed'], 500);
        }
        $result = $this->mikrotik->testConnection();
        return response()->json(['status' => 'success', 'data' => $result]);
    }

    public function enableUser(Request $request, MikrotikDevice $mikrotikDevice)
    {
        $request->validate(['username' => 'required|string']);
        $connected = $this->mikrotik->connect($mikrotikDevice);
        if (!$connected) return response()->json(['status' => 'error', 'message' => 'Connection failed'], 500);
        $result = $this->mikrotik->enablePPPoEUser($request->username);
        return response()->json(['status' => $result ? 'success' : 'error']);
    }

    public function disableUser(Request $request, MikrotikDevice $mikrotikDevice)
    {
        $request->validate(['username' => 'required|string']);
        $connected = $this->mikrotik->connect($mikrotikDevice);
        if (!$connected) return response()->json(['status' => 'error', 'message' => 'Connection failed'], 500);
        $result = $this->mikrotik->disablePPPoEUser($request->username);
        return response()->json(['status' => $result ? 'success' : 'error']);
    }

    public function onlineUsers(MikrotikDevice $mikrotikDevice)
    {
        $connected = $this->mikrotik->connect($mikrotikDevice);
        if (!$connected) return response()->json(['status' => 'error', 'message' => 'Connection failed'], 500);
        $users = $this->mikrotik->getOnlineUsers();
        return response()->json(['status' => 'success', 'data' => $users, 'count' => count($users)]);
    }

    public function addCustomerToMikrotik(Request $request, MikrotikDevice $mikrotikDevice)
    {
        $request->validate(['customer_id' => 'required|exists:customers,id']);
        $customer = Customer::with('package')->find($request->customer_id);
        $connected = $this->mikrotik->connect($mikrotikDevice);
        if (!$connected) return response()->json(['status' => 'error', 'message' => 'Connection failed'], 500);
        $profile = $customer->mikrotik_profile ?? $customer->package->name ?? 'default';
        $result = $this->mikrotik->addPPPoEUser($customer->username, $customer->password, $profile);
        return response()->json(['status' => $result ? 'success' : 'error']);
    }

    public function systemInfo(MikrotikDevice $mikrotikDevice)
    {
        $connected = $this->mikrotik->connect($mikrotikDevice);
        if (!$connected) return response()->json(['status' => 'error', 'message' => 'Connection failed'], 500);
        $info = $this->mikrotik->getSystemInfo();
        return response()->json(['status' => 'success', 'data' => $info]);
    }
}
