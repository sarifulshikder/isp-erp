<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-blue-700 text-white px-6 py-4 flex justify-between items-center shadow">
        <div class="font-bold text-lg">🌐 Customer Portal</div>
        <div class="flex items-center gap-4">
            <span class="text-sm">👤 {{ $customer->name }}</span>
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button class="bg-white text-blue-700 text-sm px-3 py-1 rounded-lg font-medium hover:bg-gray-100">
                    লগআউট
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">

        <!-- Status Banner -->
        @if($daysLeft !== null && $daysLeft <= 7)
            <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg p-4 mb-6 text-sm">
                ⚠️ আপনার সংযোগ
                @if($daysLeft < 0) মেয়াদ শেষ হয়ে গেছে!
                @elseif($daysLeft == 0) আজই শেষ হচ্ছে!
                @else {{ $daysLeft }} দিনের মধ্যে শেষ হবে।
                @endif
                দ্রুত পেমেন্ট করুন।
            </div>
        @endif

        <!-- Info Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl mb-1">📦</div>
                <div class="text-xs text-gray-500">প্যাকেজ</div>
                <div class="font-bold text-gray-800 text-sm mt-1">{{ $customer->package->name ?? 'N/A' }}</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl mb-1">💰</div>
                <div class="text-xs text-gray-500">মাসিক বিল</div>
                <div class="font-bold text-gray-800 text-sm mt-1">৳{{ $customer->package->price ?? '0' }}</div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl mb-1">📅</div>
                <div class="text-xs text-gray-500">মেয়াদ শেষ</div>
                <div class="font-bold text-gray-800 text-sm mt-1">
                    {{ $customer->expire_date ? $customer->expire_date->format('d M Y') : 'N/A' }}
                </div>
            </div>
            <div class="bg-white rounded-xl shadow p-4 text-center">
                <div class="text-2xl mb-1">
                    @if($customer->status === 'active') ✅
                    @elseif($customer->status === 'suspended') ❌
                    @else ⏳
                    @endif
                </div>
                <div class="text-xs text-gray-500">অবস্থা</div>
                <div class="font-bold text-sm mt-1
                    @if($customer->status === 'active') text-green-600
                    @elseif($customer->status === 'suspended') text-red-600
                    @else text-yellow-600 @endif">
                    @if($customer->status === 'active') সক্রিয়
                    @elseif($customer->status === 'suspended') স্থগিত
                    @else নিষ্ক্রিয় @endif
                </div>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-gray-800">🧾 সাম্প্রতিক ইনভয়েস</h2>
                <a href="{{ route('portal.invoices') }}" class="text-blue-600 text-sm hover:underline">সব দেখুন →</a>
            </div>
            @forelse($recentInvoices as $invoice)
                <div class="flex justify-between items-center py-3 border-b last:border-0">
                    <div>
                        <div class="text-sm font-medium text-gray-800">#{{ $invoice->id }}</div>
                        <div class="text-xs text-gray-400">{{ $invoice->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-gray-800">৳{{ $invoice->amount }}</div>
                        <span class="text-xs px-2 py-0.5 rounded-full
                            @if($invoice->status === 'paid') bg-green-100 text-green-700
                            @else bg-yellow-100 text-yellow-700 @endif">
                            {{ $invoice->status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-gray-400 text-sm text-center py-4">কোনো ইনভয়েস নেই</p>
            @endforelse
        </div>

        <!-- Account Info -->
        <div class="bg-white rounded-xl shadow p-6">
            <h2 class="font-bold text-gray-800 mb-4">👤 অ্যাকাউন্ট তথ্য</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-gray-500">নাম:</span> <span class="font-medium">{{ $customer->name }}</span></div>
                <div><span class="text-gray-500">ফোন:</span> <span class="font-medium">{{ $customer->phone }}</span></div>
                <div><span class="text-gray-500">Username:</span> <span class="font-medium">{{ $customer->username }}</span></div>
                <div><span class="text-gray-500">সংযোগ তারিখ:</span> <span class="font-medium">{{ $customer->connection_date ? $customer->connection_date->format('d M Y') : 'N/A' }}</span></div>
            </div>
        </div>

    </div>
</body>
</html>
