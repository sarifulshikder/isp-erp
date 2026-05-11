<x-filament-panels::page>
    <div wire:poll.30s="loadUsers">

        {{-- Ping Result --}}
        @if($pingResult)
        <div style="background:#0f172a;border:1px solid #10b981;border-radius:8px;padding:16px;margin-bottom:16px;font-family:monospace;">
            <div style="color:#10b981;font-weight:600;margin-bottom:8px;">📡 Ping Result — {{ $pingUsername }}</div>
            <pre style="color:#e2e8f0;margin:0;white-space:pre-wrap;">{{ $pingResult }}</pre>
            <button wire:click="$set('pingResult', '')" style="margin-top:8px;color:#64748b;font-size:12px;">✕ Close</button>
        </div>
        @endif

        {{-- Stats --}}
        <div style="display:flex;gap:16px;margin-bottom:16px;">
            <div style="background:#1e293b;border-radius:8px;padding:12px 20px;flex:1;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#10b981;">{{ $total }}</div>
                <div style="color:#64748b;font-size:12px;">মোট Online</div>
            </div>
            <div style="background:#1e293b;border-radius:8px;padding:12px 20px;flex:1;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#6366f1;">{{ collect($users)->where('customer', '!=', 'Unknown')->count() }}</div>
                <div style="color:#64748b;font-size:12px;">Registered</div>
            </div>
            <div style="background:#1e293b;border-radius:8px;padding:12px 20px;flex:1;text-align:center;">
                <div style="font-size:28px;font-weight:700;color:#f59e0b;">{{ collect($users)->where('customer', 'Unknown')->count() }}</div>
                <div style="color:#64748b;font-size:12px;">Unknown</div>
            </div>
        </div>

        {{-- Table --}}
        @if(empty($users))
            <div style="text-align:center;padding:40px;color:#64748b;">
                <div style="font-size:40px;margin-bottom:12px;">📡</div>
                <p>কোনো online user নেই।</p>
            </div>
        @else
            <div style="overflow-x:auto;border-radius:8px;border:1px solid #1e293b;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#1e293b;color:#94a3b8;">
                            <th style="padding:10px 12px;text-align:left;">Router</th>
                            <th style="padding:10px 12px;text-align:left;">Username</th>
                            <th style="padding:10px 12px;text-align:left;">Customer</th>
                            <th style="padding:10px 12px;text-align:left;">Phone</th>
                            <th style="padding:10px 12px;text-align:left;">IP</th>
                            <th style="padding:10px 12px;text-align:left;">MAC</th>
                            <th style="padding:10px 12px;text-align:left;">Uptime</th>
                            <th style="padding:10px 12px;text-align:left;">↓ RX</th>
                            <th style="padding:10px 12px;text-align:left;">↑ TX</th>
                            <th style="padding:10px 12px;text-align:center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr style="border-bottom:1px solid #1e293b;transition:background 0.2s;" onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='transparent'">
                            <td style="padding:10px 12px;color:#6366f1;">{{ $user['device_name'] }}</td>
                            <td style="padding:10px 12px;font-weight:600;color:#e2e8f0;">{{ $user['username'] }}</td>
                            <td style="padding:10px 12px;color:{{ $user['customer'] === 'Unknown' ? '#64748b' : '#e2e8f0' }};">
                                {{ $user['customer'] }}
                            </td>
                            <td style="padding:10px 12px;color:#94a3b8;">{{ $user['phone'] }}</td>
                            <td style="padding:10px 12px;color:#10b981;font-family:monospace;">{{ $user['ip'] }}</td>
                            <td style="padding:10px 12px;color:#64748b;font-family:monospace;font-size:11px;">{{ $user['mac'] }}</td>
                            <td style="padding:10px 12px;color:#f59e0b;">{{ $user['uptime'] }}</td>
                            <td style="padding:10px 12px;color:#22c55e;">{{ $user['rx'] }}</td>
                            <td style="padding:10px 12px;color:#f97316;">{{ $user['tx'] }}</td>
                            <td style="padding:10px 12px;text-align:center;">
                                <div style="display:flex;gap:6px;justify-content:center;">
                                    {{-- Ping --}}
                                    @if($user['ip'] !== '—')
                                    <button
                                        wire:click="ping('{{ $user['ip'] }}', '{{ $user['username'] }}')"
                                        style="padding:4px 8px;border-radius:6px;background:#1d4ed830;color:#60a5fa;border:1px solid #1d4ed8;font-size:11px;cursor:pointer;"
                                        title="Ping">
                                        📡 Ping
                                    </button>
                                    @endif
                                    {{-- Disconnect --}}
                                    <button
                                        wire:click="disconnect('{{ $user['id'] }}', {{ $user['device_id'] }})"
                                        wire:confirm="'{{ $user['username'] }}' কে disconnect করবেন?"
                                        style="padding:4px 8px;border-radius:6px;background:#ef444430;color:#ef4444;border:1px solid #ef4444;font-size:11px;cursor:pointer;"
                                        title="Disconnect">
                                        ✕ Kick
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
