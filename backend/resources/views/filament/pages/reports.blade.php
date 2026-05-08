<x-filament-panels::page>
<div class="space-y-6">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-5">
            <div class="text-sm text-gray-500">এই মাসের Collection</div>
            <div class="text-2xl font-bold text-green-600 mt-1">৳ {{ number_format($thisMonthCollection, 2) }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-5">
            <div class="text-sm text-gray-500">মোট বাকি (Unpaid)</div>
            <div class="text-2xl font-bold text-red-600 mt-1">৳ {{ number_format($totalDue, 2) }}</div>
        </div>
        <div class="rounded-xl bg-white dark:bg-gray-900 shadow p-5">
            <div class="text-sm text-gray-500">Unpaid Invoice সংখ্যা</div>
            <div class="text-2xl font-bold text-orange-600 mt-1">{{ $totalUnpaid }}</div>
        </div>
    </div>

    {{-- Monthly Collection --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow overflow-hidden">
        <div class="p-4 border-b dark:border-gray-700 font-semibold text-lg">📅 মাসিক Collection (শেষ ৬ মাস)</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left">মাস</th>
                    <th class="px-4 py-3 text-right">Payment সংখ্যা</th>
                    <th class="px-4 py-3 text-right">মোট Collection</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @foreach($monthlyCollection as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 font-medium">{{ $row['month'] }}</td>
                    <td class="px-4 py-3 text-right">{{ $row['count'] }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-green-600">৳ {{ number_format($row['amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Zone wise Report --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow overflow-hidden">
        <div class="p-4 border-b dark:border-gray-700 font-semibold text-lg">🗺️ Zone wise Report</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left">Zone</th>
                    <th class="px-4 py-3 text-left">এলাকা</th>
                    <th class="px-4 py-3 text-right">Customers</th>
                    <th class="px-4 py-3 text-right">এই মাস</th>
                    <th class="px-4 py-3 text-right">মোট Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($zoneReport as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 font-medium">{{ $row['name'] }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $row['area'] ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">{{ $row['customers'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-green-600 font-semibold">৳ {{ number_format($row['monthly'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-semibold">৳ {{ number_format($row['total'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">কোনো Zone নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Package wise Report --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow overflow-hidden">
        <div class="p-4 border-b dark:border-gray-700 font-semibold text-lg">📦 Package wise Report</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left">Package</th>
                    <th class="px-4 py-3 text-right">মূল্য</th>
                    <th class="px-4 py-3 text-right">Customers</th>
                    <th class="px-4 py-3 text-right">এই মাস Collection</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($packageReport as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 font-medium">{{ $row['name'] }}</td>
                    <td class="px-4 py-3 text-right">৳ {{ number_format($row['price'], 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="px-2 py-1 rounded-full text-xs bg-purple-100 text-purple-700">{{ $row['customers'] }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-green-600 font-semibold">৳ {{ number_format($row['monthly'], 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">কোনো Package নেই</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Due/Unpaid Report --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow overflow-hidden">
        <div class="p-4 border-b dark:border-gray-700 font-semibold text-lg">⚠️ Due / Unpaid Report</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Zone</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Due Date</th>
                    <th class="px-4 py-3 text-right">Overdue Days</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($dueReport as $row)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-4 py-3 font-medium">{{ $row['customer'] }}</td>
                    <td class="px-4 py-3">{{ $row['phone'] }}</td>
                    <td class="px-4 py-3">{{ $row['zone'] }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-red-600">৳ {{ number_format($row['amount'], 2) }}</td>
                    <td class="px-4 py-3 text-right">{{ $row['due_date'] ? \Carbon\Carbon::parse($row['due_date'])->format('d M Y') : '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        @if($row['days_overdue'] > 0)
                            <span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">{{ $row['days_overdue'] }} days</span>
                        @else
                            <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Not due</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">কোনো বাকি নেই 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
</x-filament-panels::page>
