@extends('layouts.app')
@section('title', 'Detail Kredit')

@section('content')

<style>
.kredit-wrap   { max-width: 920px; margin: 28px auto; padding: 0 12px; font-family: Arial, sans-serif; font-size: 13px; }
.k-card        { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 18px; }
.k-card-title  { font-size: 15px; font-weight: 700; color: #1f2937; margin-bottom: 14px; display: flex; align-items: center; gap: 7px; }
.k-row         { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; font-size: 13px; gap: 10px; }
.k-row strong  { color: #374151; flex-shrink: 0; }
.k-row span    { text-align: right; color: #374151; }
.k-divider     { border: none; border-top: 1px solid #e5e7eb; margin: 12px 0; }

/* Status badges */
.badge-kredit { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
.badge-paid   { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }

/* Summary grid */
.summary-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 6px; }
.s-box        { border-radius: 8px; padding: 10px 14px; text-align: center; }
.s-box .s-label { font-size: 11px; color: #6b7280; margin-bottom: 3px; }
.s-box .s-value { font-size: 16px; font-weight: 800; }
.s-total      { background: #f3f4f6; } .s-total      .s-value { color: #111827; }
.s-bayar      { background: #d1fae5; } .s-bayar      .s-value { color: #065f46; }
.s-sisa       { background: #fee2e2; } .s-sisa       .s-value { color: #dc2626; }
.s-sisa-lunas { background: #d1fae5; } .s-sisa-lunas .s-value { color: #065f46; }

/* DP banner */
.dp-banner {
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    border-radius: 7px;
    padding: 8px 14px;
    font-size: 12px;
    color: #065f46;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
    gap: 10px;
    flex-wrap: wrap;
}
.dp-banner .dp-left  { font-weight: 600; }
.dp-banner .dp-right { font-size: 11px; color: #047857; }

/* Info kredit */
.kredit-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ki-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 7px; padding: 9px 12px; }
.ki-box .ki-label { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.ki-box .ki-value { font-size: 13px; color: #111827; font-weight: 600; }
.ki-box.full      { grid-column: 1 / -1; }

/* Jatuh tempo alert */
.due-alert   { border-radius: 8px; padding: 10px 14px; font-size: 12px; margin-bottom: 6px; display: flex; align-items: center; gap: 8px; }
.due-ok      { background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46; }
.due-soon    { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
.due-overdue { background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; }

/* Cicilan progress */
.cicilan-progress { background: #f3f4f6; border-radius: 20px; height: 8px; overflow: hidden; margin: 6px 0; }
.cicilan-bar      { background: #22c55e; height: 100%; border-radius: 20px; transition: width .4s; }

/* Catatan box */
.catatan-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 7px; padding: 10px 14px; font-size: 12px; color: #78350f; white-space: pre-wrap; line-height: 1.6; }

/* Form */
.form-group       { margin-bottom: 12px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
.form-input       { width: 100%; padding: 8px 10px; border: 1.5px solid #d1d5db; border-radius: 7px; font-size: 13px; outline: none; transition: border-color .15s; }
.form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 2px rgba(59,130,246,.15); }
.form-grid        { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.form-grid-3      { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
.btn-pay          { background: #2563eb; color: #fff; border: none; padding: 10px 20px; border-radius: 7px; font-size: 13px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 4px; transition: background .15s; }
.btn-pay:hover    { background: #1d4ed8; }
.btn-lunasi       { background: #16a34a; color: #fff; border: none; padding: 10px 20px; border-radius: 7px; font-size: 13px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 8px; transition: background .15s; }
.btn-lunasi:hover { background: #15803d; }

/* Saran bayar */
.cicilan-hint { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 7px 12px; font-size: 12px; color: #1e40af; margin-bottom: 8px; }

/* Riwayat */
.history-table { width: 100%; border-collapse: collapse; }
.history-table th { padding: 9px 12px; text-align: left; background: #f9fafb; font-size: 12px; color: #6b7280; font-weight: 600; border-bottom: 1px solid #e5e7eb; }
.history-table td { padding: 9px 12px; font-size: 13px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.history-table tr:last-child td { border-bottom: none; }
.method-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.m-cash     { background: #d1fae5; color: #065f46; }
.m-transfer { background: #dbeafe; color: #1e40af; }
.m-qris     { background: #ede9fe; color: #5b21b6; }

/* Baris DP di riwayat */
.row-dp td { background: #f0fdf4 !important; }

/* Alert */
.alert { padding: 11px 16px; border-radius: 7px; margin-bottom: 16px; font-size: 13px; }
.alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.alert-error   { background: #fee2e2; color: #7f1d1d; border: 1px solid #fca5a5; }

/* Print */
@media print { .no-print { display: none !important; } }
</style>

<div class="kredit-wrap">

    {{-- PAGE TITLE --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
        <div>
            <h2 style="margin:0; font-size:20px; color:#111827;">📋 Detail Kredit / Hutang</h2>
            <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">{{ $trx->trx_number }}</p>
        </div>
        <div style="display:flex; gap:8px;" class="no-print">
            <a href="{{ route('pos') }}"
               style="background:#f3f4f6;color:#374151;padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                ← Kembali ke POS
            </a>
            <a href="{{ route('print.kredit', $trx->id) }}"
               style="background:#6b7280;color:#fff;padding:7px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;">
                🖨 Print
            </a>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success" id="alert-ok">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif

    {{-- ===== RINGKASAN ===== --}}
    <div class="k-card">
        <div class="k-card-title">
            📄 Ringkasan Transaksi
            @if($trx->status === 'kredit')
                <span class="badge-kredit">💳 Kredit Aktif</span>
            @else
                <span class="badge-paid">✅ Lunas</span>
            @endif
        </div>

        {{-- Summary boxes --}}
        <div class="summary-grid" style="margin-bottom:14px;">
            <div class="s-box s-total">
                <div class="s-label">Total Belanja</div>
                <div class="s-value">Rp {{ number_format($trx->total) }}</div>
            </div>
            <div class="s-box s-bayar">
                <div class="s-label">Sudah Dibayar</div>
                <div class="s-value">Rp {{ number_format($totalTerbayar) }}</div>
            </div>
            <div class="s-box {{ $sisa <= 0 ? 's-sisa-lunas' : 's-sisa' }}">
                <div class="s-label">Sisa Hutang</div>
                <div class="s-value">{{ $sisa <= 0 ? '✅ Lunas' : 'Rp ' . number_format($sisa) }}</div>
            </div>
        </div>

        {{-- Tampilkan info DP jika ada --}}
        @php
            $dpPayment = $trx->payments->firstWhere('note', 'DP / Uang Muka');
        @endphp
        @if($dpPayment)
        <div class="dp-banner">
            <div class="dp-left">
                💵 DP / Uang Muka sudah dibayar:
                <strong>Rp {{ number_format($dpPayment->amount) }}</strong>
                <span class="method-badge m-{{ $dpPayment->method }}" style="margin-left:6px;">
                    {{ ['cash'=>'💵 Cash','transfer'=>'🏦 Transfer','qris'=>'📱 QRIS'][$dpPayment->method] ?? $dpPayment->method }}
                </span>
            </div>
            <div class="dp-right">
                Sisa hutang awal setelah DP:
                <strong>Rp {{ number_format($trx->total - $dpPayment->amount) }}</strong>
            </div>
        </div>
        @endif

        @php
            $pct = $trx->total > 0 ? min(round(($totalTerbayar / $trx->total) * 100), 100) : 0;
        @endphp
        <div style="font-size:11px; color:#6b7280; margin-bottom:4px; margin-top:10px;">
            Progress Pembayaran: <strong>{{ $pct }}%</strong>
        </div>
        <div class="cicilan-progress"><div class="cicilan-bar" style="width:{{ $pct }}%"></div></div>

        <hr class="k-divider">

        {{-- Detail transaksi --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px 20px;">
            <div class="k-row"><strong>No. Transaksi</strong><span>{{ $trx->trx_number }}</span></div>
            <div class="k-row"><strong>Tanggal</strong><span>{{ $trx->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</span></div>
            <div class="k-row"><strong>Member</strong><span>{{ $trx->member->name ?? '-' }}</span></div>
            <div class="k-row"><strong>Diskon</strong><span>Rp {{ number_format($trx->discount ?? 0) }}</span></div>
        </div>
    </div>

    {{-- ===== INFO KREDIT ===== --}}
    @if($trx->debtor_name || $trx->debtor_phone || $trx->due_date || $trx->payment_plan || $trx->kredit_notes)
    <div class="k-card">
        <div class="k-card-title">📋 Info Kredit</div>

        {{-- Jatuh tempo alert --}}
        @if($trx->due_date && $trx->status === 'kredit')
            @php
                $today = now()->startOfDay();
                $due   = \Carbon\Carbon::parse($trx->due_date)->startOfDay();
                $diff  = $today->diffInDays($due, false);
            @endphp
            @if($diff < 0)
                <div class="due-alert due-overdue">🚨 <strong>Jatuh tempo terlewat {{ abs($diff) }} hari!</strong> Segera tagih pembayaran.</div>
            @elseif($diff <= 7)
                <div class="due-alert due-soon">⚠️ <strong>Jatuh tempo dalam {{ $diff }} hari</strong> ({{ $due->locale('id')->translatedFormat('l, d M Y') }})</div>
            @else
                <div class="due-alert due-ok">📅 <strong>Jatuh tempo: {{ $due->locale('id')->translatedFormat('l, d M Y') }}</strong> ({{ $diff }} hari lagi)</div>
            @endif
        @endif

        <div class="kredit-info-grid">
            @if($trx->debtor_name)
            <div class="ki-box">
                <div class="ki-label">👤 Nama Peminjam</div>
                <div class="ki-value">{{ $trx->debtor_name }}</div>
            </div>
            @endif

            @if($trx->debtor_phone)
            <div class="ki-box">
                <div class="ki-label">📱 No. Telepon</div>
                <div class="ki-value">
                    <a href="tel:{{ $trx->debtor_phone }}" style="color:#2563eb; text-decoration:none;">
                        {{ $trx->debtor_phone }}
                    </a>
                    &nbsp;
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', $trx->debtor_phone) }}"
                       target="_blank"
                       style="font-size:11px; background:#22c55e; color:#fff; padding:1px 7px; border-radius:10px; text-decoration:none;">
                        WA
                    </a>
                </div>
            </div>
            @endif

            @if($trx->due_date)
            <div class="ki-box">
                <div class="ki-label">📅 Jatuh Tempo</div>
                <div class="ki-value">{{ \Carbon\Carbon::parse($trx->due_date)->locale('id')->translatedFormat('l, d M Y') }}</div>
            </div>
            @endif

            @if($trx->payment_plan)
            <div class="ki-box">
                <div class="ki-label">💳 Rencana Bayar</div>
                <div class="ki-value">
                    @php $planLabels = ['cash'=>'💵 Cash','transfer'=>'🏦 Transfer','qris'=>'📱 QRIS','cicilan'=>'📆 Cicilan']; @endphp
                    {{ $planLabels[$trx->payment_plan] ?? $trx->payment_plan }}
                    @if($trx->installment_count)
                        @php
                            // Cicilan dihitung dari sisa setelah DP
                            $sisaSetelahDp = $dpPayment ? ($trx->total - $dpPayment->amount) : $trx->total;
                            $perCicilanRencana = ceil($sisaSetelahDp / $trx->installment_count);
                        @endphp
                        — <strong>{{ $trx->installment_count }}x cicilan</strong>
                        (Rp {{ number_format($perCicilanRencana) }}/cicilan)
                    @endif
                </div>
            </div>
            @endif

            @if($trx->kredit_notes)
            <div class="ki-box full">
                <div class="ki-label">📝 Catatan dari Kasir</div>
                <div class="catatan-box" style="margin-top:4px;">{{ $trx->kredit_notes }}</div>
            </div>
            @endif
        </div>

        {{-- Cicilan progress --}}
        @if($trx->installment_count && $trx->installment_count > 1)
        @php
            // Hitung cicilan berdasarkan sisa setelah DP
            $sisaUntukCicilan = $dpPayment ? ($trx->total - $dpPayment->amount) : $trx->total;
            $perCicil         = ceil($sisaUntukCicilan / $trx->installment_count);
            // Total terbayar untuk cicilan = totalTerbayar dikurangi DP
            $terbayarCicilan  = $dpPayment ? max($totalTerbayar - $dpPayment->amount, 0) : $totalTerbayar;
            $cicilanTerbayar  = $perCicil > 0 ? min(floor($terbayarCicilan / $perCicil), $trx->installment_count) : 0;
        @endphp
        <div style="margin-top:12px; background:#f9fafb; border-radius:7px; padding:10px 14px;">
            <div style="font-size:12px; font-weight:700; color:#374151; margin-bottom:8px;">
                📆 Progress Cicilan: {{ $cicilanTerbayar }}/{{ $trx->installment_count }} cicilan terbayar
            </div>
            <div style="display:flex; gap:4px; flex-wrap:wrap;">
                @for($i = 1; $i <= $trx->installment_count; $i++)
                    @php $paid_i = ($terbayarCicilan >= $perCicil * $i); @endphp
                    <div style="width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center;
                                font-size:11px; font-weight:700;
                                background:{{ $paid_i ? '#22c55e' : '#e5e7eb' }};
                                color:{{ $paid_i ? '#fff' : '#6b7280' }};">
                        {{ $i }}
                    </div>
                @endfor
            </div>
            <div style="font-size:11px; color:#6b7280; margin-top:6px;">
                Per cicilan: <strong>Rp {{ number_format($perCicil) }}</strong>
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- ===== ITEM BELANJA ===== --}}
    <div class="k-card">
        <div class="k-card-title">🛒 Item Belanja</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Produk</th>
                    <th>Satuan</th>
                    <th style="text-align:right">Harga</th>
                    <th style="text-align:center">Qty</th>
                    <th style="text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trx->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->unit->product->name }}</td>
                    <td>{{ $item->unit->unit_name }}</td>
                    <td style="text-align:right">Rp {{ number_format($item->price) }}</td>
                    <td style="text-align:center">{{ $item->qty }}</td>
                    <td style="text-align:right; font-weight:700;">
                        Rp {{ number_format(($item->price - ($item->discount ?? 0)) * $item->qty) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="border-top:2px solid #e5e7eb;">
                @if($trx->discount > 0)
                <tr>
                    <td colspan="5" style="text-align:right; padding:8px 12px; color:#6b7280;">Diskon:</td>
                    <td style="text-align:right; padding:8px 12px; color:#dc2626;">- Rp {{ number_format($trx->discount) }}</td>
                </tr>
                @endif
                <tr>
                    <td colspan="5" style="text-align:right; padding:8px 12px; font-weight:700;">Total:</td>
                    <td style="text-align:right; padding:8px 12px; font-weight:800; font-size:15px;">Rp {{ number_format($trx->total) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ===== FORM BAYAR ===== --}}
    @if($sisa > 0)
    <div class="k-card no-print">
        <div class="k-card-title">💰 Catat Pembayaran</div>

        {{-- Saran cicilan --}}
        @if($trx->installment_count && $trx->installment_count > 1)
        @php
            $sisaUntukSaran = $dpPayment ? ($trx->total - $dpPayment->amount) : $trx->total;
            $saranBayar     = ceil($sisaUntukSaran / $trx->installment_count);
        @endphp
        <div class="cicilan-hint">
            💡 Saran: Bayar <strong>Rp {{ number_format($saranBayar) }}</strong>/cicilan sesuai rencana
            ({{ $trx->installment_count }}x cicilan · Rp {{ number_format($saranBayar) }} per cicilan).
            <br>Kamu boleh bayar kurang atau lebih dari nominal cicilan.
        </div>
        @else
        <div class="cicilan-hint">
            💡 Sisa hutang: <strong>Rp {{ number_format($sisa) }}</strong>.
            Kamu boleh bayar berapa saja — tidak harus sekaligus lunas.
        </div>
        @endif

        <form method="POST" action="{{ route('pos.kredit.partial') }}">
            @csrf
            <input type="hidden" name="trx_id" value="{{ $trx->id }}">

            <div class="form-grid-3">
                <div class="form-group">
                    <label>💵 Jumlah Bayar <span style="color:#dc2626">*</span></label>
                    <input type="number" name="amount" class="form-input"
                           placeholder="Boleh kurang dari cicilan"
                           min="1" max="{{ $sisa }}" required>
                    <small style="font-size:11px; color:#6b7280;">Maks: Rp {{ number_format($sisa) }}</small>
                </div>
                <div class="form-group">
                    <label>🏦 Metode Pembayaran <span style="color:#dc2626">*</span></label>
                    <select name="method" class="form-input">
                        <option value="cash">💵 Cash / Tunai</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="qris">📱 QRIS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>🔐 Password Owner <span style="color:#dc2626">*</span></label>
                    <input type="password" name="password" class="form-input"
                           placeholder="Password owner" required>
                </div>
            </div>

            <div class="form-group">
                <label>📝 Catatan Pembayaran (opsional)</label>
                <input type="text" name="note" class="form-input"
                       placeholder="Misal: DP pertama, transfer BCA tgl 8 Maret...">
            </div>

            <button type="submit" class="btn-pay">💳 Simpan Pembayaran</button>
        </form>

        {{-- Tombol Lunasi Sekaligus --}}
        <hr class="k-divider" style="margin-top:16px;">
        <div style="font-size:12px; color:#6b7280; margin-bottom:8px;">
            Atau lunasi sekaligus (sisa Rp {{ number_format($sisa) }})
        </div>
        <form method="POST" action="{{ route('pos.kredit.lunasi') }}">
            @csrf
            <input type="hidden" name="trx_id" value="{{ $trx->id }}">
            <div class="form-grid-3">
                <div class="form-group">
                    <label>🏦 Metode</label>
                    <select name="method" class="form-input">
                        <option value="cash">💵 Cash</option>
                        <option value="transfer">🏦 Transfer</option>
                        <option value="qris">📱 QRIS</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>🔐 Password Owner</label>
                    <input type="password" name="password" class="form-input"
                           placeholder="Password owner" required>
                </div>
                <div class="form-group">
                    <label>📝 Catatan (opsional)</label>
                    <input type="text" name="note" class="form-input" placeholder="Catatan">
                </div>
            </div>
            <button type="submit" class="btn-lunasi">✅ Lunasi Sekaligus (Rp {{ number_format($sisa) }})</button>
        </form>

    </div>
    @endif

    {{-- ===== RIWAYAT PEMBAYARAN ===== --}}
    <div class="k-card">
        <div class="k-card-title">📜 Riwayat Pembayaran</div>

        @if($trx->payments->isEmpty())
            <div style="text-align:center; padding:20px; color:#9ca3af; font-size:13px;">
                Belum ada pembayaran yang tercatat
            </div>
        @else
        <table class="history-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal & Jam</th>
                    <th>Jumlah</th>
                    <th>Metode</th>
                    <th>Dicatat oleh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @php $runningTotal = 0; @endphp
                @foreach($trx->payments as $p)
                @php
                    $runningTotal += $p->amount;
                    $isDp = ($p->note === 'DP / Uang Muka');
                @endphp
                <tr class="{{ $isDp ? 'row-dp' : '' }}">
                    <td>
                        {{ $loop->iteration }}
                        @if($isDp)
                            <span style="font-size:10px; background:#059669; color:#fff;
                                         padding:1px 5px; border-radius:10px; margin-left:3px;">DP</span>
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($p->paid_at)->locale('id')->translatedFormat('l, d M Y H:i') }}</td>
                    <td>
                        <strong>Rp {{ number_format($p->amount) }}</strong>
                        <br><small style="color:#6b7280; font-size:10px;">Akumulasi: Rp {{ number_format($runningTotal) }}</small>
                    </td>
                    <td>
                        <span class="method-badge m-{{ $p->method }}">
                            {{ ['cash'=>'💵 Cash','transfer'=>'🏦 Transfer','qris'=>'📱 QRIS'][$p->method] ?? $p->method }}
                        </span>
                    </td>
                    <td style="font-size:12px; color:#6b7280;">
                        {{-- FIX: relasi di KreditPayment model bernama createdBy(), bukan creator() --}}
                        {{ $p->createdBy->name ?? '—' }}
                    </td>
                    <td style="font-size:12px; color:#374151;">
                        {{ $p->note ?: '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot style="border-top:2px solid #e5e7eb;">
                <tr>
                    <td colspan="2" style="padding:9px 12px; text-align:right; font-weight:700;">Total Terbayar:</td>
                    <td colspan="4" style="padding:9px 12px; font-weight:800; color:#16a34a;">
                        Rp {{ number_format($totalTerbayar) }}
                    </td>
                </tr>
            </tfoot>
        </table>
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-hide success alert setelah 3.5 detik
    const a = document.getElementById('alert-ok');
    if (a) {
        setTimeout(() => {
            a.style.transition = 'opacity .5s';
            a.style.opacity    = '0';
            setTimeout(() => a.remove(), 500);
        }, 3500);
    }
});
</script>

@endsection