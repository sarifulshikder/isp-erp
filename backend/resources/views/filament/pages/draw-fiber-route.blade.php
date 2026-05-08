<x-filament-panels::page>

<div class="space-y-4">

    {{-- Route Info Form --}}
    <div style="background:white;border-radius:12px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
        <h3 style="font-weight:600;margin-bottom:12px;color:#374151;">Route তথ্য</h3>
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;">
            <div>
                <label style="font-size:13px;color:#6b7280;">Route Name</label>
                <input type="text" id="route-name" placeholder="Route-01"
                    style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;margin-top:4px;color:#111827;background:white;">
            </div>
            <div>
                <label style="font-size:13px;color:#6b7280;">Route Type</label>
                <select id="route-type"
                    style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;margin-top:4px;color:#111827;background:white;">
                    <option value="olt_to_splitter">OLT → Splitter</option>
                    <option value="splitter_to_customer">Splitter → Customer</option>
                    <option value="olt_to_customer">OLT → Customer</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px;color:#6b7280;">OLT Device</label>
                <select id="route-olt"
                    style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;margin-top:4px;color:#111827;background:white;">
                    <option value="">-- Select OLT --</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px;color:#6b7280;">Splitter</label>
                <select id="route-splitter"
                    style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;margin-top:4px;color:#111827;background:white;">
                    <option value="">-- Select Splitter --</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px;color:#6b7280;">Customer</label>
                <select id="route-customer"
                    style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;margin-top:4px;color:#111827;background:white;">
                    <option value="">-- Select Customer --</option>
                </select>
            </div>
            <div>
                <label style="font-size:13px;color:#6b7280;">Route Color</label>
                <input type="color" id="route-color" value="#FF6B35"
                    style="width:100%;padding:4px;border:1px solid #d1d5db;border-radius:8px;margin-top:4px;height:38px;">
            </div>
        </div>
    </div>

    {{-- Instructions --}}
    <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:12px;padding:12px;">
        <p style="font-size:13px;color:#1D4ED8;margin:0;">
            📍 <strong>Map এ click করুন</strong> → প্রতিটি click এ একটি point যোগ হবে।
            রাস্তার বাঁকে বাঁকে click করুন। শেষে <strong>Save Route</strong> চাপুন।
        </p>
    </div>

    {{-- Controls --}}
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <button onclick="undoLastPoint()"
            style="padding:8px 16px;background:#F59E0B;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;">
            ↩️ Undo Last Point
        </button>
        <button onclick="clearRoute()"
            style="padding:8px 16px;background:#EF4444;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;">
            🗑️ Clear All
        </button>
        <button onclick="setMapView('street')" id="btn-street"
            style="padding:8px 16px;background:#2563eb;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;">
            🗺️ Street
        </button>
        <button onclick="setMapView('satellite')" id="btn-satellite"
            style="padding:8px 16px;background:#e5e7eb;color:#374151;border:none;border-radius:8px;cursor:pointer;font-size:13px;">
            🛰️ Satellite
        </button>
        <span id="point-count"
            style="padding:8px 16px;background:#F3F4F6;border-radius:8px;font-size:13px;color:#374151;">
            Points: 0
        </span>
        <button onclick="saveRoute()"
            style="padding:8px 20px;background:#10B981;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;margin-left:auto;">
            💾 Save Route
        </button>
    </div>

    {{-- Map --}}
    <div style="background:white;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.1);overflow:hidden;">
        <div id="draw-map" style="height:580px;width:100%;cursor:crosshair;"></div>
    </div>

    {{-- Coordinates Preview --}}
    <div style="background:white;border-radius:12px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,0.1);">
        <h3 style="font-weight:600;margin-bottom:8px;color:#374151;font-size:13px;">Coordinates Preview</h3>
        <textarea id="coords-preview" readonly rows="4"
            style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px;font-size:12px;font-family:monospace;color:#6b7280;">
        </textarea>
    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// ── Tile Layers ───────────────────────────────────────────────────────────────
const streetLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
});
const satelliteLayer = L.tileLayer(
    'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© ESRI', maxZoom: 19
});
const labelLayer = L.tileLayer(
    'https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}.png', {
    maxZoom: 19, opacity: 0.7
});

const map = L.map('draw-map', {
    center: [23.8103, 90.4125],
    zoom: 15,
    layers: [streetLayer]
});

let currentTile = 'street';
function setMapView(type) {
    if (type === 'satellite' && currentTile !== 'satellite') {
        map.removeLayer(streetLayer);
        map.addLayer(satelliteLayer);
        map.addLayer(labelLayer);
        currentTile = 'satellite';
        document.getElementById('btn-satellite').style.cssText = 'padding:8px 16px;background:#2563eb;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;';
        document.getElementById('btn-street').style.cssText = 'padding:8px 16px;background:#e5e7eb;color:#374151;border:none;border-radius:8px;cursor:pointer;font-size:13px;';
    } else if (type === 'street' && currentTile !== 'street') {
        map.removeLayer(satelliteLayer);
        map.removeLayer(labelLayer);
        map.addLayer(streetLayer);
        currentTile = 'street';
        document.getElementById('btn-street').style.cssText = 'padding:8px 16px;background:#2563eb;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;';
        document.getElementById('btn-satellite').style.cssText = 'padding:8px 16px;background:#e5e7eb;color:#374151;border:none;border-radius:8px;cursor:pointer;font-size:13px;';
    }
}

