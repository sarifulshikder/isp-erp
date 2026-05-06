<?php
namespace App\Http\Controllers;
use App\Models\Package;
use Illuminate\Http\Request;
class PackageController extends Controller {
    public function index() {
        return response()->json(Package::all());
    }
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'speed_download' => 'required|integer',
            'speed_upload' => 'required|integer',
            'price' => 'required|numeric',
            'validity_days' => 'required|integer',
        ]);
        $package = Package::create($request->all());
        return response()->json(['status' => 'success', 'data' => $package], 201);
    }
    public function show(Package $package) {
        return response()->json($package);
    }
    public function update(Request $request, Package $package) {
        $package->update($request->all());
        return response()->json(['status' => 'success', 'data' => $package]);
    }
    public function destroy(Package $package) {
        $package->delete();
        return response()->json(['status' => 'success', 'message' => 'Package deleted']);
    }
}
