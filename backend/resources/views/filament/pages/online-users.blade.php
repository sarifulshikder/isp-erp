<x-filament-panels::page>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<style>
.ou-card { background:var(--fi-bg); border:1px solid var(--fi-border); border-radius:10px; padding:14px; text-align:center; }
.ou-btn { padding:8px 14px; border-radius:8px; border:none; cursor:pointer; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px; }
.ou-btn-blue  { background:#3b82f6; color:white; }
.ou-btn-green { background:#10b981; color:white; }
.ou-btn-red   { background:#ef4444; color:white; }
.ou-btn-orange{ background:#f97316; color:white; }
.ou-btn-gray  { background:#6b7280; color:white; }
.ou-table { width:100%; border-collapse:collapse; font-size:12px; }
.ou-table th { padding:10px 8px; text-align:left; font-weight:600; border-bottom:2px solid var(--fi-border); color:var(--fi-muted); }
.ou-table td { padding:10px 8px; border-bottom:1px solid var(--fi-border); }
.ou-table tr:hover td { background:var(--fi-hover); }
.ou-table tr.selected td { background:rgba(59,130,246,0.1); }
.ou-badge { padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; background:#10b98120; color:#10b981; }
.ou-modal-bg { position:fixed; top:0; left:0; right:0; bottom:0; background:#00000080; z-index:9999; display:flex; align-items:center; justify-content:center; }
.ou-modal { background:var(--fi-bg); border:1px solid var(--fi-border); border-radius:12px; padding:24px; width:92%; max-width:600px; }
</style>

<div>

{{-- Ping Result --}}
@if($pingResult)
<div style="background:#dcfce7;border:1px solid #10b981;border-radius:8px;padding:14px;margin-bottom:16px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <div style="color:#059669;font-weight:600;font-size:13px;">📡 Ping — {{ $pingUsername }}</div>
        <button wire:click="$set('pingResult','')" style="color:#6b7280;background:none;border:none;cursor:pointer;">✕</button>
    </div>
    <pre style="color:#065f46;margin:0;white-space:pre-wrap;font-size:12px;font-family:monospace;">{{ $pingResult }}</pre>
</div>
@endif

@livewire('traffic-chart', [], key('tc'))

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
    <div class="ou-card">
        <div style="font-size:30px;font-weight:700;color:#10b981;">{{ $total }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:2px;">মোট Online</div>
    </div>
    <div class="ou-card">
        <div style="font-size:30px;font-weight:700;color:#3b82f6;">{{ collect($users)->where('customer','!=','Unknown')->count() }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:2px;">Registered</div>
    </div>
    <div class="ou-card">
        <div style="font-size:30px;font-weight:700;color:#f59e0b;">{{ collect($users)->where('customer','Unknown')->count() }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:2px;">Unknown</div>
    </div>
</div>

{{-- Action Buttons --}}
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center;">
    <button wire:click="loadUsers" class="ou-btn ou-btn-gray">🔄 Refresh</button>
    <button
        onclick="doLiveTraffic()"
        class="ou-btn ou-btn-green">
        📊 Live Traffic
    </button>
    <button
        onclick="doPing()"
        class="ou-btn ou-btn-blue">
        📡 Ping
    </button>
    <button
        onclick="doDisconnect()"
        class="ou-btn ou-btn-red">
        ✕ Disconnect
    </button>
    <button
        onclick="doDisconnectAll()"
        class="ou-btn ou-btn-orange">
        ✕✕ Disconnect Multiple
    </button>
    <span id="selectedCount" style="font-size:12px;color:#6b7280;margin-left:8px;"></span>
</div>

{{-- Search + Filter --}}
<div style="display:flex;gap:10px;margin-bottom:16px;">
    <input wire:model.live.debounce.300ms="searchQuery" placeholder="🔍 Username, Customer, IP..." style="flex:1;padding:9px 14px;border-radius:8px;border:1px solid var(--fi-border);background:var(--fi-bg);font-size:13px;outline:none;color:inherit;"/>
    <select wire:model.live="selectedDeviceId" wire:change="loadUsers" style="padding:9px 14px;border-radius:8px;border:1px solid var(--fi-border);background:var(--fi-bg);font-size:13px;outline:none;color:inherit;">
        <option value="0">সব Router</option>
        @foreach(App\Models\MikrotikDevice::where('status','active')->get() as $dev)
        <option value="{{ $dev->id }}">{{ $dev->name }}</option>
        @endforeach
    </select>
</div>

{{-- Table --}}
@if(empty($users))
<div style="text-align:center;padding:50px;color:#6b7280;">
    <div style="font-size:48px;margin-bottom:12px;">📡</div>
    <p style="font-size:15px;">কোনো online user নেই।</p>
</div>
@else
<div style="overflow-x:auto;border-radius:10px;border:1px solid var(--fi-border);">
    <table class="ou-table">
        <thead>
            <tr>
                <th style="width:36px;"><input type="checkbox" id="checkAll" onchange="toggleAll(this)" style="cursor:pointer;width:15px;height:15px;"></th>
                <th>Status</th>
                <th>Username</th>
                <th>Customer</th>
                <th>IP Address</th>
                <th>MAC</th>
                <th>Uptime</th>
                <th>Router</th>
            </tr>
        </thead>
        <tbody id="ouTableBody">
            @foreach($users as $i => $user)
            <tr id="row_{{ $i }}" data-username="{{ $user['username'] }}" data-device="{{ $user['device_id'] }}" data-session="{{ $user['id'] }}" data-ip="{{ $user['ip'] }}" onclick="rowClick(this)" style="cursor:pointer;">
                <td onclick="event.stopPropagation()">
                    <input type="checkbox" class="rowCheck" onchange="updateCount()" style="cursor:pointer;width:15px;height:15px;">
                </td>
                <td><span class="ou-badge">Online</span></td>
                <td style="font-weight:600;">{{ $user['username'] }}</td>
                <td style="color:{{ $user['customer']==='Unknown'?'#9ca3af':'inherit' }};">{{ $user['customer'] }}</td>
                <td style="font-family:monospace;color:#10b981;">{{ $user['ip'] }}</td>
                <td style="font-family:monospace;font-size:11px;color:#9ca3af;">{{ $user['mac'] }}</td>
                <td style="color:#f59e0b;">{{ $user['uptime'] }}</td>
                <td style="color:#6366f1;font-size:11px;">{{ $user['device_name'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

</div>

<script>
var _wire = window.Livewire;

function getSelected() {
    return Array.from(document.querySelectorAll('.rowCheck:checked')).map(function(cb) {
        return cb.closest('tr');
    });
}

function updateCount() {
    var n = getSelected().length;
    var el = document.getElementById('selectedCount');
    if (el) el.textContent = n > 0 ? n + ' টি selected' : '';
}

function toggleAll(cb) {
    document.querySelectorAll('.rowCheck').forEach(function(c) { c.checked = cb.checked; });
    updateCount();
}

function rowClick(row) {
    var cb = row.querySelector('.rowCheck');
    cb.checked = !cb.checked;
    updateCount();
}

function doLiveTraffic() {
    var sel = getSelected();
    if (sel.length === 0) { alert('একজন user select করুন।'); return; }
    var row = sel[0];
    @this.dispatch('openTrafficModal', {username: row.dataset.username, deviceId: parseInt(row.dataset.device)});
document.querySelectorAll('.rowCheck').forEach(function(c){ c.checked = false; }); updateCount();
}

function doPing() {
    var sel = getSelected();
    if (sel.length === 0) { alert('একজন user select করুন।'); return; }
    var row = sel[0];
    if (row.dataset.ip === '—') { alert('IP address নেই।'); return; }
    @this.ping(row.dataset.ip, row.dataset.username);
}

function doDisconnect() {
    var sel = getSelected();
    if (sel.length === 0) { alert('একজন user select করুন।'); return; }
    var row = sel[0];
    if (!confirm(row.dataset.username + ' কে disconnect করবেন?')) return;
    @this.disconnect(row.dataset.session, parseInt(row.dataset.device));
}

function doDisconnectAll() {
    var sel = getSelected();
    if (sel.length === 0) { alert('কমপক্ষে একজন user select করুন।'); return; }
    if (!confirm(sel.length + ' জন user কে disconnect করবেন?')) return;
    sel.forEach(function(row) {
        @this.disconnect(row.dataset.session, parseInt(row.dataset.device));
    });
}
</script>

</x-filament-panels::page>
