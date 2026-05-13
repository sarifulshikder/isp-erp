<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">🔗 Uplink Port Status</x-slot>
        @if($uplinks->isEmpty())
            <div style="text-align:center;padding:16px;color:#6b7280;font-size:13px;">
                No uplink data yet. Run: <code>php artisan uplink:monitor</code>
            </div>
        @else
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">
            @foreach($uplinks as $uplink)
            @php
                $isUp = $uplink->status === 'up';
                $isActive = $uplink->is_active_port;
                $bgColor = $isUp ? ($isActive ? '#dcfce7' : '#f0fdf4') : '#fef2f2';
                $borderColor = $isUp ? ($isActive ? '#16a34a' : '#86efac') : '#ef4444';
                $statusColor = $isUp ? '#16a34a' : '#ef4444';
                $roleLabel = $uplink->role === 'master' ? '🔵 Master' : '🟡 Backup';
            @endphp
            <div style="background:{{ $bgColor }};border:2px solid {{ $borderColor }};border-radius:10px;padding:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div style="font-weight:700;font-size:15px;color:#111;">{{ $uplink->port_name }}</div>
                    @if($isActive)
                    <span style="background:#16a34a;color:white;font-size:10px;padding:2px 8px;border-radius:20px;font-weight:700;">ACTIVE</span>
                    @endif
                </div>
                <div style="font-size:12px;color:#6b7280;margin-bottom:6px;">{{ $uplink->description }}</div>
                <div style="font-size:12px;color:#374151;margin-bottom:6px;">{{ $roleLabel }}</div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:10px;height:10px;border-radius:50%;background:{{ $statusColor }};{{ $isUp ? 'box-shadow:0 0 6px '.$statusColor.';' : '' }}"></div>
                    <span style="font-weight:700;color:{{ $statusColor }};font-size:14px;">{{ strtoupper($uplink->status) }}</span>
                </div>
                @if($uplink->last_changed_at)
                <div style="font-size:11px;color:#9ca3af;margin-top:8px;">
                    Last changed: {{ $uplink->last_changed_at->diffForHumans() }}
                </div>
                @endif
                <div style="font-size:11px;color:#9ca3af;">
                    OLT: {{ $uplink->olt->name ?? '—' }}
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
