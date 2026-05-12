<x-filament-widgets::widget>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">

    {{-- Registration History --}}
    <div style="border-radius:10px;overflow:hidden;border:1px solid var(--fi-border);">
        <div style="background:#6366f1;padding:12px 16px;font-weight:700;color:white;font-size:14px;">
            👥 Registration History
        </div>
        <div style="background:var(--fi-bg);">
            @foreach([
                ['label' => 'Registered Today',       'value' => $registration['today']],
                ['label' => 'Registered Yesterday',   'value' => $registration['yesterday']],
                ['label' => 'Registered This Month',  'value' => $registration['this_month']],
                ['label' => 'Registered Last Month',  'value' => $registration['last_month']],
                ['label' => 'Total Customers',        'value' => $registration['total']],
            ] as $row)
            <div style="display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--fi-border);font-size:13px;">
                <span style="color:var(--fi-muted);">{{ $row['label'] }}</span>
                <span style="font-weight:700;color:#6366f1;">{{ $row['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Expiration History --}}
    <div style="border-radius:10px;overflow:hidden;border:1px solid var(--fi-border);">
        <div style="background:#f59e0b;padding:12px 16px;font-weight:700;color:white;font-size:14px;">
            ⏰ Expiration History
        </div>
        <div style="background:var(--fi-bg);">
            @foreach([
                ['label' => 'Expiring Today',         'value' => $expiration['today']],
                ['label' => 'Expired Yesterday',      'value' => $expiration['yesterday']],
                ['label' => 'Expiring Tomorrow',      'value' => $expiration['tomorrow']],
                ['label' => 'Expiring in 7 Days',     'value' => $expiration['next_7_days']],
                ['label' => 'Expired Last 7 Days',    'value' => $expiration['expired_7days']],
            ] as $row)
            <div style="display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--fi-border);font-size:13px;">
                <span style="color:var(--fi-muted);">{{ $row['label'] }}</span>
                <span style="font-weight:700;color:#f59e0b;">{{ $row['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Invoice History --}}
    <div style="border-radius:10px;overflow:hidden;border:1px solid var(--fi-border);">
        <div style="background:#10b981;padding:12px 16px;font-weight:700;color:white;font-size:14px;">
            🧾 Invoice History
        </div>
        <div style="background:var(--fi-bg);">
            @foreach([
                ['label' => 'Pending Invoices',    'value' => $invoice['pending']],
                ['label' => 'Paid Today',          'value' => $invoice['today']],
                ['label' => 'Paid Yesterday',      'value' => $invoice['yesterday']],
                ['label' => 'Paid This Month',     'value' => $invoice['this_month']],
                ['label' => 'Total Unpaid Amount', 'value' => $invoice['total_unpaid']],
            ] as $row)
            <div style="display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--fi-border);font-size:13px;">
                <span style="color:var(--fi-muted);">{{ $row['label'] }}</span>
                <span style="font-weight:700;color:#10b981;">{{ $row['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Collection History --}}
    <div style="border-radius:10px;overflow:hidden;border:1px solid var(--fi-border);">
        <div style="background:#3b82f6;padding:12px 16px;font-weight:700;color:white;font-size:14px;">
            💰 Collection History
        </div>
        <div style="background:var(--fi-bg);">
            @foreach([
                ['label' => 'Today',      'value' => $collection['today']],
                ['label' => 'Yesterday',  'value' => $collection['yesterday']],
                ['label' => 'This Month', 'value' => $collection['this_month']],
                ['label' => 'Last Month', 'value' => $collection['last_month']],
                ['label' => 'Total',      'value' => $collection['total']],
            ] as $row)
            <div style="display:flex;justify-content:space-between;padding:10px 16px;border-bottom:1px solid var(--fi-border);font-size:13px;">
                <span style="color:var(--fi-muted);">{{ $row['label'] }}</span>
                <span style="font-weight:700;color:#3b82f6;">{{ $row['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>
</x-filament-widgets::widget>
