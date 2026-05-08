<x-filament-panels::page>

<div class="space-y-4">

    {{-- Controls --}}
    <div class="flex flex-wrap gap-3 items-center bg-white dark:bg-gray-800 rounded-xl p-4 shadow">
        <span class="font-semibold text-gray-700 dark:text-gray-200">Layer:</span>
        <label class="flex items-center gap-1 cursor-pointer">
            <input type="checkbox" id="toggle-olt" checked onchange="toggleLayer('olt')" class="rounded">
            <span class="flex items-center gap-1 text-sm"><span class="inline-block w-3 h-3 rounded-full bg-red-500"></span> OLT</span>
        </label>
        <label class="flex items-center gap-1 cursor-pointer">
            <input type="checkbox" id="toggle-splitter" checked onchange="toggleLayer('splitter')" class="rounded">
            <span class="flex items-center gap-1 text-sm"><span class="inline-block w-3 h-3 rounded-full bg-orange-500"></span> Splitter</span>
        </label>
        <label class="flex items-center gap-1 cursor-pointer">
            <input type="checkbox" id="toggle-customer" checked onchange="toggleLayer('customer')" class="rounded">
            <span class="flex items-center gap-1 text-sm"><span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span> Customer</span>
        </label>
        <label class="flex items-center gap-1 cursor-pointer">
            <input type="checkbox" id="toggle-route" checked onchange="toggleLayer('route')" class="rounded">
            <span class="flex items-center gap-1 text-sm"><span class="inline-block w-3 h-3 rounded-full bg-yellow-500"></span> Fiber Route</span>
        </label>
        <div class="ml-auto flex gap-2">
            <button onclick="setMapView('street')" id="btn-street"
                style="padding:4px 12px;border-radius:8px;background:#2563eb;color:white;font-size:13px;border:none;cursor:pointer;">
                🗺️ Street
            </button>
            <button onclick="setMapView('satellite')" id="btn-satellite"
                style="padding:4px 12px;border-radius:8px;background:#e5e7eb;color:#374151;font-size:13px;border:none;cursor:pointer;">
                🛰️ Satellite
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
        <div style="background:white;border-radius:12px;padding:12px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
            <div id="stat-olt" style="font-size:24px;font-weight:bold;color:#EF4444;">-</div>
            <div style="font-size:12px;color:#6b7280;">OLT</div>
        </div>
        <div style="background:white;border-radius:12px;padding:12px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
            <div id="stat-splitter" style="font-size:24px;font-weight:bold;color:#F97316;">-</div>
            <div style="font-size:12px;color:#6b7280;">Splitter</div>
        </div>
        <div style="background:white;border-radius:12px;padding:12px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
            <div id="stat-customer" style="font-size:24px;font-weight:bold;color:#3B82F6;">-</div>
            <div style="font-size:12px;color:#6b7280;">Customer</div>
        </div>
        <div style="background:white;border-radius:12px;padding:12px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
            <div id="stat-route" style="font-size:24px;font-weight:bold;color:#EAB308;">-</div>
            <div style="font-size:12px;color:#6b7280;">Fiber Route</div>
        </div>
    </div>

    {{-- Map --}}
    <div style="background:white;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.1);overflow:hidden;">
        <div id="network-map" style="height:620px;width:100%;"></div>
    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
});

const satelliteLayer = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© ESRI World Imagery', maxZoom: 19
});

// Labels layer on top of satellite
const labelLayer = L.tileLayer(
    'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}.png', {
    attribution: '', maxZoom: 19, opacity: 0.7
});

const map = L.map('network-map', {
    center: [23.8103, 90.4125],
    zoom: 13,
    layers: [streetLayer]
});

let currentTile = 'street';

