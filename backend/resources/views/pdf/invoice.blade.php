<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 13px; color: #333; }
.container { padding: 30px; }
.header { border-bottom: 2px solid #0ea5e9; padding-bottom: 20px; margin-bottom: 20px; }
.company-name { font-size: 24px; font-weight: bold; color: #0ea5e9; }
.company-info { font-size: 12px; color: #666; margin-top: 5px; }
.invoice-title { font-size: 28px; font-weight: bold; color: #333; }
.badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
.badge-unpaid { background: #fee2e2; color: #ef4444; }
.badge-paid { background: #dcfce7; color: #22c55e; }
.badge-partial { background: #fef9c3; color: #f59e0b; }
.info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
.info-row { margin-bottom: 5px; }
.info-label { color: #666; display: inline-block; width: 80px; }
.info-value { font-weight: bold; }
table { width: 100%; border-collapse: collapse; margin: 20px 0; }
th { background: #0ea5e9; color: white; padding: 10px; text-align: left; }
td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
.total-label { color: #666; display: inline-block; width: 150px; }
.total-value { font-weight: bold; }
.footer { margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center; color: #666; font-size: 11px; }
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="company-name">{{ $company['name'] }}</div>
        <div class="company-info">
            @if($company['phone']) Phone: {{ $company['phone'] }} @endif
            @if($company['email']) | Email: {{ $company['email'] }} @endif
            @if($company['address']) | {{ $company['address'] }} @endif
        </div>
        <div style="margin-top:10px">
            <span class="invoice-title">INVOICE</span>
            <span class="badge badge-{{ $invoice->status }}" style="margin-left:10px">{{ strtoupper($invoice->status) }}</span>
        </div>
        <div style="color:#666; font-size:12px; margin-top:5px">
            No: <strong>{{ $invoice->invoice_no }}</strong> |
            Issue: {{ $invoice->issue_date }} |
            Due: {{ $invoice->due_date }}
        </div>
    </div>

    <div class="info-box">
        <div style="font-size:11px; text-transform:uppercase; color:#666; margin-bottom:8px">Customer Info</div>
        <div class="info-row"><span class="info-label">Name:</span><span class="info-value">{{ $invoice->customer->name }}</span></div>
        <div class="info-row"><span class="info-label">Phone:</span><span class="info-value">{{ $invoice->customer->phone }}</span></div>
        <div class="info-row"><span class="info-label">Username:</span><span class="info-value">{{ $invoice->customer->username }}</span></div>
        @if($invoice->customer->address)
        <div class="info-row"><span class="info-label">Address:</span><span class="info-value">{{ $invoice->customer->address }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">Package:</span><span class="info-value">{{ $invoice->package->name }} ({{ $invoice->package->speed_download }}/{{ $invoice->package->speed_upload }} Mbps)</span></div>
        <div class="info-row"><span class="info-label">Expire:</span><span class="info-value">{{ $invoice->customer->expire_date }}</span></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Period</th>
                <th style="text-align:right">Amount (BDT)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $invoice->package->name }} Internet Service</td>
                <td>{{ $invoice->issue_date }} to {{ $invoice->due_date }}</td>
                <td style="text-align:right">{{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div style="text-align:right; margin-top:10px">
        <div class="info-row"><span class="total-label">Subtotal:</span><span class="total-value">{{ number_format($invoice->amount, 2) }} BDT</span></div>
        @if($invoice->discount > 0)
        <div class="info-row"><span class="total-label">Discount:</span><span class="total-value">- {{ number_format($invoice->discount, 2) }} BDT</span></div>
        @endif
        <div class="info-row" style="font-size:16px; color:#0ea5e9; border-top:2px solid #0ea5e9; padding-top:8px; margin-top:8px">
            <span class="total-label"><strong>Total:</strong></span>
            <span class="total-value"><strong>{{ number_format($invoice->total, 2) }} BDT</strong></span>
        </div>
        @if($invoice->payments->count() > 0)
        <div class="info-row" style="color:#22c55e"><span class="total-label">Paid:</span><span class="total-value">{{ number_format($invoice->payments->sum('amount'), 2) }} BDT</span></div>
        <div class="info-row" style="color:#ef4444"><span class="total-label">Due:</span><span class="total-value">{{ number_format($invoice->total - $invoice->payments->sum('amount'), 2) }} BDT</span></div>
        @endif
    </div>

    @if($invoice->payments->count() > 0)
    <div style="margin-top:20px">
        <div style="font-size:11px; text-transform:uppercase; color:#666; margin-bottom:8px">Payment History</div>
        <table>
            <thead><tr><th>Date</th><th>Method</th><th>Transaction ID</th><th style="text-align:right">Amount</th></tr></thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at }}</td>
                    <td>{{ strtoupper($payment->method) }}</td>
                    <td>{{ $payment->transaction_id ?? '-' }}</td>
                    <td style="text-align:right">{{ number_format($payment->amount, 2) }} BDT</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business! | {{ $company['name'] }} | {{ $company['phone'] }}</p>
        <p style="margin-top:5px">Generated: {{ now()->format('d M Y h:i A') }}</p>
    </div>
</div>
</body>
</html>
