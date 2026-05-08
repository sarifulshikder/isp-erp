<x-filament-panels::page>
    <div class="flex flex-col gap-4">
        <div class="flex justify-between items-center">
            <p class="text-gray-500 dark:text-gray-400">Snipe-IT Inventory System এ সরাসরি access করুন</p>
            <a href="http://103.164.50.8:8081" target="_blank"
               class="fi-btn fi-btn-color-primary fi-color-custom fi-btn-size-md inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold bg-primary-600 text-white hover:bg-primary-500">
                🔗 Snipe-IT খুলুন (নতুন Tab এ)
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="fi-wi-stats-overview-stat rounded-xl bg-white shadow dark:bg-gray-900 p-6">
                <div class="text-sm text-gray-500">Total Assets</div>
                <div class="text-3xl font-bold text-primary-600">
                    {{ app(\App\Services\SnipeItService::class)->getAssets()['total'] ?? 0 }}
                </div>
            </div>
            <div class="fi-wi-stats-overview-stat rounded-xl bg-white shadow dark:bg-gray-900 p-6">
                <div class="text-sm text-gray-500">Categories</div>
                <div class="text-3xl font-bold text-success-600">
                    {{ count(app(\App\Services\SnipeItService::class)->getCategories()) }}
                </div>
            </div>
            <div class="fi-wi-stats-overview-stat rounded-xl bg-white shadow dark:bg-gray-900 p-6">
                <div class="text-sm text-gray-500">URL</div>
                <div class="text-sm font-mono text-gray-600 dark:text-gray-300 mt-2">
                    103.164.50.8:8081
                </div>
            </div>
        </div>

        {{-- Recent Assets Table --}}
        <div class="fi-ta rounded-xl bg-white shadow dark:bg-gray-900 overflow-hidden">
            <div class="p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold">সাম্প্রতিক Assets</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left">Asset Tag</th>
                        <th class="px-4 py-3 text-left">নাম</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Assigned To</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @php
                        $assets = app(\App\Services\SnipeItService::class)->getAssets(20)['rows'] ?? [];
                    @endphp
                    @forelse($assets as $asset)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-4 py-3 font-mono">{{ $asset['asset_tag'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $asset['name'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                {{ $asset['category']['name'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs
                                {{ ($asset['status_label']['name'] ?? '') === 'Deployable' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $asset['status_label']['name'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $asset['assigned_to']['name'] ?? 'Unassigned' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400">কোনো asset নেই</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