function setMapView(type) {
    if (type === 'satellite' && currentTile !== 'satellite') {
        map.removeLayer(streetLayer);
        map.addLayer(satelliteLayer);
        map.addLayer(labelLayer);
        currentTile = 'satellite';
        document.getElementById('btn-satellite').style.background = '#2563eb';
        document.getElementById('btn-satellite').style.color = 'white';
        document.getElementById('btn-street').style.background = '#e5e7eb';
        document.getElementById('btn-street').style.color = '#374151';
    } else if (type === 'street' && currentTile !== 'street') {
        map.removeLayer(satelliteLayer);
        map.removeLayer(labelLayer);
        map.addLayer(streetLayer);
        currentTile = 'street';
        document.getElementById('btn-street').style.background = '#2563eb';
        document.getElementById('btn-street').style.color = 'white';
        document.getElementById('btn-satellite').style.background = '#e5e7eb';
        document.getElementById('btn-satellite').style.color = '#374151';
    }
}

const layers = {
    olt:      L.layerGroup().addTo(map),
    splitter: L.layerGroup().addTo(map),
    customer: L.layerGroup().addTo(map),
    route:    L.layerGroup().addTo(map),
};

function toggleLayer(type) {
    if (map.hasLayer(layers[type])) map.removeLayer(layers[type]);
    else map.addLayer(layers[type]);
}

function makeIcon(color, symbol) {
    return L.divIcon({
        className: '',
        html: `<div style="background:${color};width:32px;height:32px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.4);">
                 <span style="display:block;transform:rotate(45deg);text-align:center;line-height:26px;font-size:14px;">${symbol}</span>
               </div>`,
        iconSize: [32, 32], iconAnchor: [16, 32], popupAnchor: [0, -34]
    });
}

const icons = {
    olt:       makeIcon('#EF4444', '📡'),
    splitter:  makeIcon('#F97316', '🔀'),
    active:    makeIcon('#3B82F6', '👤'),
    suspended: makeIcon('#6B7280', '⛔'),
    inactive:  makeIcon('#EAB308', '💤'),
};

fetch('/api/network-map')
    .then(r => r.json())
    .then(data => {
        document.getElementById('stat-olt').textContent      = data.olts.length;
        document.getElementById('stat-splitter').textContent = data.splitters.length;
        document.getElementById('stat-customer').textContent = data.customers.length;
        document.getElementById('stat-route').textContent    = data.routes.length;

        const bounds = [];

        data.olts.forEach(o => {
            bounds.push([o.lat, o.lng]);
            L.marker([o.lat, o.lng], { icon: icons.olt })
                .bindPopup(`<b>📡 ${o.name}</b><br>Brand: ${o.brand}<br>IP: ${o.ip}<br>Status: <b>${o.status}</b>`)
                .addTo(layers.olt);
        });

        data.splitters.forEach(s => {
            bounds.push([s.lat, s.lng]);
            L.marker([s.lat, s.lng], { icon: icons.splitter })
                .bindPopup(`<b>🔀 ${s.name}</b><br>Type: ${s.split}<br>Zone: ${s.zone ?? 'N/A'}<br>Status: <b>${s.status}</b>`)
                .addTo(layers.splitter);
        });

        data.customers.forEach(c => {
            bounds.push([c.lat, c.lng]);
            const icon = icons[c.status] ?? icons.active;
            L.marker([c.lat, c.lng], { icon })
                .bindPopup(`<b>👤 ${c.name}</b><br>📞 ${c.phone}<br>Package: ${c.package ?? 'N/A'}<br>Zone: ${c.zone ?? 'N/A'}<br>Expire: ${c.expire ?? 'N/A'}<br>Status: <b>${c.status}</b>`)
                .addTo(layers.customer);
        });

        data.routes.forEach(r => {
            if (r.coordinates && r.coordinates.length > 1) {
                L.polyline(r.coordinates, { color: r.color, weight: 3, opacity: 0.8 })
                    .bindPopup(`<b>〰️ ${r.name}</b><br>Type: ${r.type}`)
                    .addTo(layers.route);
            }
        });

        if (bounds.length > 0) map.fitBounds(bounds, { padding: [40, 40] });
    })
    .catch(e => console.error('Map error:', e));
</script>

</x-filament-panels::page>
