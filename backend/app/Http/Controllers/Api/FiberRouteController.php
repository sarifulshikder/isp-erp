<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FiberRoute;
use Illuminate\Http\Request;

class FiberRouteController extends Controller
{
    public function index()
    {
        return FiberRoute::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string',
            'type'          => 'required|string',
            'olt_device_id' => 'nullable|integer',
            'splitter_id'   => 'nullable|integer',
            'customer_id'   => 'nullable|integer',
            'coordinates'   => 'required|array|min:2',
            'color'         => 'nullable|string',
            'status'        => 'nullable|string',
            'note'          => 'nullable|string',
        ]);

        $route = FiberRoute::create($validated);
        return response()->json($route, 201);
    }

    public function show(FiberRoute $fiberRoute)
    {
        return $fiberRoute;
    }

    public function update(Request $request, FiberRoute $fiberRoute)
    {
        $fiberRoute->update($request->all());
        return $fiberRoute;
    }

    public function destroy(FiberRoute $fiberRoute)
    {
        $fiberRoute->delete();
        return response()->json(['deleted' => true]);
    }
}