// ── Drawing State ─────────────────────────────────────────────────────────────
let points = [];
let markers = [];
let polyline = null;

function updatePolyline() {
    if (polyline) map.removeLayer(polyline);
    if (points.length > 1) {
        const color = document.getElementById('route-color').value;
        polyline = L.polyline(points, { color, weight: 4, opacity: 0.9 }).addTo(map);
    }
    document.getElementById('point-count').textContent = `Points: ${points.length}`;
    document.getElementById('coords-preview').value = JSON.stringify(points, null, 2);
}

// Click to add point
map.on('click', function(e) {
    const lat = parseFloat(e.latlng.lat.toFixed(7));
    const lng = parseFloat(e.latlng.lng.toFixed(7));
    points.push([lat, lng]);

    const marker = L.circleMarker([lat, lng], {
        radius: 6, color: '#fff', weight: 2,
        fillColor: document.getElementById('route-color').value,
        fillOpacity: 1
    }).addTo(map);
    markers.push(marker);
    updatePolyline();
});

function undoLastPoint() {
    if (points.length === 0) return;
    points.pop();
    const m = markers.pop();
    if (m) map.removeLayer(m);
    updatePolyline();
}

function clearRoute() {
    points = [];
    markers.forEach(m => map.removeLayer(m));
    markers = [];
    if (polyline) { map.removeLayer(polyline); polyline = null; }
    document.getElementById('point-count').textContent = 'Points: 0';
    document.getElementById('coords-preview').value = '';
}

// ── Load existing markers for reference ──────────────────────────────────────
fetch('/api/network-map')
    .then(r => r.json())
    .then(data => {
        // Populate selects
        const oltSel = document.getElementById('route-olt');
        data.olts.forEach(o => {
            oltSel.innerHTML += `<option value="${o.id}">${o.name}</option>`;
            // Show OLT marker
            L.marker([o.lat, o.lng], {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="background:#EF4444;width:24px;height:24px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;font-size:10px;box-shadow:0 2px 4px rgba(0,0,0,0.3);">📡</div>',
                    iconSize: [24,24], iconAnchor: [12,12]
                })
            }).bindTooltip(o.name).addTo(map);
        });

        const splSel = document.getElementById('route-splitter');
        data.splitters.forEach(s => {
            splSel.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            L.marker([s.lat, s.lng], {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="background:#F97316;width:24px;height:24px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;font-size:10px;box-shadow:0 2px 4px rgba(0,0,0,0.3);">🔀</div>',
                    iconSize: [24,24], iconAnchor: [12,12]
                })
            }).bindTooltip(s.name).addTo(map);
        });

        const custSel = document.getElementById('route-customer');
        data.customers.forEach(c => {
            custSel.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            L.marker([c.lat, c.lng], {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="background:#3B82F6;width:24px;height:24px;border-radius:50%;border:3px solid white;display:flex;align-items:center;justify-content:center;font-size:10px;box-shadow:0 2px 4px rgba(0,0,0,0.3);">👤</div>',
                    iconSize: [24,24], iconAnchor: [12,12]
                })
            }).bindTooltip(c.name).addTo(map);
        });

        // Fit to existing markers
        const allPoints = [
            ...data.olts.map(o => [o.lat, o.lng]),
            ...data.splitters.map(s => [s.lat, s.lng]),
            ...data.customers.map(c => [c.lat, c.lng]),
        ];
        if (allPoints.length > 0) map.fitBounds(allPoints, { padding: [40,40] });
    });

// ── Save Route ────────────────────────────────────────────────────────────────
function saveRoute() {
    const name = document.getElementById('route-name').value.trim();
    if (!name) { alert('Route Name দিন!'); return; }
    if (points.length < 2) { alert('কমপক্ষে ২টা point দিন!'); return; }

    const payload = {
        name,
        type:          document.getElementById('route-type').value,
        olt_device_id: document.getElementById('route-olt').value || null,
        splitter_id:   document.getElementById('route-splitter').value || null,
        customer_id:   document.getElementById('route-customer').value || null,
        color:         document.getElementById('route-color').value,
        coordinates:   points,
        status:        'active',
    };

    fetch('/api/fiber-routes', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            alert('✅ Route সফলভাবে save হয়েছে!');
            clearRoute();
            document.getElementById('route-name').value = '';
        } else {
            alert('❌ Error: ' + JSON.stringify(data));
        }
    })
    .catch(e => alert('❌ Save failed: ' + e.message));
}
</script>

</x-filament-panels::page>
