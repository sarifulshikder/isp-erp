<div>
@if($visible)
<div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:24px;width:92%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div style="font-weight:700;font-size:16px;color:#111;">📊 Live Traffic — {{ $username }}</div>
            <button wire:click="hide" style="background:none;border:none;cursor:pointer;font-size:22px;color:#6b7280;">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div style="background:#dcfce7;border-radius:10px;padding:20px;text-align:center;">
                <div style="color:#059669;font-size:32px;font-weight:800;">{{ number_format($latest['rx'], 3) }}</div>
                <div style="color:#059669;font-size:13px;margin-top:4px;font-weight:600;">Mbps ↓ Download</div>
            </div>
            <div style="background:#ffedd5;border-radius:10px;padding:20px;text-align:center;">
                <div style="color:#ea580c;font-size:32px;font-weight:800;">{{ number_format($latest['tx'], 3) }}</div>
                <div style="color:#ea580c;font-size:13px;margin-top:4px;font-weight:600;">Mbps ↑ Upload</div>
            </div>
        </div>
        {{-- History --}}
        @if(count($chartPoints) > 1)
        <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:16px;max-height:120px;overflow-y:auto;">
            @foreach(array_reverse($chartPoints) as $p)
            <div style="display:flex;justify-content:space-between;font-size:12px;padding:3px 0;border-bottom:1px solid #e2e8f0;color:#374151;">
                <span style="color:#9ca3af;">{{ $p['time'] }}</span>
                <span style="color:#059669;">↓ {{ number_format($p['rx'], 3) }} Mbps</span>
                <span style="color:#ea580c;">↑ {{ number_format($p['tx'], 3) }} Mbps</span>
            </div>
            @endforeach
        </div>
        @endif
        <div style="color:#9ca3af;font-size:11px;text-align:center;margin-bottom:14px;">⟳ প্রতি ১ সেকেন্ডে auto-update</div>
        <div style="display:flex;justify-content:flex-end;">
            <button wire:click="hide" style="padding:10px 24px;border-radius:8px;background:#6b7280;color:white;border:none;cursor:pointer;font-weight:600;font-size:14px;">বন্ধ করুন</button>
        </div>
    </div>
</div>
<div wire:poll.1s="refresh"></div>
@else
@endif
</div>
