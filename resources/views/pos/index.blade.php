@extends('layouts.app')
@section('title','Point of Sale')
@section('content')
@php $isReadOnly = $isReadOnly ?? false; @endphp
<style>
* { box-sizing: border-box; }
html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; font-family: Arial, sans-serif; font-size: 17px; }
.pos-wrapper { padding: 7px 10px; height: calc(100vh - 56px); display: flex; flex-direction: column; overflow: hidden; }
.trx-header { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 4px 12px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px; height: 38px; flex-shrink: 0; }
.trx-header .trx-left   { display: flex; align-items: center; gap: 10px; }
.trx-header .trx-right  { display: flex; align-items: center; gap: 6px; }
.trx-header .trx-number { font-size: 15px; font-weight: 700; color: #0d6efd; }
.trx-header .trx-time   { font-size: 13px; color: #6c757d; }
.trx-header.readonly-header { background: linear-gradient(135deg, #fff3e0, #ffe0b2); border-color: #ffb347; }
.trx-header.readonly-header .trx-number { color: #e67e00; }
.readonly-badge { display: inline-flex; align-items: center; gap: 4px; background: #e67e00; color: #fff; font-size: 15px; font-weight: 700; border-radius: 20px; padding: 2px 10px; white-space: nowrap; }
.readonly-banner { background: linear-gradient(135deg, #fff8e1, #fff3cd); border: 2px solid #ffc107; border-radius: 6px; padding: 6px 12px; margin-bottom: 7px; flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.readonly-banner-left { display: flex; align-items: center; gap: 8px; }
.readonly-banner-icon { font-size: 20px; }
.readonly-banner-text { font-size: 15px; color: #7a5400; }
.readonly-banner-text strong { color: #c45c00; }
.readonly-banner-btn { display: inline-flex; align-items: center; gap: 5px; background: #e67e00; color: #fff; border: none; border-radius: 6px; padding: 5px 12px; cursor: pointer; font-weight: 700; font-size: 15px; text-decoration: none; white-space: nowrap; transition: background .15s; }
.readonly-banner-btn:hover { background: #c45c00; color: #fff; }
.new-transaction-btn { display: flex; align-items: center; gap: 4px; background: #28a745; color: white; border: none; border-radius: 5px; padding: 5px 12px; cursor: pointer; font-weight: 600; font-size: 15px; transition: all .2s; white-space: nowrap; }
.new-transaction-btn:hover { background: #218838; }
.jurnal-btn { display: flex; align-items: center; gap: 4px; background: #6d28d9; color: white; border: none; border-radius: 5px; padding: 5px 12px; cursor: pointer; font-weight: 600; font-size: 15px; transition: all .2s; white-space: nowrap; }
.jurnal-btn:hover { background: #5b21b6; }
.pos-container { display: flex; gap: 10px; flex: 1; overflow: hidden; min-height: 0; }
.pos-left { flex: 0 0 310px; background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 8px; display: flex; flex-direction: column; overflow: hidden; gap: 5px; }
.pos-right { flex: 1; background: #fff; border: 1px solid #ddd; border-radius: 6px; padding: 8px; display: flex; flex-direction: column; overflow: hidden; gap: 5px; min-height: 0; }
.pos-right.readonly-panel { background: #fffdf7; border-color: #ffe0b2; }
.pos-box { border: 1px solid #ddd; border-radius: 5px; overflow: auto; }
.pos-table { margin: 0; font-size: 13px; }
.pos-table th { background: #f5f5f5; position: sticky; top: 0; z-index: 1; font-size: 14px; padding: 4px 4px; white-space: nowrap; }
.pos-table td { vertical-align: middle; padding: 3px 4px; font-size: 14px; }
.qty-input   { width: 54px; text-align: center; font-size: 15px; padding: 2px 4px; }
.unit-select { width: 82px; font-size: 13px; padding: 2px 4px; }
.big-total   { font-size: 19px; font-weight: bold; }
.locked      { background: #eee; cursor: not-allowed; }
.member-info { font-size: 13px; color: #555; word-break: break-word; }
.section-label { font-size: 13px; font-weight: 700; color: #333; margin: 0; flex-shrink: 0; }
.input-active { border-color: #0d6efd !important; box-shadow: 0 0 0 2px rgba(13,110,253,.2) !important; background-color: #f0f6ff !important; }
#searchBox { border: 1px solid #ddd; border-radius: 5px 5px 0 0; overflow-x: auto; overflow-y: auto; max-height: calc(32px * 4 + 32px); flex-shrink: 0; }
#searchBox table { margin: 0; font-size: 13px; white-space: nowrap; }
#searchBox thead th { background: #f5f5f5; position: sticky; top: 0; z-index: 2; font-size: 15px; padding: 5px 9px; border-bottom: 2px solid #ddd; white-space: nowrap; }
#searchBox tbody td { vertical-align: middle; padding: 4px 9px; font-size: 15px; white-space: nowrap; }
#searchResult tr.search-row-active td { background-color: #0d6efd !important; color: #fff !important; }
#searchResult tr.search-row-active td span { background: rgba(255,255,255,0.25) !important; color: #fff !important; }
#searchResult tr:hover td { background-color: #e8f0fe; }
#searchResult tr.search-row-active:hover td { background-color: #0b5ed7 !important; }
.search-nav-hint { display: none; font-size: 11px; color: #6c757d; padding: 2px 6px; background: #f8f9fa; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px; text-align: center; flex-shrink: 0; }
.search-nav-hint.show { display: block; }
#searchBox::-webkit-scrollbar        { width: 5px; height: 5px; }
#searchBox::-webkit-scrollbar-track  { background: #f1f1f1; }
#searchBox::-webkit-scrollbar-thumb  { background: #bbb; border-radius: 3px; }
.cart-section { border: 1px solid #ddd; border-radius: 5px; overflow: hidden; display: flex; flex-direction: column; flex-shrink: 0; }
.cart-section.readonly-cart { border-color: #ffcc80; }
.cart-table-header { background: #f5f5f5; border-bottom: 2px solid #ddd; flex-shrink: 0; }
.readonly-cart .cart-table-header { background: #fff8e1; border-bottom-color: #ffcc80; }
.cart-table-header table { margin: 0; width: 100%; table-layout: fixed; font-size: 15px; }
.cart-table-header th    { font-weight: 600; padding: 6px 8px; }
.cart-table-body { overflow-y: auto; max-height: calc(36px * 4); flex-shrink: 0; }
.cart-table-body table { margin: 0; width: 100%; table-layout: fixed; font-size: 15px; }
.cart-table-body td    { padding: 4px 7px; vertical-align: middle; }
.cart-footer { border-top: 1px solid #ddd; padding-top: 6px; flex: 1; overflow-y: auto; min-height: 0; padding-right: 4px; }
.cart-footer::-webkit-scrollbar        { width: 5px; }
.cart-footer::-webkit-scrollbar-track  { background: #f1f1f1; }
.cart-footer::-webkit-scrollbar-thumb  { background: #ccc; border-radius: 3px; }
.total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }
.trx-today-header { display: flex; align-items: center; gap: 7px; flex-shrink: 0; }
.pending-badge { display: inline-flex; align-items: center; justify-content: center; background: #dc3545; color: #fff; font-size: 13px; font-weight: 700; border-radius: 20px; padding: 2px 8px; min-width: 20px; height: 20px; line-height: 1; animation: pulse-badge 1.5s infinite; }
.pending-badge.hidden { display: none; }
@keyframes pulse-badge { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: .8; transform: scale(1.08); } }
.method-select { width: 100%; font-size: 15px; padding: 6px 9px; border: 1.5px solid #dee2e6; border-radius: 6px; background: #fff; cursor: pointer; transition: border-color .2s; }
.method-select:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }
.panel-notice { background: #fffbe6; border: 1px solid #ffe58f; border-radius: 6px; padding: 7px 11px; font-size: 15px; color: #7a5400; margin-bottom: 5px; }
.kredit-readonly-summary { background: linear-gradient(135deg, #fff8f0, #fff3e0); border: 2px solid #ffcc80; border-radius: 10px; padding: 12px 14px; margin-bottom: 8px; }
.krs-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px dashed #ffcc80; }
.krs-header-icon  { font-size: 22px; }
.krs-header-title { font-size: 14px; font-weight: 800; color: #c45c00; }
.krs-header-sub   { font-size: 11px; color: #a06000; margin-top: 1px; }
.krs-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 5px; }
.krs-row:last-child { margin-bottom: 0; }
.krs-label  { color: #7a3b00; font-weight: 600; }
.krs-value  { font-weight: 700; color: #333; }
.krs-divider { border: none; border-top: 1px dashed #ffcc80; margin: 6px 0; }
.krs-total-row { display: flex; justify-content: space-between; align-items: center; padding-top: 6px; }
.krs-total-label { font-size: 15px; font-weight: 800; color: #c45c00; }
.krs-total-value { font-size: 21px; font-weight: 900; color: #e67e00; }
.krs-sisa-row { display: flex; justify-content: space-between; align-items: center; background: #fff5f5; border: 1.5px solid #fca5a5; border-radius: 7px; padding: 7px 11px; margin-top: 6px; }
.krs-sisa-label { font-size: 15px; font-weight: 700; color: #991b1b; }
.krs-sisa-value { font-size: 16px; font-weight: 900; color: #dc2626; }
.btn-detail-kredit { display: flex; align-items: center; justify-content: center; gap: 6px; width: 100%; padding: 11px; font-size: 15px; font-weight: 800; color: #fff; background: linear-gradient(135deg, #e67e00, #c45c00); border: none; border-radius: 8px; cursor: pointer; transition: all .15s; text-decoration: none; margin-top: 4px; }
.btn-detail-kredit:hover { box-shadow: 0 4px 14px rgba(230,126,0,.35); transform: translateY(-1px); color: #fff; }
.kredit-panel-full { background: linear-gradient(135deg, #fff8f0, #fff3e0); border: 2px solid #ffcc80; border-radius: 8px; padding: 9px 11px; margin-bottom: 5px; animation: slideDown .2s ease; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.kredit-header { display: flex; align-items: center; gap: 6px; margin-bottom: 7px; padding-bottom: 6px; border-bottom: 1px dashed #ffcc80; }
.kredit-header-icon  { font-size: 17px; }
.kredit-header-title { font-size: 13px; font-weight: 800; color: #c45c00; }
.kredit-header-sub   { font-size: 11px; color: #a06000; margin-top: 1px; }
.kredit-total-banner { background: #fff; border: 1.5px solid #ffb347; border-radius: 6px; padding: 6px 11px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
.kredit-total-banner .ktb-label  { font-size: 15px; color: #7a3b00; font-weight: 600; }
.kredit-total-banner .ktb-amount { font-size: 16px; font-weight: 900; color: #e67e00; }
.dp-box { background: #fff; border: 1.5px solid #a7f3d0; border-radius: 7px; padding: 9px 11px; margin-bottom: 7px; }
.dp-box-title { font-size: 11px; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 5px; }
.dp-sisa-info { display: none; margin-top: 6px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: 5px 10px; font-size: 12px; color: #166534; }
.kredit-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin-bottom: 5px; }
.kredit-form-grid .kfg-full { grid-column: 1 / -1; }
.kredit-field label { font-size: 14px; font-weight: 700; color: #7a3b00; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .3px; }
.kredit-field input, .kredit-field select, .kredit-field textarea { width: 100%; font-size: 13px; padding: 4px 8px; border: 1.5px solid #ffcc80; border-radius: 6px; background: #fffdf9; color: #333; transition: border-color .15s; outline: none; }
.kredit-field input:focus, .kredit-field select:focus, .kredit-field textarea:focus { border-color: #e67e00; background: #fff; box-shadow: 0 0 0 2px rgba(230,126,0,.15); }
.kredit-field textarea { resize: none; height: 40px; font-family: inherit; }
.dp-field input, .dp-field select { border-color: #6ee7b7 !important; }
.dp-field input:focus, .dp-field select:focus { border-color: #059669 !important; box-shadow: 0 0 0 2px rgba(5,150,105,.15) !important; }
.jatuh-tempo-chips { display: flex; gap: 3px; flex-wrap: wrap; margin-top: 3px; }
.jt-chip { background: #fff; border: 1.5px solid #ffcc80; border-radius: 20px; padding: 2px 9px; font-size: 11px; font-weight: 700; color: #8a4000; cursor: pointer; transition: all .12s; white-space: nowrap; }
.jt-chip:hover  { background: #ffe0b2; border-color: #e67e00; }
.jt-chip.active { background: #e67e00; border-color: #e67e00; color: #fff; }
.jt-info { background: #fff8e6; border: 1px solid #ffe0b2; border-radius: 5px; padding: 4px 9px; font-size: 11px; color: #7a3b00; display: flex; justify-content: space-between; align-items: center; margin-top: 3px; }
.jt-info .jt-date { font-weight: 700; color: #c45c00; }
.angsuran-info { background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 5px; padding: 3px 8px; font-size: 12px; color: #1b5e20; margin-top: 3px; display: none; }
.kredit-success-box { background: linear-gradient(135deg, #fff8f0, #fff3e0); border: 2px solid #e67e00; border-radius: 10px; padding: 14px; text-align: center; }
.kredit-success-box .ks-icon  { font-size: 30px; margin-bottom: 4px; }
.kredit-success-box .ks-title { font-weight: 800; color: #e67e00; font-size: 15px; margin-bottom: 2px; }
.kredit-success-box .ks-trx   { font-size: 13%; color: #aaa; margin-bottom: 4px; }
.kredit-success-box .ks-total { font-size: 13px; color: #7a3b00; margin-bottom: 6px; }
.kredit-success-box .ks-due   { font-size: 13px; background:#fff8e6; border:1px solid #ffe0b2; border-radius:5px; padding:4px 8px; color:#c45c00; margin-bottom:8px; display:inline-block; }
.kredit-success-box .ks-btns  { display: flex; gap: 6px; }
.kredit-success-box .ks-btns a, .kredit-success-box .ks-btns button { flex: 1; font-size: 14px; padding: 8px 4px; border-radius: 7px; border: none; cursor: pointer; font-weight: 700; text-decoration: none; display: flex; align-items: center; justify-content: center; }
.form-control-xs { font-size: 13px; padding: 5px 9px; height: 32px; }
.form-control-xs:focus { outline: none; border-color: #86b7fe; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }
.alert-xs { font-size: 13px; padding: 5px 10px; margin-bottom: 0; border-radius: 4px; }
.btn-qty  { padding: 2px 8px; font-size: 15px; line-height: 1.5; }
#barcode.adding, #search.adding { background-color: #fff8e1 !important; border-color: #ffc107 !important; }
.input-readonly-mode { background: #f5f5f5 !important; cursor: not-allowed !important; color: #888 !important; pointer-events: none; }

/* ── Tabel Transaksi Hari Ini ── */
.trx-today-table { width: 100%; border-collapse: collapse; }
.trx-today-table th { background: #f5f5f5; position: sticky; top: 0; z-index: 1; font-size: 13px; font-weight: 700; padding: 5px 4px; white-space: nowrap; color: #495057; border-bottom: 2px solid #dee2e6; }
.trx-today-table td { font-size: 12px; padding: 4px 4px; border-bottom: 1px solid #f1f3f5; vertical-align: middle; }
.trx-today-table tbody tr:hover { background: #e8f4fd; cursor: pointer; }
.trx-today-table tbody tr.active-row { background: #dbeafe; }
.num-col { text-align: right; font-variant-numeric: tabular-nums; }

/* JURNAL OVERLAY */
#jurnalOverlay { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1060; display: none; align-items: stretch; justify-content: flex-end; backdrop-filter: blur(2px); }
#jurnalOverlay.show { display: flex; }
#jurnalPanel { width: 82vw; max-width: 1100px; background: #fff; box-shadow: -6px 0 32px rgba(0,0,0,.22); display: flex; flex-direction: column; overflow: hidden; animation: slideInRight .22s ease; }
@keyframes slideInRight { from { transform: translateX(60px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.jr-header { background: linear-gradient(135deg, #6d28d9, #5b21b6); color: #fff; padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
.jr-header-title { font-size: 16px; font-weight: 800; }
.jr-header-sub   { font-size: 14px; opacity: .82; margin-top: 2px; }
.jr-close-btn { background: rgba(255,255,255,.22); border: none; color: #fff; border-radius: 6px; padding: 5px 12px; cursor: pointer; font-size: 17px; line-height: 1; transition: background .15s; white-space: nowrap; }
.jr-close-btn:hover { background: rgba(255,255,255,.38); }
.jr-loading { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 40px; color: #6c757d; font-size: 14px; position: absolute; inset: 60px 0 0 0; background: #fff; z-index: 1; }
.jr-spinner { width: 24px; height: 24px; border: 3px solid #ddd6fe; border-top-color: #6d28d9; border-radius: 50%; animation: spin .7s linear infinite; }
#jurnalFrame { flex: 1; border: none; width: 100%; min-height: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ══ PASSWORD MODAL ══ */
#pwdModalOverlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.55);
    z-index: 9999; display: none; align-items: center; justify-content: center;
    backdrop-filter: blur(3px);
}
#pwdModalOverlay.show { display: flex; }
#pwdModalBox {
    background: #fff; border-radius: 12px; padding: 24px 28px;
    width: 360px; box-shadow: 0 8px 40px rgba(0,0,0,.25);
    animation: popIn .18s ease;
}
@keyframes popIn { from { transform: scale(.92); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.pwd-modal-icon  { font-size: 30px; text-align: center; margin-bottom: 6px; }
.pwd-modal-title { font-size: 15px; font-weight: 800; color: #1f2937; text-align: center; margin-bottom: 4px; }
.pwd-modal-sub   { font-size: 14px; color: #6b7280; text-align: center; margin-bottom: 14px; line-height: 1.5; }
.pwd-modal-label { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 4px; display: block; }
.pwd-modal-wrap  { position: relative; margin-bottom: 6px; }
.pwd-modal-input {
    width: 100%; font-size: 15px; padding: 9px 42px 9px 13px;
    border: 2px solid #d1d5db; border-radius: 8px;
    outline: none; transition: border-color .15s; letter-spacing: 2px;
}
.pwd-modal-input:focus { border-color: #6d28d9; box-shadow: 0 0 0 3px rgba(109,40,217,.15); }
.pwd-modal-eye {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; font-size: 17px; color: #9ca3af;
    padding: 0; line-height: 1;
}
.pwd-modal-eye:hover { color: #374151; }
.pwd-modal-error { font-size: 12px; color: #dc2626; min-height: 16px; margin-bottom: 10px; text-align: center; }
.pwd-modal-btns { display: flex; gap: 8px; }
.pwd-modal-btns button {
    flex: 1; padding: 10px; border-radius: 8px; border: none; cursor: pointer;
    font-size: 14px; font-weight: 700; transition: all .15s;
}
.pwd-btn-ok     { background: #6d28d9; color: #fff; }
.pwd-btn-ok:hover { background: #5b21b6; }
.pwd-btn-cancel { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
.pwd-btn-cancel:hover { background: #e5e7eb; }
</style>

{{-- ══ PASSWORD MODAL (menggantikan prompt) ══ --}}
<div id="pwdModalOverlay">
    <div id="pwdModalBox">
        <div class="pwd-modal-icon" id="pwdModalIcon">🔐</div>
        <div class="pwd-modal-title" id="pwdModalTitle">Verifikasi Password Owner</div>
        <div class="pwd-modal-sub"  id="pwdModalSub">Masukkan password owner untuk melanjutkan.</div>
        <label class="pwd-modal-label">Password Owner</label>
        <div class="pwd-modal-wrap">
            <input type="password" id="pwdModalInput" class="pwd-modal-input"
                   placeholder="Masukkan password..." autocomplete="current-password">
            <button type="button" class="pwd-modal-eye" id="pwdModalEye"
                    onclick="togglePwdVisibility()" title="Tampilkan/sembunyikan password">👁</button>
        </div>
        <div class="pwd-modal-error" id="pwdModalError"></div>
        <div class="pwd-modal-btns">
            <button class="pwd-btn-cancel" onclick="pwdModalCancel()">✕ Batal</button>
            <button class="pwd-btn-ok"     onclick="pwdModalConfirm()">🔓 Konfirmasi</button>
        </div>
    </div>
</div>

<div class="pos-wrapper">

    @if($isReadOnly)
    <div class="readonly-banner">
        <div class="readonly-banner-left">
            <span class="readonly-banner-icon">🔒</span>
            <div class="readonly-banner-text">
                <strong>Mode Hanya Lihat — Transaksi Kredit</strong><br>
                Transaksi ini berstatus kredit dan tidak dapat diubah. Untuk pembayaran, gunakan tombol di sebelah kanan.
            </div>
        </div>
        <a href="{{ route('pos.kredit.show', $trx->id) }}" class="readonly-banner-btn">📋 Detail &amp; Bayar Kredit →</a>
    </div>
    @endif

    <div class="trx-header {{ $isReadOnly ? 'readonly-header' : '' }}">
        <div class="trx-left">
            <span class="trx-number">{{ $trx->trx_number }}</span>
            <span class="trx-time">{{ $trx->created_at->format('d M Y') }} • {{ $trx->created_at->format('H:i:s') }}</span>
            @if($isReadOnly)<span class="readonly-badge">🔒 Kredit – Read Only</span>@endif
        </div>
        <div class="trx-right">
            <button class="jurnal-btn" onclick="openJurnal()">📒 Jurnal Umum</button>
            <button class="new-transaction-btn" onclick="createNewTransaction()">+ Transaksi Baru</button>
        </div>
    </div>

    <div class="pos-container">

        {{-- ========== KOLOM KIRI ========== --}}
        <div class="pos-left">
            <input type="hidden" id="warehouse_id" value="{{ $activeWarehouse->id }}">
            <div class="alert alert-info alert-xs">Gudang: <strong>{{ $activeWarehouse->name }}</strong></div>

            <input type="text" id="barcode" class="form-control form-control-xs {{ $isReadOnly ? 'input-readonly-mode' : '' }}"
                   placeholder="{{ $isReadOnly ? '🔒 Tidak dapat menambah produk (mode kredit)' : '① Scan barcode / Enter untuk lanjut' }}"
                   {{ $isReadOnly ? 'readonly disabled' : '' }}>
            <input type="text" id="search"  class="form-control form-control-xs {{ $isReadOnly ? 'input-readonly-mode' : '' }}"
                   placeholder="{{ $isReadOnly ? '🔒 Pencarian dinonaktifkan (mode kredit)' : '② Cari produk — ↑↓ pilih, ←→ geser kolom, Enter ambil' }}"
                   {{ $isReadOnly ? 'readonly disabled' : '' }}>

            <span class="section-label">Hasil Pencarian</span>
            <div id="searchBox">
                <table class="table table-sm table-bordered mb-0" id="searchTable">
                    <thead>
                        <tr>
                            <th style="width:28px;">No</th>
                            <th style="min-width:110px;">Barcode</th>
                            <th style="min-width:140px;">Nama</th>
                            <th style="min-width:50px;">Sat.</th>
                            @foreach($warehouses as $idx => $wh)
                                <th style="min-width:62px; text-align:center;" title="{{ $wh->name }}">Stok {{ chr(65 + $idx) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="searchResult">
                        <tr>
                            <td colspan="{{ 4 + count($warehouses) }}" class="text-center text-muted" style="font-size:12px; padding:7px;">
                                @if($isReadOnly)🔒 Transaksi kredit — pencarian produk dinonaktifkan
                                @else Ketik minimal 2 karakter untuk mencari produk @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="search-nav-hint" id="searchNavHint">↑↓ Pilih baris &nbsp;|&nbsp; ←→ Geser kolom &nbsp;|&nbsp; Enter Ambil &nbsp;|&nbsp; Esc Tutup</div>

            {{-- NAMA PEMBELI + MEMBER --}}
            <div style="flex-shrink:0;">

                {{-- ③ Nama Pembeli Biasa --}}
                <span class="section-label">③ Nama Pembeli</span>
                @if($isReadOnly)
                    <input type="text" class="form-control form-control-xs input-readonly-mode mt-1"
                           value="{{ $trx->buyer_name ?? ($trx->member ? $trx->member->name : 'Tidak ada nama') }}" readonly disabled>
                @else
                    <input type="text" id="buyerName" class="form-control form-control-xs mt-1"
                           placeholder="Nama pembeli (opsional, tampil di struk)"
                           value="{{ old('buyer_name', $trx->buyer_name ?? '') }}">
                @endif

                {{-- ④ Member Terdaftar --}}
                <div class="mt-1" style="display:flex; align-items:center; gap:5px;">
                    <span class="section-label" style="white-space:nowrap;">④ Member</span>
                    <span style="font-size:11px; color:#6c757d;">(klik untuk pilih member terdaftar)</span>
                </div>
                @if($isReadOnly)
                    <input type="text" class="form-control form-control-xs input-readonly-mode mt-1"
                           value="{{ $trx->member ? $trx->member->name . ' — ' . $trx->member->level : 'Tidak ada member' }}" readonly disabled>
                @else
                    <input type="text" id="member" class="form-control form-control-xs locked mt-1"
                           placeholder="🔐 Klik untuk pilih member (butuh password)" readonly onclick="unlockMember()">
                @endif
                <div id="memberResult" class="border mt-1" style="max-height:70px; overflow:auto;"></div>
                <div id="memberInfo" class="mt-1 member-info">
                    @if($isReadOnly && $trx->member)
                        <strong>Nama:</strong> {{ $trx->member->name }} | <strong>Level:</strong> {{ $trx->member->level }} | <strong>Disc:</strong> {{ $trx->member->discount }}%
                    @endif
                </div>
            </div>

            {{-- TRANSAKSI HARI INI --}}
            <div class="trx-today-header">
    <span class="section-label">Transaksi Hari Ini</span>
    @php $pendingCount = $todayTransactions->where('status','pending')->count(); @endphp
    <span class="pending-badge {{ $pendingCount == 0 ? 'hidden' : '' }}" id="pendingBadge">
        {{ $pendingCount }} Pending
    </span>
</div>

<div class="pos-box" style="flex:1; overflow:auto; min-height:0;">
    <table class="trx-today-table">
        <thead>
            <tr>
                <th style="width:18px;">No</th>
                <th style="min-width:72px;">Transaksi</th>
                <th style="min-width:82px;">Pelanggan</th>
                <th style="width:34px; text-align:center;">Jam</th>
                <th style="width:50px; text-align:right;">Total</th>
                <th style="width:48px; text-align:right;">Dibayar</th>
                <th style="width:48px; text-align:right;">Sisa</th>
                <th style="width:48px; text-align:center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($todayTransactions as $t)
            @php
                $tTotal  = (float)($t->subtotal_items ?? $t->total ?? 0);
                $tPaid   = (float)($t->paid_amount ?? $t->paid ?? 0);
                $tSisa   = (float)($t->sisa ?? max($tTotal - $tPaid, 0));
                $hasPart = $t->status === 'pending' && $tPaid > 0;
                $customerName = $t->member ? $t->member->name : ($t->buyer_name ?? 'Umum');
            @endphp
            <tr class="{{ $t->id == $trx->id ? 'active-row' : '' }}"
                @if($t->status === 'pending')
                    onclick="openPending({{ $t->id }})" title="Lanjutkan transaksi"
                @elseif($t->status === 'kredit')
                    onclick="openKreditReadOnly({{ $t->id }})" title="Lihat detail kredit"
                @else
                    onclick="openPaidTransaction({{ $t->id }})" title="Buka kembali (butuh password)"
                @endif
            >
                <td>{{ $loop->iteration }}</td>
                <td style="max-width:72px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; font-size:11px; color:#333;">
                    {{ $t->trx_number }}
                </td>
                <td style="max-width:82px; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; font-size:11px; font-weight:500;">
                    @if($t->member)
                        <i class="fa fa-user-check text-info" style="font-size:9px;"></i> 
                    @endif
                    {{ $customerName }}
                </td>
                <td style="text-align:center; color:#6c757d; font-size:11px;">{{ $t->created_at->format('H:i') }}</td>
                <td class="num-col" style="color:#333; font-weight:600;">{{ number_format($tTotal / 1000, 0) }}K</td>
                <td class="num-col" style="color:#059669; font-weight:600;">
                    @if($tPaid > 0){{ number_format($tPaid / 1000, 0) }}K
                    @else<span style="color:#ccc;">—</span>@endif
                </td>
                <td class="num-col">
                    @if($t->status === 'paid' || $tSisa <= 0)
                        <span style="color:#059669; font-weight:700;">✓</span>
                    @else
                        <span style="color:{{ $hasPart ? '#dc2626' : '#e67e00' }}; font-weight:700;">{{ number_format($tSisa / 1000, 0) }}K</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    @if($t->status === 'paid')
                        <span class="badge bg-success" style="font-size:9px; padding:3px 5px;">Paid</span>
                    @elseif($t->status === 'kredit')
                        <span class="badge bg-warning text-dark" style="font-size:9px; padding:3px 5px;">Kredit</span>
                    @elseif($t->status === 'bayar_tagihan')
                        <span class="badge bg-info text-dark" style="font-size:9px; padding:3px 5px;">Tagihan</span>
                    @elseif($hasPart)
                        <span class="badge bg-danger" style="font-size:9px; padding:3px 5px;">Cicil</span>
                    @else
                        <span class="badge bg-secondary" style="font-size:9px; padding:3px 5px;">Pending</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center text-muted py-2" style="font-size:13px;">Belum ada transaksi</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

        </div>{{-- end pos-left --}}

        {{-- ========== KOLOM KANAN ========== --}}
        <div class="pos-right {{ $isReadOnly ? 'readonly-panel' : '' }}">

            <div class="cart-section {{ $isReadOnly ? 'readonly-cart' : '' }}">
                <div class="cart-table-header">
                    <table class="table table-sm mb-0">
                        <colgroup>
                            <col style="width:30px"><col>
                            <col style="width:85px">
                            @if(!$isReadOnly)<col style="width:135px">@endif
                            <col style="width:105px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>*</th>
                                <th>Nama Produk</th>
                                <th>Satuan</th>
                                @if(!$isReadOnly)<th>Qty</th>
                                @else<th style="text-align:center;">Qty</th>@endif
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="cart-table-body">
                    <table class="table table-bordered table-sm mb-0">
                        <colgroup>
                            <col style="width:30px"><col>
                            <col style="width:85px">
                            @if(!$isReadOnly)<col style="width:135px">@endif
                            <col style="width:105px">
                        </colgroup>
                        <tbody id="cartBody">
                            @php $total = 0; @endphp
                            @foreach($trx->items as $i)
                            @php $sub = ($i->price - ($i->discount ?? 0)) * $i->qty; $total += $sub; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $i->unit->product->name }}<br><small class="text-muted" style="font-size:11px;">{{ $i->unit->barcode ?? '-' }}</small></td>
                                <td>
                                    @if($isReadOnly)
                                        <span style="font-size:12px; color:#555; font-weight:600;">{{ $i->unit->unit_name }}</span>
                                    @else
                                        <select class="form-select form-select-sm unit-select" style="font-size:12px; padding:2px 4px;" onchange="updateUnit({{ $i->id }},this.value)">
                                            @foreach($i->unit->product->units as $u)
                                            <option value="{{ $u->id }}" {{ $u->id==$i->product_unit_id ? 'selected':'' }}>{{ $u->unit_name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td>
                                    @if($isReadOnly)
                                        <span style="font-size:13px; font-weight:700; color:#333; display:block; text-align:center;">{{ $i->qty }}</span>
                                    @else
                                        <div class="d-flex align-items-center gap-1">
                                            <button class="btn btn-sm btn-outline-secondary btn-qty" onclick="minusQty({{ $i->id }})">−</button>
                                            <input type="number" class="form-control form-control-sm qty-input" value="{{ $i->qty }}" onchange="updateQtyManual({{ $i->id }},this.value)">
                                            <button class="btn btn-sm btn-outline-secondary btn-qty" onclick="plusQty({{ $i->id }})">+</button>
                                            <button class="btn btn-sm btn-danger btn-qty" onclick="removeItemWithAuth({{ $i->id }}, '{{ addslashes($i->unit->product->name) }}')">🗑</button>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold" style="font-size:13px;">Rp {{ number_format($sub) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="cart-footer">
                @if($isReadOnly)
                @php
                    $totalTerbayar = $trx->payments ? $trx->payments->sum('amount') : ($trx->accepted ?? 0);
                    $sisaHutang    = max($trx->total - $totalTerbayar, 0);
                @endphp
                <div class="kredit-readonly-summary">
                    <div class="krs-header">
                        <div class="krs-header-icon">📋</div>
                        <div>
                            <div class="krs-header-title">Ringkasan Transaksi Kredit</div>
                            <div class="krs-header-sub">Transaksi ini bersifat read-only — tidak dapat diedit</div>
                        </div>
                    </div>
                    @if($trx->debtor_name)<div class="krs-row"><span class="krs-label">👤 Peminjam</span><span class="krs-value">{{ $trx->debtor_name }}</span></div>@endif
                    @if($trx->debtor_phone)<div class="krs-row"><span class="krs-label">📱 Telepon</span><span class="krs-value">{{ $trx->debtor_phone }}</span></div>@endif
                    @if($trx->due_date)<div class="krs-row"><span class="krs-label">📅 Jatuh Tempo</span><span class="krs-value">{{ \Carbon\Carbon::parse($trx->due_date)->translatedFormat('d M Y') }}</span></div>@endif
                    @if($trx->payment_plan)<div class="krs-row"><span class="krs-label">💳 Cara Bayar</span><span class="krs-value">{{ ucfirst($trx->payment_plan) }}{{ $trx->installment_count ? " ({$trx->installment_count}x)" : '' }}</span></div>@endif
                    @if($trx->discount > 0)<div class="krs-row"><span class="krs-label">🏷️ Diskon</span><span class="krs-value" style="color:#28a745;">- Rp {{ number_format($trx->discount) }}</span></div>@endif
                    <hr class="krs-divider">
                    <div class="krs-total-row"><span class="krs-total-label">💰 Total Belanja</span><span class="krs-total-value">Rp {{ number_format($trx->total) }}</span></div>
                    @if($totalTerbayar > 0)<div class="krs-row" style="margin-top:6px;"><span class="krs-label" style="color:#065f46;">✅ Sudah Dibayar</span><span class="krs-value" style="color:#059669;">Rp {{ number_format($totalTerbayar) }}</span></div>@endif
                    @if($sisaHutang > 0)
                        <div class="krs-sisa-row"><span class="krs-sisa-label">📌 Sisa Hutang</span><span class="krs-sisa-value">Rp {{ number_format($sisaHutang) }}</span></div>
                    @else
                        <div class="krs-sisa-row" style="border-color:#a7f3d0; background:#f0fdf4;"><span class="krs-sisa-label" style="color:#065f46;">✅ Sudah Lunas</span><span class="krs-sisa-value" style="color:#059669;">Rp 0</span></div>
                    @endif
                </div>
                <a href="{{ route('pos.kredit.show', $trx->id) }}" class="btn-detail-kredit">📋 Buka Halaman Detail &amp; Pembayaran Kredit →</a>
                <div style="margin-top:8px; text-align:center;"><button class="btn btn-outline-secondary btn-sm w-100" style="font-size:13px;" onclick="createNewTransaction()">+ Transaksi Baru</button></div>

                @else
                @php $alreadyPaid = (float)($trx->paid ?? 0); @endphp
                @if($alreadyPaid > 0)
                <div style="background:#fff3cd; border:1px solid #ffc107; border-radius:6px; padding:6px 11px; margin-bottom:5px; font-size:13px; color:#856404;">
                    💵 Sudah dibayar: <strong>Rp {{ number_format($alreadyPaid) }}</strong>
                    &nbsp;|&nbsp; Sisa: <strong style="color:#dc2626;">Rp {{ number_format(max($total - $alreadyPaid, 0)) }}</strong>
                </div>
                @endif

                <div class="total-row">
                    <span style="font-size:15px; color:#6c757d; font-weight:600;">Total</span>
                    <span class="big-total" id="totalText" data-total="{{ $total }}" data-original="{{ $total }}">Rp {{ number_format($total) }}</span>
                </div>
                <div class="total-row">
                    <span style="font-size:13px;">④ Diskon (Rp):</span>
                    <input type="number" id="discount_rp" class="form-control locked" style="width:105px; font-size:13px; padding:3px 8px; height:30px;" placeholder="Diskon (Rp)" readonly onclick="unlockDiscountRp()">
                </div>
                <div class="total-row">
                    <span style="font-size:13px;">⑤ Diskon (%):</span>
                    <input type="number" id="discount_percent" class="form-control locked" style="width:105px; font-size:13px; padding:3px 8px; height:30px;" placeholder="Diskon (%)" readonly onclick="unlockDiscountPercent()">
                </div>
                <div style="margin-bottom:6px;">
                    <label style="font-size:12px; font-weight:700; color:#555; display:block; margin-bottom:3px;">⑥ Metode Pembayaran</label>
                    <select id="paymentMethod" class="method-select" onchange="onMethodChange(this.value)">
                        <option value="cash">💵 Cash / Tunai</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="qris">📱 QRIS</option>
                        <option value="kredit">📋 Kredit / Hutang</option>
                    </select>
                </div>
                <div id="panelNotice" class="panel-notice" style="display:none;"></div>
                <div id="panelCash">
                    <input type="number" id="paid" class="form-control form-control-xs" placeholder="⑦ Jumlah bayar → Enter untuk bayar">
                    <div class="total-row mt-1">
                        <span style="font-size:14px;">Kembalian:</span>
                        <span id="changeText" class="big-total" style="color:#28a745; font-size:17px;">Rp 0</span>
                    </div>
                </div>
                <div id="panelKredit" style="display:none;">
                    <div class="kredit-panel-full">
                        <div class="kredit-header"><div class="kredit-header-icon">📋</div><div><div class="kredit-header-title">Transaksi Kredit / Hutang</div><div class="kredit-header-sub">Stok dikurangi · Pembayaran ditangguhkan · Catat sebagai piutang</div></div></div>
                        <div class="kredit-total-banner"><span class="ktb-label">💰 Total Belanja</span><span class="ktb-amount" id="kreditTotalBelanja">Rp 0</span></div>
                        <div class="dp-box">
                            <div class="dp-box-title">💵 Uang Muka / DP (opsional)</div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                                <div class="kredit-field dp-field"><label>Jumlah DP</label><input type="number" id="kreditDP" min="0" placeholder="0 = tidak ada DP" oninput="updateKreditSisa()"></div>
                                <div class="kredit-field dp-field"><label>Metode DP</label><select id="kreditDPMethod"><option value="cash">💵 Cash</option><option value="transfer">🏦 Transfer</option><option value="qris">📱 QRIS</option></select></div>
                            </div>
                            <div class="dp-sisa-info" id="kreditSisaBox">DP dibayar: <strong id="kreditDPText">Rp 0</strong> &nbsp;→&nbsp; Sisa hutang: <strong id="kreditSisaText" style="color:#dc2626;">Rp 0</strong></div>
                        </div>
                        <div class="kredit-total-banner" style="border-color:#fca5a5; background:#fff5f5;"><span class="ktb-label" style="color:#991b1b;">📌 Sisa Hutang</span><span class="ktb-amount" id="kreditTotal" style="color:#dc2626;">Rp 0</span></div>
                        <div class="kredit-form-grid">
                            <div class="kredit-field kfg-full"><label>👤 Nama Peminjam / Pelanggan</label><input type="text" id="kreditNama" placeholder="Nama pelanggan (opsional jika ada member)"></div>
                            <div class="kredit-field"><label>📱 No. Telepon</label><input type="text" id="kreditTelp" placeholder="08xxxxxxxxxx"></div>
                            <div class="kredit-field"><label>💳 Rencana Cara Bayar</label><select id="kreditCaraBayar"><option value="cash">💵 Cash / Tunai</option><option value="transfer">🏦 Transfer Bank</option><option value="qris">📱 QRIS</option><option value="cicilan">📆 Cicilan</option></select></div>
                            <div class="kredit-field kfg-full" id="kreditCicilanGroup" style="display:none;"><label>📆 Jumlah Cicilan (kali)</label><input type="number" id="kreditCicilan" min="2" max="36" value="3" placeholder="Misal: 3" oninput="hitungCicilan()"><div class="angsuran-info" id="angsuranInfo"></div></div>
                        </div>
                        <div class="kredit-field" style="margin-bottom:4px;">
                            <label>📅 Estimasi Jatuh Tempo</label>
                            <input type="date" id="kreditJatuhTempo" oninput="updateJatuhTempoInfo()">
                            <div class="jatuh-tempo-chips" id="jtChips">
                                <span class="jt-chip" onclick="setJatuhTempo(7)">7 hari</span>
                                <span class="jt-chip" onclick="setJatuhTempo(14)">14 hari</span>
                                <span class="jt-chip active" onclick="setJatuhTempo(30)">30 hari</span>
                                <span class="jt-chip" onclick="setJatuhTempo(60)">2 bulan</span>
                                <span class="jt-chip" onclick="setJatuhTempo(90)">3 bulan</span>
                            </div>
                            <div class="jt-info" id="jtInfo"><span>⏳ Jatuh tempo:</span><span class="jt-date" id="jtInfoDate">—</span></div>
                        </div>
                        <div class="kredit-field" style="margin-bottom:0; margin-top:0;"><label>📝 Catatan / Keterangan</label><textarea id="kreditCatatan" placeholder="Misal: pembayaran saat gajian, transfer ke BCA 123xxx..."></textarea></div>
                    </div>
                </div>
                <button id="btnPay"    class="btn btn-primary btn-sm w-100 mt-1" style="font-size:14px;" onclick="processPay()">💳 Simpan / Bayar</button>
                <button id="btnKredit" class="btn btn-sm w-100 mt-1" style="font-size:14px; display:none; color:#fff; background:#e67e00; border:none; border-radius:6px; font-weight:700; padding:9px;" onclick="processPay()">📋 Simpan sebagai Kredit / Hutang</button>
                @endif
            </div>

        </div>{{-- end pos-right --}}
    </div>
</div>

<div id="jurnalOverlay" onclick="handleJurnalOverlayClick(event)">
    <div id="jurnalPanel">
        <div class="jr-header">
            <div><div class="jr-header-title">📒 Jurnal Umum Detail</div><div class="jr-header-sub">Data transaksi dalam format jurnal akuntansi · Halaman kasir tetap aktif</div></div>
            <div style="display:flex; gap:8px; align-items:center;">
                <a id="jurnalNewTabBtn" href="{{ route('reports.journal') }}" target="_blank"
                   style="background:rgba(255,255,255,.22); color:#fff; border-radius:6px; padding:5px 12px; font-size:13px; font-weight:700; text-decoration:none; white-space:nowrap;">↗ Tab Baru</a>
                <button class="jr-close-btn" onclick="closeJurnal()">✕ Tutup</button>
            </div>
        </div>
        <div class="jr-loading" id="jurnalLoading"><div class="jr-spinner"></div> Memuat jurnal...</div>
        <iframe id="jurnalFrame" src="about:blank" onload="onJurnalFrameLoad()" style="display:none;"></iframe>
    </div>
</div>

<script>
const TRX           = {{ $trx->id }};
const csrf          = '{{ csrf_token() }}';
const warehouseList = @json($warehousesJson);
const IS_READ_ONLY  = @json($isReadOnly);
const JURNAL_URL    = '{{ route("reports.journal") }}';
function getWarehouseId() { return document.getElementById('warehouse_id').value; }
const jsonHeaders = { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' };
let memberUnlocked = false, manualDiscountRp = 0, manualDiscountPercent = 0, memberDiscount = 0;
let selectedPaymentMethod = 'cash', isAdding = false, isScanPending = false, selectedSearchIdx = -1;

// ✅ FIX: Simpan override password yang sudah diverifikasi agar bisa dikirim saat pay
let overridePasswordUsed = null;

// ══════════════════════════════════════════════════════
//  PASSWORD MODAL — menggantikan prompt() biasa
// ══════════════════════════════════════════════════════
let _pwdResolve = null;

function askPassword(title = 'Verifikasi Password Owner', sub = 'Masukkan password owner untuk melanjutkan.', icon = '🔐') {
    return new Promise(resolve => {
        _pwdResolve = resolve;
        document.getElementById('pwdModalIcon').textContent  = icon;
        document.getElementById('pwdModalTitle').textContent = title;
        document.getElementById('pwdModalSub').textContent   = sub;
        document.getElementById('pwdModalInput').value       = '';
        document.getElementById('pwdModalError').textContent = '';
        document.getElementById('pwdModalInput').type        = 'password';
        document.getElementById('pwdModalEye').textContent   = '👁';
        document.getElementById('pwdModalOverlay').classList.add('show');
        setTimeout(() => document.getElementById('pwdModalInput').focus(), 80);
    });
}

function pwdModalCancel() {
    document.getElementById('pwdModalOverlay').classList.remove('show');
    if (_pwdResolve) { _pwdResolve(null); _pwdResolve = null; }
}

function pwdModalConfirm() {
    const val = document.getElementById('pwdModalInput').value;
    if (!val.trim()) {
        document.getElementById('pwdModalError').textContent = '⚠️ Password tidak boleh kosong.';
        document.getElementById('pwdModalInput').focus();
        return;
    }
    document.getElementById('pwdModalOverlay').classList.remove('show');
    if (_pwdResolve) { _pwdResolve(val); _pwdResolve = null; }
}

function togglePwdVisibility() {
    const inp = document.getElementById('pwdModalInput');
    const eye = document.getElementById('pwdModalEye');
    if (inp.type === 'password') { inp.type = 'text';     eye.textContent = '🙈'; }
    else                         { inp.type = 'password'; eye.textContent = '👁'; }
    inp.focus();
}

document.getElementById('pwdModalInput').addEventListener('keydown', e => {
    if (e.key === 'Enter')  { e.preventDefault(); pwdModalConfirm(); }
    if (e.key === 'Escape') { e.preventDefault(); pwdModalCancel(); }
});
document.getElementById('pwdModalOverlay').addEventListener('click', e => {
    if (e.target === document.getElementById('pwdModalOverlay')) pwdModalCancel();
});

// ══════════════════════════════════════════════════════
//  SEARCH & NAVIGATION
// ══════════════════════════════════════════════════════
function updateSearchHighlight() {
    const rows = document.querySelectorAll('#searchResult tr[data-unit-id]');
    rows.forEach((r,i) => r.classList.toggle('search-row-active', i === selectedSearchIdx));
    if (selectedSearchIdx >= 0 && rows[selectedSearchIdx]) rows[selectedSearchIdx].scrollIntoView({block:'nearest'});
}
function showSearchHint(s) { document.getElementById('searchNavHint').classList.toggle('show', s); }
function resetSearchSelection() { selectedSearchIdx = -1; updateSearchHighlight(); showSearchHint(false); }

const NAV_ORDER = ['barcode','search','buyerName','member','discount_rp','discount_percent','paid'];
function focusNext(id) {
    if (IS_READ_ONLY) return;
    const idx = NAV_ORDER.indexOf(id); if (idx === -1) return;
    if (idx === NAV_ORDER.length-1) { document.getElementById('btnPay')?.click(); return; }
    const nextId = NAV_ORDER[idx+1], nextEl = document.getElementById(nextId); if (!nextEl) return;
    if (nextEl.readOnly || nextEl.classList.contains('locked')) { focusNext(nextId); return; }
    nextEl.focus(); nextEl.select && nextEl.select(); highlightActive(nextId);
}
function highlightActive(id) {
    if (IS_READ_ONLY) return;
    NAV_ORDER.forEach(i => { const e = document.getElementById(i); if(e) e.classList.remove('input-active'); });
    const e = document.getElementById(id); if(e) e.classList.add('input-active');
}

if (!IS_READ_ONLY) {
    document.getElementById('barcode').addEventListener('keydown', function(e) {
        if (e.key !== 'Enter') return; e.preventDefault();
        if (isScanPending || isAdding) return;
        const code = this.value.trim(); if (!code) { focusNext('barcode'); return; }
        isScanPending = true;
        document.getElementById('barcode').classList.add('adding');
        document.getElementById('search').classList.add('adding');
        const bEl = this;
        fetch('/pos/scan', {method:'POST', headers:jsonHeaders, body:JSON.stringify({code, warehouse_id:getWarehouseId()})})
            .then(r=>r.json()).then(r=>{
                isScanPending = false;
                document.getElementById('barcode').classList.remove('adding');
                document.getElementById('search').classList.remove('adding');
                if (!r.success) { alert(r.message); return; }
                bEl.value = ''; add(r.id); bEl.focus(); highlightActive('barcode');
            }).catch(err=>{
                isScanPending=false;
                document.getElementById('barcode').classList.remove('adding');
                document.getElementById('search').classList.remove('adding');
                console.error(err); alert('Gagal scan barcode. Coba lagi.');
            });
    });
    document.getElementById('barcode').addEventListener('focus', ()=>highlightActive('barcode'));
}

let isFromKeyboard = false;
if (!IS_READ_ONLY) {
    document.getElementById('search').addEventListener('keydown', function(e) {
        const box = document.getElementById('searchBox');
        const rows = document.querySelectorAll('#searchResult tr[data-unit-id]');
        const rc   = rows.length;
        if (e.key==='ArrowDown')  { e.preventDefault(); if(rc){ selectedSearchIdx=Math.min(selectedSearchIdx+1,rc-1); updateSearchHighlight(); } return; }
        if (e.key==='ArrowUp')    { e.preventDefault(); if(rc){ selectedSearchIdx=selectedSearchIdx<=0?-1:selectedSearchIdx-1; updateSearchHighlight(); } return; }
        if (e.key==='ArrowRight') { e.preventDefault(); box.scrollLeft+=80; return; }
        if (e.key==='ArrowLeft')  { e.preventDefault(); box.scrollLeft-=80; return; }
        if (e.key==='Escape') {
            e.preventDefault();
            document.getElementById('searchResult').innerHTML=`<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:13px;padding:7px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
            this.value=''; resetSearchSelection(); return;
        }
        if (e.key==='Enter') {
            e.preventDefault(); if(isScanPending||isAdding) return;
            if (selectedSearchIdx>=0&&rows[selectedSearchIdx]) { isFromKeyboard=true; addFromSearch(Number(rows[selectedSearchIdx].dataset.unitId)); setTimeout(()=>{isFromKeyboard=false;},300); return; }
            if (rc===1) { isFromKeyboard=true; addFromSearch(Number(rows[0].dataset.unitId)); setTimeout(()=>{isFromKeyboard=false;},300); return; }
            if (this.value.trim()===''||rc===0) focusNext('search');
        }
    });
    document.getElementById('search').addEventListener('keyup', function(e) {
        if (['Enter','ArrowDown','ArrowUp','ArrowLeft','ArrowRight','Escape'].includes(e.key)) return;
        const q = this.value.trim();
        if (q.length<2) {
            document.getElementById('searchResult').innerHTML=`<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:13px;padding:7px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
            resetSearchSelection(); document.getElementById('searchBox').scrollLeft=0; return;
        }
        fetch(`/pos/search?q=${encodeURIComponent(q)}&warehouse_id=${getWarehouseId()}`).then(r=>r.json()).then(items=>{
            selectedSearchIdx=-1; let html='';
            items.forEach((p,i)=>{
                let sc=''; (p.stocks||[]).forEach(s=>{const col=s>0?'#155724':'#666',bg=s>0?'#d4edda':'#e9ecef'; sc+=`<td style="text-align:center;min-width:62px;"><span style="background:${bg};color:${col};padding:2px 7px;border-radius:4px;font-size:12px;font-weight:600;">${s}</span></td>`;});
                html+=`<tr style="cursor:pointer;" data-unit-id="${p.id}" onclick="if(!isFromKeyboard) addFromSearch(${p.id})"><td style="width:28px;">${i+1}</td><td style="min-width:110px;">${p.barcode??'-'}</td><td style="min-width:140px;">${p.name}</td><td style="min-width:50px;">${p.unit}</td>${sc}</tr>`;
            });
            const cc=4+warehouseList.length;
            document.getElementById('searchResult').innerHTML=html||`<tr><td colspan="${cc}" class="text-center text-muted" style="font-size:13px;padding:7px;">Tidak ada hasil untuk "<strong>${q}</strong>"</td></tr>`;
            document.getElementById('searchBox').scrollLeft=0; showSearchHint(items.length>0);
        });
    });
    document.getElementById('search').addEventListener('focus', function(){
        highlightActive('search');
        if(document.querySelectorAll('#searchResult tr[data-unit-id]').length>0) showSearchHint(true);
    });
    document.getElementById('search').addEventListener('blur', ()=>setTimeout(()=>showSearchHint(false),200));
    ['buyerName','member','discount_rp','discount_percent','paid'].forEach(id=>{
        const el=document.getElementById(id); if(!el) return;
        el.addEventListener('keydown', e=>{ if(e.key!=='Enter') return; e.preventDefault(); if(id==='paid') processPay(); else focusNext(id); });
        el.addEventListener('focus', ()=>highlightActive(id));
    });
}

window.addEventListener('load', ()=>{ if(!IS_READ_ONLY){ document.getElementById('barcode').focus(); highlightActive('barcode'); setJatuhTempo(30); } });

function createNewTransaction() { window.location.href='/pos?new_transaction=1'; }
function openKreditReadOnly(id) { window.location.href=`/pos?trx_id=${id}`; }

// ── UNLOCK MEMBER ──
async function unlockMember() {
    if (IS_READ_ONLY || memberUnlocked) return;
    const pwd = await askPassword('Buka Input Member', 'Masukkan password owner untuk mengisi data member.');
    if (!pwd) return;
    fetch('/pos/override-owner',{method:'POST',headers:jsonHeaders,body:JSON.stringify({password:pwd})}).then(r=>r.json()).then(r=>{
        if (!r.success) { alert("❌ Password salah"); return; }
        memberUnlocked=true;
        const el=document.getElementById('member'); el.readOnly=false; el.classList.remove('locked'); el.focus(); highlightActive('member');
    });
}

// ── UNLOCK DISKON RP ──
async function unlockDiscountRp() {
    if (IS_READ_ONLY) return;
    const el=document.getElementById('discount_rp'); if(!el.classList.contains('locked')) return;
    const pwd = await askPassword('Buka Diskon (Rp)', 'Masukkan password owner untuk memberi diskon rupiah.');
    if (!pwd) return;
    fetch('/pos/override-owner',{method:'POST',headers:jsonHeaders,body:JSON.stringify({password:pwd})}).then(r=>r.json()).then(r=>{
        if (!r.success) { alert("❌ Password salah"); return; }
        el.readOnly=false; el.classList.remove('locked'); el.focus(); highlightActive('discount_rp');
    });
}

// ── UNLOCK DISKON % ──
async function unlockDiscountPercent() {
    if (IS_READ_ONLY) return;
    const el=document.getElementById('discount_percent'); if(!el.classList.contains('locked')) return;
    const pwd = await askPassword('Buka Diskon (%)', 'Masukkan password owner untuk memberi diskon persen.');
    if (!pwd) return;
    fetch('/pos/override-owner',{method:'POST',headers:jsonHeaders,body:JSON.stringify({password:pwd})}).then(r=>r.json()).then(r=>{
        if (!r.success) { alert("❌ Password salah"); return; }
        el.readOnly=false; el.classList.remove('locked'); el.focus(); highlightActive('discount_percent');
    });
}

if (!IS_READ_ONLY) {
    document.getElementById('discount_rp').addEventListener('input', function(){
        const v=this.value.trim(); if(v===''||Number(v)<=0){manualDiscountRp=0;this.value='';}else{manualDiscountRp=Number(v);manualDiscountPercent=0;document.getElementById('discount_percent').value='';}
        applyDiscountLive();
    });
    document.getElementById('discount_percent').addEventListener('input', function(){
        const v=this.value.trim(); if(v===''||Number(v)<=0){manualDiscountPercent=0;this.value='';}else{manualDiscountPercent=Number(v);manualDiscountRp=0;document.getElementById('discount_rp').value='';}
        applyDiscountLive();
    });
}

function onMethodChange(m) {
    if(IS_READ_ONLY) return; selectedPaymentMethod=m;
    const pC=document.getElementById('panelCash'),pK=document.getElementById('panelKredit'),pN=document.getElementById('panelNotice'),bP=document.getElementById('btnPay'),bK=document.getElementById('btnKredit');
    const total=Number(document.getElementById('totalText').dataset.total);
    pC.style.display=pK.style.display=pN.style.display='none'; bP.style.display=''; bK.style.display='none';
    if(m==='kredit'){
        pK.style.display='';bP.style.display='none';bK.style.display='';
        document.getElementById('kreditTotalBelanja').innerText='Rp '+total.toLocaleString('id-ID');
        document.getElementById('kreditTotal').innerText='Rp '+total.toLocaleString('id-ID');
        document.getElementById('kreditDP').value=''; document.getElementById('kreditSisaBox').style.display='none';
        const mEl=document.getElementById('member'); if(mEl&&mEl.value.trim()){const kN=document.getElementById('kreditNama');if(!kN.value)kN.value=mEl.value;}
    } else if(m==='transfer'){
        pC.style.display='';pN.style.display='';pN.innerHTML='⚠️ Pastikan bukti <strong>transfer bank</strong> sudah diterima sebelum proses bayar.';
        document.getElementById('paid').value=total; updateKembalian();
    } else if(m==='qris'){
        pC.style.display='';pN.style.display='';pN.innerHTML='📱 Pastikan notifikasi <strong>QRIS</strong> sudah diterima sebelum proses bayar.';
        document.getElementById('paid').value=total; updateKembalian();
    } else {
        pC.style.display=''; document.getElementById('paid').value=''; updateKembalian();
    }
}

function updateKreditSisa() {
    if(IS_READ_ONLY) return;
    const t=Number(document.getElementById('totalText').dataset.total),dp=parseFloat(document.getElementById('kreditDP').value)||0,s=Math.max(t-dp,0),box=document.getElementById('kreditSisaBox');
    if(dp>0){box.style.display='';document.getElementById('kreditDPText').innerText='Rp '+dp.toLocaleString('id-ID');document.getElementById('kreditSisaText').innerText='Rp '+s.toLocaleString('id-ID');document.getElementById('kreditTotal').innerText='Rp '+s.toLocaleString('id-ID');}
    else{box.style.display='none';document.getElementById('kreditTotal').innerText='Rp '+t.toLocaleString('id-ID');}
    hitungCicilan();
}

function setJatuhTempo(days) {
    if(IS_READ_ONLY) return;
    const d=new Date(); d.setDate(d.getDate()+days);
    const yyyy=d.getFullYear(),mm=String(d.getMonth()+1).padStart(2,'0'),dd=String(d.getDate()).padStart(2,'0');
    document.getElementById('kreditJatuhTempo').value=`${yyyy}-${mm}-${dd}`;
    document.querySelectorAll('.jt-chip').forEach(c=>c.classList.remove('active'));
    const map={7:0,14:1,30:2,60:3,90:4}; if(map[days]!==undefined) document.querySelectorAll('.jt-chip')[map[days]]?.classList.add('active');
    updateJatuhTempoInfo();
}

function updateJatuhTempoInfo() {
    if(IS_READ_ONLY) return;
    const val=document.getElementById('kreditJatuhTempo').value; if(!val) return;
    const today=new Date();today.setHours(0,0,0,0);
    const due=new Date(val);due.setHours(0,0,0,0);
    const diff=Math.round((due-today)/(864e5)),tgl=due.toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}),hari=['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][due.getDay()];
    document.getElementById('jtInfoDate').innerText=`${hari}, ${tgl} (${diff} hari lagi)`;
    const cd=[7,14,30,60,90],matched=cd.find(d=>{const t=new Date();t.setDate(t.getDate()+d);t.setHours(0,0,0,0);return t.getTime()===due.getTime();});
    document.querySelectorAll('.jt-chip').forEach(c=>c.classList.remove('active'));
    if(matched!==undefined){const map={7:0,14:1,30:2,60:3,90:4};document.querySelectorAll('.jt-chip')[map[matched]]?.classList.add('active');}
    hitungCicilan();
}

if(!IS_READ_ONLY){
    const kCB=document.getElementById('kreditCaraBayar');
    if(kCB){kCB.addEventListener('change',function(){const ic=this.value==='cicilan';document.getElementById('kreditCicilanGroup').style.display=ic?'':'none';if(ic)hitungCicilan();else document.getElementById('angsuranInfo').style.display='none';});}
}

function hitungCicilan() {
    if(IS_READ_ONLY) return; if(document.getElementById('kreditCaraBayar').value!=='cicilan') return;
    const t=Number(document.getElementById('totalText').dataset.total),dp=parseFloat(document.getElementById('kreditDP').value)||0,s=Math.max(t-dp,0),n=parseInt(document.getElementById('kreditCicilan').value)||1,pc=Math.ceil(s/n),infoEl=document.getElementById('angsuranInfo'),jv=document.getElementById('kreditJatuhTempo').value;
    let cd=''; if(jv){const due=new Date(jv),today=new Date();today.setHours(0,0,0,0);const diff=Math.round((due-today)/864e5),intv=Math.round(diff/n),pts=[];for(let i=1;i<=Math.min(n,4);i++){const d=new Date(today);d.setDate(d.getDate()+intv*i);pts.push(d.toLocaleDateString('id-ID',{day:'numeric',month:'short'}));}if(n>4)pts.push('...');cd=` <small style="color:#555">(${pts.join(' · ')})</small>`;}
    infoEl.innerHTML=`📆 ${n}x cicilan = <strong>Rp ${pc.toLocaleString('id-ID')}</strong>/cicilan${cd}`; infoEl.style.display='';
}

function addFromSearch(id) {
    if(IS_READ_ONLY){alert('🔒 Transaksi kredit tidak dapat diedit.');return;} if(isScanPending||isAdding) return;
    add(id);
    document.getElementById('search').value='';
    document.getElementById('searchResult').innerHTML=`<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:13px;padding:7px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
    resetSearchSelection(); document.getElementById('searchBox').scrollLeft=0; document.getElementById('barcode').focus(); highlightActive('barcode');
}

// ✅ FIX: ADD ITEM
async function add(id, ow=null) {
    if(IS_READ_ONLY){alert('🔒 Transaksi kredit tidak dapat diedit.');return;} if(isAdding) return; isAdding=true;
    document.getElementById('barcode').classList.add('adding'); document.getElementById('search').classList.add('adding');
    fetch('/pos/add-item',{method:'POST',headers:jsonHeaders,body:JSON.stringify({trx_id:TRX,product_unit_id:id,warehouse_id:getWarehouseId(),override_password:ow})})
    .then(r=>r.json()).then(async r=>{
        isAdding=false; document.getElementById('barcode').classList.remove('adding'); document.getElementById('search').classList.remove('adding');
        if(r.readonly){alert('🔒 '+r.message);return;}
        if(r.need_override){
            const p = await askPassword('Override Stok', `Stok tidak cukup!\nMasukkan password owner untuk melanjutkan.`, '⚠️');
            if(!p) return;
            overridePasswordUsed = p;
            add(id, p);
            return;
        }
        if(!r.success){alert(r.message);return;}
        if(ow) overridePasswordUsed = ow;
        loadCart();
    }).catch(err=>{
        isAdding=false; document.getElementById('barcode').classList.remove('adding'); document.getElementById('search').classList.remove('adding');
        console.error(err); alert('Terjadi error saat menambah item. Coba lagi.');
    });
}

function loadCart() {
    if(IS_READ_ONLY) return;
    fetch(`/pos?trx_id=${TRX}`).then(r=>r.text()).then(html=>{
        const doc=new DOMParser().parseFromString(html,'text/html'),tEl=doc.querySelector('#totalText'); if(!tEl) return;
        const orig=Math.round(Number(tEl.dataset.total));
        document.querySelector('#cartBody').innerHTML=doc.querySelector('#cartBody').innerHTML;
        document.getElementById('totalText').dataset.original=orig; document.getElementById('totalText').dataset.total=orig; document.getElementById('totalText').innerText='Rp '+orig.toLocaleString('id-ID');
        const nb=doc.querySelector('#pendingBadge'); if(nb){const b=document.getElementById('pendingBadge');b.innerText=nb.innerText;b.classList.toggle('hidden',nb.classList.contains('hidden'));}
        applyDiscountLive(); updateKembalian();
        if(selectedPaymentMethod==='kredit'){const t=Number(document.getElementById('totalText').dataset.total);document.getElementById('kreditTotalBelanja').innerText='Rp '+t.toLocaleString('id-ID');updateKreditSisa();}
    });
}

function applyDiscountLive() {
    if(IS_READ_ONLY) return; const tEl=document.getElementById('totalText'); if(!tEl) return;
    const awal=Math.round(Number(tEl.dataset.original)); let akhir=awal;
    if(manualDiscountRp>0)akhir=awal-Math.round(manualDiscountRp);
    else if(manualDiscountPercent>0)akhir=awal-Math.round(awal*manualDiscountPercent/100);
    else if(memberDiscount>0)akhir=awal-Math.round(awal*memberDiscount/100);
    if(akhir<0)akhir=0; tEl.innerText='Rp '+akhir.toLocaleString('id-ID'); tEl.dataset.total=akhir; updateKembalian();
    if(selectedPaymentMethod==='kredit'){document.getElementById('kreditTotalBelanja').innerText='Rp '+akhir.toLocaleString('id-ID');updateKreditSisa();}
}

function plusQty(id){if(IS_READ_ONLY)return;updateQtyManual(id,getQty(id)+1);}
function minusQty(id){if(IS_READ_ONLY)return;updateQtyManual(id,Math.max(getQty(id)-1,1));}
function getQty(id){return Number(document.querySelector(`input[onchange="updateQtyManual(${id},this.value)"]`).value);}

// ✅ FIX: UPDATE QTY
async function updateQtyManual(iId,qty,ow=null) {
    if(IS_READ_ONLY){alert('🔒 Transaksi kredit tidak dapat diedit.');return;}
    fetch('/pos/update-qty-manual',{method:'POST',headers:jsonHeaders,body:JSON.stringify({trx_id:TRX,item_id:iId,qty,warehouse_id:getWarehouseId(),override_password:ow})})
    .then(r=>r.json()).then(async r=>{
        if(r.readonly){alert('🔒 '+r.message);return;}
        if(r.need_override){
            const p = await askPassword('Override Stok', `Stok tidak cukup!\nMasukkan password owner.`, '⚠️');
            if(!p) return;
            overridePasswordUsed = p;
            updateQtyManual(iId, qty, p);
            return;
        }
        if(ow) overridePasswordUsed = ow;
        loadCart();
    });
}

function updateUnit(iId,uId) {
    if(IS_READ_ONLY){alert('🔒 Transaksi kredit tidak dapat diedit.');return;}
    fetch('/pos/update-unit',{method:'POST',headers:jsonHeaders,body:JSON.stringify({trx_id:TRX,item_id:iId,product_unit_id:uId,warehouse_id:getWarehouseId()})})
    .then(r=>r.json()).then(r=>{ if(r.readonly){alert('🔒 '+r.message);return;} loadCart(); });
}

// ── HAPUS ITEM ──
async function removeItemWithAuth(iId, name) {
    if(IS_READ_ONLY){alert('🔒 Transaksi kredit tidak dapat diedit.');return;}
    const p = await askPassword('Hapus Item', `Masukkan password owner untuk menghapus:\n"${name}"`, '🗑');
    if (!p) return;
    fetch('/pos/override-owner',{method:'POST',headers:jsonHeaders,body:JSON.stringify({password:p})}).then(r=>r.json()).then(r=>{
        if(!r.success){alert("❌ Password salah!");return;}
        if(!confirm(`⚠️ Hapus item:\n${name}\n\nYakin?`)) return;
        fetch('/pos/remove-item',{method:'POST',headers:jsonHeaders,body:JSON.stringify({trx_id:TRX,item_id:iId})})
        .then(r=>r.json()).then(r=>{ if(r.readonly){alert('🔒 '+r.message);return;} if(r.success)loadCart(); else alert("Gagal menghapus item."); });
    });
}

if(!IS_READ_ONLY){ const pEl=document.getElementById('paid'); if(pEl) pEl.addEventListener('input',updateKembalian); }

function updateKembalian() {
    if(IS_READ_ONLY) return;
    const tEl=document.getElementById('totalText'),pEl=document.getElementById('paid'),cEl=document.getElementById('changeText');
    if(!tEl||!pEl||!cEl) return;
    const total=Number(tEl.dataset.total),bayar=Number(pEl.value||0);
    cEl.innerText='Rp '+Math.max(bayar-total,0).toLocaleString('id-ID');
}

// ✅ FIX FINAL: processPay
async function processPay(forceOverride = null) {
    if(IS_READ_ONLY){alert('🔒 Transaksi kredit tidak dapat diproses ulang dari sini. Gunakan halaman detail kredit.');return;}
    const tEl=document.getElementById('totalText'),total=Number(tEl.dataset.total),pm=selectedPaymentMethod;
    const bayar=pm==='kredit'?0:Number(document.getElementById('paid').value||0);
    const memberId=document.getElementById('member')?.dataset.memberId||null;
    const buyerName=(document.getElementById('buyerName')?.value||'').trim()||null;
    if(pm!=='kredit'&&bayar<=0){alert('Masukkan jumlah bayar terlebih dahulu!');document.getElementById('paid').focus();return;}
    if(pm==='kredit'){
        const jv=document.getElementById('kreditJatuhTempo').value;
        if(!jv){alert('Tentukan jatuh tempo terlebih dahulu!');return;}
        const dp=parseFloat(document.getElementById('kreditDP').value)||0;
        if(dp>=total){alert('DP tidak boleh sama atau melebihi total belanja. Gunakan pembayaran biasa.');return;}
    }
    const kd=pm==='kredit'?{
        nama_peminjam : document.getElementById('kreditNama').value.trim(),
        telepon       : document.getElementById('kreditTelp').value.trim(),
        cara_bayar    : document.getElementById('kreditCaraBayar').value,
        cicilan       : document.getElementById('kreditCaraBayar').value==='cicilan'?parseInt(document.getElementById('kreditCicilan').value)||1:null,
        jatuh_tempo   : document.getElementById('kreditJatuhTempo').value,
        catatan       : document.getElementById('kreditCatatan').value.trim(),
        dp            : parseFloat(document.getElementById('kreditDP').value)||0,
        dp_method     : document.getElementById('kreditDPMethod').value,
    }:null;

    const overrideToSend = forceOverride || overridePasswordUsed || null;

    const sw=pm!=='kredit'?window.open('','_blank'):null;
    try {
        const res=await fetch('/pos/pay',{
            method:'POST',
            headers:jsonHeaders,
            body:JSON.stringify({
                trx_id            : TRX,
                paid              : bayar,
                member_id         : memberId,
                buyer_name        : buyerName,
                payment_method    : pm,
                frontend_total    : total,
                kredit_data       : kd,
                override_password : overrideToSend,
            })
        });
        const r=await res.json();
        if(r.success){
            if(r.is_kredit){showKreditSuccess(r.trx_id,total,kd);return;}
            if(r.paid_off){
                const lb={cash:'💵 Cash / Tunai',transfer:'🏦 Transfer Bank',qris:'📱 QRIS'};
                alert('✅ Transaksi lunas!\nMetode   : '+(lb[pm]||pm)+'\nKembalian: Rp '+Math.max(bayar-total,0).toLocaleString('id-ID'));
                if(sw)sw.location.href=`/transactions/${r.trx_id}/struk`;
                setTimeout(()=>{window.location.href='/pos?new_transaction=1';},500);
            } else {
                const st=(r.sisa!==undefined?r.sisa:(total-bayar)).toLocaleString('id-ID');
                alert('💵 Pembayaran diterima!\nSudah dibayar: Rp '+r.paid_so_far.toLocaleString('id-ID')+'\nSisa         : Rp '+st);
                if(sw)sw.close(); window.location.reload();
            }
        } else {
            if(r.need_override) {
                if(sw) sw.close();
                const p = await askPassword(
                    '⚠️ Stok Tidak Cukup — Perlu Izin Owner',
                    r.message + '\n\nMasukkan password owner untuk tetap memproses.',
                    '⚠️'
                );
                if(!p) return;
                overridePasswordUsed = p;
                processPay(p);
                return;
            }
            alert(r.message||'Gagal menyimpan transaksi');
            if(sw)sw.close();
        }
    } catch(err) { alert('Terjadi error: '+err.message); if(sw)sw.close(); }
}

function showKreditSuccess(trxId,total,kd){
    const pk=document.getElementById('panelKredit');
    let dueStr='—'; if(kd&&kd.jatuh_tempo){const d=new Date(kd.jatuh_tempo);dueStr=d.toLocaleDateString('id-ID',{weekday:'long',day:'numeric',month:'long',year:'numeric'});}
    const cbl={cash:'💵 Cash',transfer:'🏦 Transfer',qris:'📱 QRIS',cicilan:'📆 Cicilan'},dp=kd?.dp||0,sisa=Math.max(total-dp,0);
    let dpInfo=''; if(dp>0){const md={cash:'💵 Cash',transfer:'🏦 Transfer',qris:'📱 QRIS'};dpInfo=`<div style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:6px 10px;margin-bottom:5px;font-size:12px;color:#065f46;">💰 DP dibayar: <strong>Rp ${dp.toLocaleString('id-ID')}</strong> (${md[kd.dp_method]||kd.dp_method})<br>📌 Sisa hutang: <strong style="color:#dc2626;">Rp ${sisa.toLocaleString('id-ID')}</strong></div>`;}
    let ei=''; if(kd&&kd.cara_bayar==='cicilan'&&kd.cicilan){const pc=Math.ceil(sisa/kd.cicilan);ei=`<div style="font-size:12px;color:#7a3b00;margin-bottom:4px;">📆 ${kd.cicilan}x cicilan = <strong>Rp ${pc.toLocaleString('id-ID')}</strong>/cicilan</div>`;}
    pk.innerHTML=`<div class="kredit-success-box"><div class="ks-icon">✅</div><div class="ks-title">Kredit Berhasil Disimpan!</div><div class="ks-trx">Transaksi #${trxId}</div><div class="ks-total">Total Belanja: <strong>Rp ${total.toLocaleString('id-ID')}</strong></div>${dpInfo}${ei}<div class="ks-due">📅 Jatuh Tempo: ${dueStr}</div>${kd?.cara_bayar?`<div style="font-size:12px;color:#888;margin-bottom:6px;">Rencana bayar: ${cbl[kd.cara_bayar]||kd.cara_bayar}</div>`:''}<div class="ks-btns"><a href="/pos/kredit/${trxId}" style="background:#e67e00;color:#fff;">📋 Detail Kredit</a><button onclick="window.location.href='/pos?new_transaction=1'" style="background:#28a745;color:#fff;">✚ Transaksi Baru</button></div></div>`;
    document.getElementById('btnKredit').style.display='none';
}

if(!IS_READ_ONLY){
    const mBox=document.getElementById('memberResult'), mEl=document.getElementById('member');
    if(mEl){mEl.addEventListener('keyup',function(e){
        if(!memberUnlocked||e.key==='Enter') return;
        const q=this.value; if(q.length<2){mBox.innerHTML='';return;}
        fetch(`/pos/search-member?q=${q}`).then(r=>r.json()).then(items=>{
            mBox.innerHTML='';
            items.forEach(m=>{mBox.innerHTML+=`<div class="p-1 border-bottom" style="cursor:pointer;font-size:13px;" onclick="selectMember(${m.id})"><strong>${m.name}</strong> — <small class="text-muted">${m.phone}</small></div>`;});
        });
    });}
}

function selectMember(id) {
    if(IS_READ_ONLY) return; manualDiscountRp=manualDiscountPercent=0;
    fetch(`/pos/get-member?id=${id}`).then(r=>r.json()).then(m=>{
        const el=document.getElementById('member'); el.value=m.name; el.dataset.memberId=m.id; document.getElementById('memberResult').innerHTML='';
        memberDiscount=Number(m.discount||0); document.getElementById('discount_rp').value=''; document.getElementById('discount_percent').value=memberDiscount>0?memberDiscount:'';
        const dp=document.getElementById('discount_percent'); dp.readOnly=false; dp.classList.remove('locked');
        document.getElementById('memberInfo').innerHTML=`<strong>Nama:</strong> ${m.name} | <strong>Level:</strong> ${m.level} | <strong>Disc:</strong> ${m.discount}% | <strong>Poin:</strong> ${m.points}`;
        const bnEl=document.getElementById('buyerName'); if(bnEl&&!bnEl.value) bnEl.value=m.name;
        const kN=document.getElementById('kreditNama'); if(kN&&!kN.value) kN.value=m.name;
        applyDiscountLive();
        fetch('/pos/set-member',{method:'POST',headers:jsonHeaders,body:JSON.stringify({trx_id:TRX,member_id:m.id})})
        .then(()=>fetch('/pos/set-discount',{method:'POST',headers:jsonHeaders,body:JSON.stringify({trx_id:TRX,discount:getFinalDiscount()})}))
        .then(()=>loadCart());
    });
}

function getFinalDiscount(){
    const tEl=document.getElementById('totalText'); if(!tEl) return 0;
    const t=Number(tEl.dataset.original||0); if(t<=0) return 0;
    if(manualDiscountPercent>0) return manualDiscountPercent;
    if(manualDiscountRp>0) return (manualDiscountRp/t)*100;
    if(memberDiscount>0) return memberDiscount;
    return 0;
}

function openPending(id){ if(!id)return; if(confirm("Lanjutkan transaksi ini?")) window.location.href=`/pos?trx_id=${id}`; }

// ── BUKA TRANSAKSI PAID ──
async function openPaidTransaction(id) {
    if(!id) return;
    const p = await askPassword('Buka Kembali Transaksi', 'Masukkan password owner untuk membuka kembali transaksi yang sudah selesai.', '🔓');
    if (!p) return;
    document.body.style.cursor='wait';
    fetch('/pos/reopen-transaction',{method:'POST',headers:jsonHeaders,body:JSON.stringify({trx_id:id,password:p})})
    .then(r=>r.json()).then(r=>{
        document.body.style.cursor='';
        if(!r.success){alert("❌ "+(r.message||"Gagal membuka transaksi"));return;}
        window.location.href=`/pos?trx_id=${r.trx_id}`;
    }).catch(err=>{ document.body.style.cursor=''; console.error(err); alert("Terjadi error. Coba lagi."); });
}

let jurnalLoaded=false;
function openJurnal(){
    document.getElementById('jurnalOverlay').classList.add('show');
    const f=document.getElementById('jurnalFrame'),l=document.getElementById('jurnalLoading');
    if(!jurnalLoaded){l.style.display='flex';f.style.display='none';f.src=JURNAL_URL;jurnalLoaded=true;}
}
function closeJurnal(){ document.getElementById('jurnalOverlay').classList.remove('show'); }
function onJurnalFrameLoad(){
    const f=document.getElementById('jurnalFrame'),l=document.getElementById('jurnalLoading');
    if(f.src&&f.src!=='about:blank'){l.style.display='none';f.style.display='';}
}
function handleJurnalOverlayClick(e){ if(e.target===document.getElementById('jurnalOverlay')) closeJurnal(); }

document.addEventListener('keydown', e=>{
    if(e.key!=='Escape') return;
    if(document.getElementById('pwdModalOverlay').classList.contains('show')) return;
    if(document.getElementById('jurnalOverlay').classList.contains('show')){ closeJurnal(); return; }
});
</script>
@endsection