<?php
namespace App\Http\Controllers;
use App\Models\MikrotikDevice;
use Illuminate\Http\Request;
class MikrotikController extends Controller {
    public function index() {
        return response()->json(MikrotikDevice::all());
    }
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $device = MikrotikDevice::create($request->all());
        return response()->json(['status' => 'success', 'data' => $device], 201);
    }
    public function show(MikrotikDevice $mikrotikDevice) {
        return response()->json($mikrotikDevice);
    }
    public function update(Request $request, MikrotikDevice $mikrotikDevice) {
        $mikrotikDevice->update($request->all());
        return response()->json(['status' => 'success', 'data' => $mikrotikDevice]);
    }
    public function destroy(MikrotikDevice $mikrotikDevice) {
        $mikrotikDevice->delete();
        return response()->json(['status' => 'success', 'message' => 'Device deleted']);
    }
}
