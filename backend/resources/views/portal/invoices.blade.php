<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - ইনভয়েস</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- Navbar -->
    <nav class="bg-blue-700 text-white px-6 py-4 flex justify-between items-center shadow">
        <div class="font-bold text-lg">🌐 Customer Portal</div>
        <div class="flex items-center gap-4">
            <a href="{{ route('portal.dashboard') }}" class="text-sm hover:underline">← ড্যাশবোর্ড</a>
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button class="bg-white text-blue-700 text-sm px-3 py-1 rounded-lg font-medium hover:bg-gray-100">
                    লগআউট
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6">🧾 সকল ইনভয়েস</h1>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            @forelse($invoices as $invoice)
                <div class="flex justify-between items-center px-6 py-4 border-b last:border-0 hover:bg-gray-50">
                    <div>
                        <div class="font-medium text-gray-800">#{{ $invoice->id }}</div>
                        <div class="text-xs text-gray-400">{{ $invoice->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <div class="font-bold text-gray-800">৳{{ $invoice->amount }}</div>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                @if($invoice->status === 'paid') bg-green-100 text-green-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ $invoice->status === 'paid' ? 'পরিশোধিত' : 'বাকি' }}
                            </span>
                        </div>
                        <a href="{{ route('portal.invoices.show', $invoice->id) }}"
                            class="text-blue-600 text-sm hover:underline">বিস্তারিত</a>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-400 py-12">কোনো ইনভয়েস পাওয়া যায়নি</div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    </div>
</body>
</html>
