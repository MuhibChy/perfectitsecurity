<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Wallet Statement</title>
<style>body{font-family:sans-serif;font-size:12px;color:#111}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:6px;text-align:left}th{background:#f1f5f9}.num{text-align:right}.head{margin-bottom:16px}</style>
</head><body>
<div class="head">
    <h2>FWallet Statement — {{ $wallet->wallet_reference }}</h2>
    <p>Currency {{ $wallet->currency }} · Status {{ ucfirst($wallet->status) }} · Generated {{ now()->format('d M Y H:i') }}</p>
</div>
<table>
    <thead><tr><th>Date</th><th>Reference</th><th>Type</th><th>Description</th><th class="num">Credit</th><th class="num">Debit</th><th class="num">Balance After</th><th>Status</th></tr></thead>
    <tbody>
        @foreach($transactions as $t)
        <tr>
            <td>{{ $t->created_at->format('d M Y H:i') }}</td>
            <td>{{ $t->transaction_reference }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td>
            <td>{{ $t->description ?? '—' }}</td>
            <td class="num">{{ $t->isCredit() ? number_format($t->amount, 2) : '' }}</td>
            <td class="num">{{ $t->isCredit() ? '' : number_format($t->amount, 2) }}</td>
            <td class="num">{{ number_format($t->balance_after, 2) }}</td>
            <td>{{ ucfirst($t->status) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
</body></html>
