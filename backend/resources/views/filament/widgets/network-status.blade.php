<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">🌐 Network Status</x-slot>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">

            {{-- MikroTik Devices --}}
            @foreach($mikrotikDevices as $device)
            <div style="background:#1a2e1a; border:1px solid #2d4a2d; border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-weight:600; color:#e2e8f0;">🔧 {{ $device['name'] }}</span>
                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; background:{{ $device['status'] === 'online' ? '#22c55e30' : '#ef444430' }}; color:{{ $device['status'] === 'online' ? '#22c55e' : '#ef4444' }};">
                        {{ strtoupper($device['status']) }}
                    </span>
                </div>
                <div style="color:#94a3b8; font-size:13px;">{{ $device['host'] }}</div>
                <div style="color:#10b981; font-size:22px; font-weight:700; margin-top:8px;">{{ $device['online_users'] }}</div>
                <div style="color:#64748b; font-size:12px;">Online Users</div>
            </div>
            @endforeach

            {{-- OLT Status --}}
            @foreach($oltDevices as $olt)
            <div style="background:#1a2e1a; border:1px solid #2d4a2d; border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-weight:600; color:#e2e8f0;">📡 {{ $olt->name }}</span>
                    <span style="padding:3px 10px; border-radius:20px; font-size:11px; background:{{ $olt->status === 'active' ? '#22c55e30' : '#ef444430' }}; color:{{ $olt->status === 'active' ? '#22c55e' : '#ef4444' }};">
                        {{ strtoupper($olt->status) }}
                    </span>
                </div>
                <div style="color:#94a3b8; font-size:13px;">{{ $olt->brand }} | {{ $olt->ip }}</div>
            </div>
            @endforeach

            {{-- ONU Summary --}}
            <div style="background:#1a2e1a; border:1px solid #2d4a2d; border-radius:12px; padding:16px;">
                <div style="font-weight:600; color:#e2e8f0; margin-bottom:12px;">📊 ONU Summary</div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#94a3b8;">Total</span>
                        <span style="color:#e2e8f0; font-weight:600;">{{ $totalOnu }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#22c55e;">Online</span>
                        <span style="color:#22c55e; font-weight:600;">{{ $onlineOnu }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:#f59e0b;">Warning</span>
                        <span style="color:#f59e0b; font-weight:600;">{{ $warningOnu }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <a href="/admin/onu-monitors" style="color:#ef4444;">Critical</a>
                        <span style="color:#ef4444; font-weight:600;">{{ $criticalOnu }}</span>
                    </div>
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
