<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">📊 Live Interface Traffic (10s refresh)</x-slot>
        @if(empty($devices))
            <div style="text-align:center; padding:20px; color:#64748b;">
                <p>No MikroTik devices connected</p>
            </div>
        @else
            @foreach($devices as $device)
            <div style="margin-bottom:20px;">
                <div style="font-weight:600; color:#10b981; margin-bottom:12px; font-size:15px;">
                    🔧 {{ $device['device'] }} ({{ $device['host'] }})
                </div>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap:10px;">
                    @foreach($device['interfaces'] as $iface)
                    <div style="background:#1a2e1a; border:1px solid #2d4a2d; border-radius:10px; padding:12px;">
                        <div style="font-weight:600; color:#e2e8f0; font-size:13px; margin-bottom:6px;">
                            {{ $iface['name'] }}
                            <span style="font-size:10px; color:#64748b;">({{ $iface['type'] }})</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; margin-top:6px;">
                            <div>
                                <div style="color:#64748b; font-size:10px;">↓ RX</div>
                                <div style="color:#22c55e; font-size:12px; font-weight:600;">{{ $iface['rx_mbps'] ?? $iface['rx'] ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div style="color:#64748b; font-size:10px;">↑ TX</div>
                                <div style="color:#f59e0b; font-size:12px; font-weight:600;">{{ $iface['tx_mbps'] ?? $iface['tx'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
