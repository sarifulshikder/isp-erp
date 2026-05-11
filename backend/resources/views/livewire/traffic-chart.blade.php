<div>
@if($visible)
<div style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:24px;width:92%;max-width:580px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div style="font-weight:700;font-size:15px;color:#111;">📊 Live Traffic — {{ $username }}</div>
            <button wire:click="hide" style="background:none;border:none;cursor:pointer;font-size:22px;color:#6b7280;">✕</button>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
            <div style="background:#dcfce7;border-radius:8px;padding:14px;text-align:center;">
                <div style="color:#059669;font-size:28px;font-weight:700;">{{ number_format($latest['rx'], 3) }}</div>
                <div style="color:#059669;font-size:12px;">Mbps ↓ Download</div>
            </div>
            <div style="background:#ffedd5;border-radius:8px;padding:14px;text-align:center;">
                <div style="color:#ea580c;font-size:28px;font-weight:700;">{{ number_format($latest['tx'], 3) }}</div>
                <div style="color:#ea580c;font-size:12px;">Mbps ↑ Upload</div>
            </div>
        </div>
        {{-- Graph with wire:ignore so Livewire doesn't touch it --}}
        <div wire:ignore style="height:200px;margin-bottom:8px;position:relative;">
            <canvas id="tcCanvas" style="width:100%;height:100%;"></canvas>
        </div>
        {{-- Hidden data for JS to read --}}
        <div id="tcDataStore" data-points='@json($chartPoints ?? [])' style="display:none;"></div>
        <div style="color:#9ca3af;font-size:11px;text-align:center;margin-bottom:14px;">⟳ প্রতি ১০ সেকেন্ডে auto-update</div>
        <div style="display:flex;justify-content:flex-end;">
            <button wire:click="hide" style="padding:8px 20px;border-radius:8px;background:#6b7280;color:white;border:none;cursor:pointer;font-weight:600;">বন্ধ করুন</button>
        </div>
    </div>
</div>
<div wire:poll.10s="refresh"></div>
<script>
(function() {
    function renderChart() {
        var store = document.getElementById('tcDataStore');
        var canvas = document.getElementById('tcCanvas');
        if (!store || !canvas) { setTimeout(renderChart, 100); return; }
        var points = JSON.parse(store.getAttribute('data-points') || '[]');
        if (points.length === 0) { setTimeout(renderChart, 500); return; }
        if (window._tc) {
            window._tc.data.labels = points.map(function(p){ return p.time; });
            window._tc.data.datasets[0].data = points.map(function(p){ return p.rx; });
            window._tc.data.datasets[1].data = points.map(function(p){ return p.tx; });
            window._tc.update('none');
            return;
        }
        window._tc = new Chart(canvas, {
            type: 'line',
            data: {
                labels: points.map(function(p){ return p.time; }),
                datasets: [
                    { label: '↓ Download (Mbps)', data: points.map(function(p){ return p.rx; }), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', fill: true, tension: 0.4, pointRadius: 3 },
                    { label: '↑ Upload (Mbps)', data: points.map(function(p){ return p.tx; }), borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.15)', fill: true, tension: 0.4, pointRadius: 3 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false, animation: { duration: 300 },
                plugins: { legend: { labels: { font: { size: 11 }, usePointStyle: true } } },
                scales: {
                    x: { ticks: { font: { size: 10 }, maxTicksLimit: 8 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: 'rgba(0,0,0,0.05)' } }
                }
            }
        });
    }
    renderChart();
    document.addEventListener('livewire:update', function() {
        setTimeout(function() {
            var store = document.getElementById('tcDataStore');
            if (!store || !window._tc) return;
            var points = JSON.parse(store.getAttribute('data-points') || '[]');
            if (points.length === 0) return;
            window._tc.data.labels = points.map(function(p){ return p.time; });
            window._tc.data.datasets[0].data = points.map(function(p){ return p.rx; });
            window._tc.data.datasets[1].data = points.map(function(p){ return p.tx; });
            window._tc.update('none');
        }, 50);
    });
})();
</script>
@else
<script>
if (window._tc) { try { window._tc.destroy(); } catch(e){} window._tc = null; }
</script>
@endif
</div>
