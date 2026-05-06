<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইনভয়েস বিস্তারিত</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-blue-700 text-white px-6 py-4 flex justify-between items-center shadow">
        <div class="font-bold text-lg">🌐 Customer Portal</div>
        <a href="{{ route('portal.invoices.index') }}" class="text-sm hover:underline">← ইনভয়েস তালিকা</a>
    </nav>

    <div class="max-w-2xl mx-auto px-4 py-8">

        {{-- Success/Error messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">ইনভয়েস #{{ $invoice->id }}</h1>
                    <p class="text-gray-400 text-sm">{{ $invoice->created_at->format('d M Y') }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium
                    @if($invoice->status === 'paid') bg-green-100 text-green-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ $invoice->status === 'paid' ? '✅ পরিশোধিত' : '⏳ বাকি' }}
                </span>
            </div>

            <div class="border-t border-b py-4 mb-6">
                <div class="flex justify-between text-sm py-2">
                    <span class="text-gray-500">বিবরণ</span>
                    <span class="font-medium">{{ $invoice->description ?? 'মাসিক বিল' }}</span>
                </div>
                <div class="flex justify-between text-sm py-2">
                    <span class="text-gray-500">মেয়াদ</span>
                    <span class="font-medium">{{ $invoice->due_date ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-sm py-2 font-bold text-lg">
                    <span>মোট</span>
                    <span class="text-blue-700">৳{{ number_format($invoice->amount, 2) }}</span>
                </div>
            </div>

            @if($invoice->status === 'paid')
                {{-- Paid info --}}
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-700">
                    ✅ এই ইনভয়েসটি পরিশোধ করা হয়েছে।
                    @if($invoice->paid_at)
                        <span class="block mt-1 text-green-500">তারিখ: {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y, h:i A') }}</span>
                    @endif
                </div>
            @else
                {{-- Payment options --}}
                <div class="space-y-3">
                    <p class="text-sm text-gray-500 font-medium">পেমেন্ট পদ্ধতি বেছে নিন:</p>

                    {{-- bKash Button --}}
                    <a href="{{ route('portal.bkash.initiate', $invoice->id) }}"
                       onclick="return confirm('bKash দিয়ে ৳{{ $invoice->amount }} পরিশোধ করবেন?')"
                       class="flex items-center justify-center gap-3 w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-xl transition duration-200 shadow">
                        <img src="https://www.bkash.com/sites/default/files/bkash-logo.png"
                             alt="bKash" class="h-6"
                             onerror="this.style.display='none'">
                        <span>bKash দিয়ে পেমেন্ট করুন</span>
                    </a>

                    {{-- Nagad (coming soon) --}}
                    <button disabled
                        class="flex items-center justify-center gap-3 w-full bg-gray-100 text-gray-400 font-bold py-3 px-6 rounded-xl cursor-not-allowed">
                        <span>🟠 Nagad — শীঘ্রই আসছে</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
