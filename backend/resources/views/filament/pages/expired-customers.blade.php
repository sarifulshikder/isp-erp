<x-filament-panels::page>
    @php $customers = $this->getViewData()['customers']; @endphp
    @if($customers->isEmpty())
        <div style="text-align:center;padding:40px;color:#64748b;">
            <x-heroicon-o-check-circle style="width:48px;height:48px;margin:0 auto 12px;color:#22c55e;" />
            <p>কোনো expired customer নেই।</p>
        </div>
    @else
        <div style="margin-bottom:16px;font-weight:600;color:#ef4444;">
            {{ $customers->count() }} জন customer expire হয়ে গেছে
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="background:#1e293b;color:#94a3b8;">
                        <th style="padding:10px;text-align:left;">নাম</th>
                        <th style="padding:10px;text-align:left;">Phone</th>
                        <th style="padding:10px;text-align:left;">Package</th>
                        <th style="padding:10px;text-align:left;">Zone</th>
                        <th style="padding:10px;text-align:left;">Expire Date</th>
                        <th style="padding:10px;text-align:left;">Status</th>
                        <th style="padding:10px;text-align:left;">কতদিন আগে</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    @php $days = $customer->expire_date->diffInDays(today()); @endphp
                    <tr style="border-bottom:1px solid #1e293b;">
                        <td style="padding:10px;font-weight:600;color:#e2e8f0;">{{ $customer->name }}</td>
                        <td style="padding:10px;color:#94a3b8;">{{ $customer->phone }}</td>
                        <td style="padding:10px;color:#6366f1;">{{ $customer->package?->name ?? '—' }}</td>
                        <td style="padding:10px;color:#94a3b8;">{{ $customer->zone?->name ?? '—' }}</td>
                        <td style="padding:10px;color:#ef4444;">{{ $customer->expire_date->format('d M Y') }}</td>
                        <td style="padding:10px;">
                            <span style="padding:3px 10px;border-radius:20px;font-size:11px;background:{{ $customer->status === 'suspended' ? '#ef444430' : '#f59e0b30' }};color:{{ $customer->status === 'suspended' ? '#ef4444' : '#f59e0b' }};">
                                {{ strtoupper($customer->status) }}
                            </span>
                        </td>
                        <td style="padding:10px;color:#64748b;">{{ $days }} দিন আগে</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
