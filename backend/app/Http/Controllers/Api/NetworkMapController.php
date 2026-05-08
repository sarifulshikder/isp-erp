<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\OltDevice;
use App\Models\Splitter;
use App\Models\FiberRoute;
use App\Models\Zone;

class NetworkMapController extends Controller
{
    public function index()
    {
        // OLT Devices
        $olts = OltDevice::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'name', 'brand', 'ip', 'status', 'latitude', 'longitude'])
            ->map(fn($o) => [
                'id'     => $o->id,
                'type'   => 'olt',
                'name'   => $o->name,
                'brand'  => $o->brand,
                'ip'     => $o->ip,
                'status' => $o->status,
                'lat'    => (float) $o->latitude,
                'lng'    => (float) $o->longitude,
            ]);

        // Splitters
        $splitters = Splitter::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('zone:id,name')
            ->get(['id', 'name', 'type', 'status', 'latitude', 'longitude', 'zone_id', 'olt_device_id'])
            ->map(fn($s) => [
                'id'      => $s->id,
                'type'    => 'splitter',
                'name'    => $s->name,
                'split'   => $s->type,
                'status'  => $s->status,
                'zone'    => $s->zone?->name,
                'lat'     => (float) $s->latitude,
                'lng'     => (float) $s->longitude,
            ]);

        // Customers
        $customers = Customer::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('package:id,name', 'zone:id,name')
            ->get(['id', 'name', 'phone', 'status', 'latitude', 'longitude', 'package_id', 'zone_id', 'expire_date'])
            ->map(fn($c) => [
                'id'      => $c->id,
                'type'    => 'customer',
                'name'    => $c->name,
                'phone'   => $c->phone,
                'status'  => $c->status,
                'package' => $c->package?->name,
                'zone'    => $c->zone?->name,
                'expire'  => $c->expire_date?->format('d M Y'),
                'lat'     => (float) $c->latitude,
                'lng'     => (float) $c->longitude,
            ]);

        // Fiber Routes
        $routes = FiberRoute::where('status', 'active')
            ->get(['id', 'name', 'type', 'coordinates', 'color', 'status'])
            ->map(fn($r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'type'        => $r->type,
                'coordinates' => $r->coordinates,
                'color'       => $r->color,
                'status'      => $r->status,
            ]);

        // Zones
        $zones = Zone::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->withCount('customers')
            ->get(['id', 'name', 'latitude', 'longitude'])
            ->map(fn($z) => [
                'id'              => $z->id,
                'name'            => $z->name,
                'customers_count' => $z->customers_count,
                'lat'             => (float) $z->latitude,
                'lng'             => (float) $z->longitude,
            ]);

        return response()->json([
            'olts'      => $olts,
            'splitters' => $splitters,
            'customers' => $customers,
            'routes'    => $routes,
            'zones'     => $zones,
        ]);
    }
}
