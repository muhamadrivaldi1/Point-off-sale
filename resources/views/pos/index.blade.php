@extends('layouts.app')
@section('title','Point of Sale')

@section('content')

<style>
* { box-sizing: border-box; }

html, body {
    margin: 0; padding: 0;
    height: 100%; overflow: hidden;
    font-family: Arial, sans-serif;
    font-size: 13px;
}

.pos-wrapper {
    padding: 7px 10px;
    height: calc(100vh - 56px);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.trx-header {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 4px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 7px;
    height: 34px;
    flex-shrink: 0;
}
.trx-header .trx-left   { display: flex; align-items: center; gap: 10px; }
.trx-header .trx-right  { display: flex; align-items: center; gap: 6px; }
.trx-header .trx-number { font-size: 13px; font-weight: 700; color: #0d6efd; }
.trx-header .trx-time   { font-size: 11px; color: #6c757d; }

.new-transaction-btn {
    display: flex; align-items: center; gap: 4px;
    background: #28a745; color: white; border: none; border-radius: 5px;
    padding: 4px 10px; cursor: pointer; font-weight: 600; font-size: 12px;
    transition: all .2s; white-space: nowrap;
}
.new-transaction-btn:hover { background: #218838; }

/* ===== BAYAR TAGIHAN BUTTON ===== */
/* .bayar-tagihan-btn {
    display: flex; align-items: center; gap: 4px;
    background: #fd7e14; color: white; border: none; border-radius: 5px;
    padding: 4px 10px; cursor: pointer; font-weight: 600; font-size: 12px;
    transition: all .2s; white-space: nowrap;
}
.bayar-tagihan-btn:hover { background: #e96b05; } */

/* ===== JURNAL BUTTON ===== */
.jurnal-btn {
    display: flex; align-items: center; gap: 4px;
    background: #6d28d9; color: white; border: none; border-radius: 5px;
    padding: 4px 10px; cursor: pointer; font-weight: 600; font-size: 12px;
    transition: all .2s; white-space: nowrap;
}
.jurnal-btn:hover { background: #5b21b6; }

.pos-container {
    display: flex;
    gap: 10px;
    flex: 1;
    overflow: hidden;
    min-height: 0;
}

.pos-left {
    flex: 0 0 285px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    gap: 5px;
}

.pos-right {
    flex: 1;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    gap: 5px;
    min-height: 0;
}

.pos-box      { border: 1px solid #ddd; border-radius: 5px; overflow: auto; }
.pos-table    { margin: 0; font-size: 12px; }
.pos-table th { background: #f5f5f5; position: sticky; top: 0; z-index: 1;
                font-size: 12px; padding: 4px 6px; }
.pos-table td { vertical-align: middle; padding: 3px 6px; font-size: 12px; }

.qty-input   { width: 48px; text-align: center; font-size: 12px; padding: 1px 3px; }
.unit-select { width: 74px; font-size: 12px; padding: 1px 3px; }
.big-total   { font-size: 17px; font-weight: bold; }
.locked      { background: #eee; cursor: not-allowed; }
.member-info { font-size: 11px; color: #555; word-break: break-word; }

.section-label {
    font-size: 12px; font-weight: 700; color: #333; margin: 0; flex-shrink: 0;
}

.input-active {
    border-color: #0d6efd !important;
    box-shadow: 0 0 0 2px rgba(13,110,253,.2) !important;
    background-color: #f0f6ff !important;
}

#searchBox {
    border: 1px solid #ddd; border-radius: 5px 5px 0 0;
    overflow-x: auto; overflow-y: auto;
    max-height: calc(29px * 4 + 28px); flex-shrink: 0;
}
#searchBox table { margin: 0; font-size: 12px; white-space: nowrap; }
#searchBox thead th {
    background: #f5f5f5; position: sticky; top: 0; z-index: 2;
    font-size: 12px; padding: 4px 8px;
    border-bottom: 2px solid #ddd; white-space: nowrap;
}
#searchBox tbody td {
    vertical-align: middle; padding: 3px 8px;
    font-size: 12px; white-space: nowrap;
}

#searchResult tr.search-row-active td { background-color: #0d6efd !important; color: #fff !important; }
#searchResult tr.search-row-active td span { background: rgba(255,255,255,0.25) !important; color: #fff !important; }
#searchResult tr:hover td { background-color: #e8f0fe; }
#searchResult tr.search-row-active:hover td { background-color: #0b5ed7 !important; }

.search-nav-hint {
    display: none; font-size: 10px; color: #6c757d;
    padding: 2px 6px; background: #f8f9fa; border: 1px solid #ddd;
    border-top: none; border-radius: 0 0 4px 4px;
    text-align: center; flex-shrink: 0;
}
.search-nav-hint.show { display: block; }

#searchBox::-webkit-scrollbar        { width: 5px; height: 5px; }
#searchBox::-webkit-scrollbar-track  { background: #f1f1f1; }
#searchBox::-webkit-scrollbar-thumb  { background: #bbb; border-radius: 3px; }
#searchBox::-webkit-scrollbar-thumb:hover { background: #888; }

.cart-section {
    border: 1px solid #ddd; border-radius: 5px;
    overflow: hidden; display: flex; flex-direction: column; flex-shrink: 0;
}
.cart-table-header { background: #f5f5f5; border-bottom: 2px solid #ddd; flex-shrink: 0; }
.cart-table-header table { margin: 0; width: 100%; table-layout: fixed; font-size: 12px; }
.cart-table-header th    { font-weight: 600; padding: 5px 7px; }
.cart-table-body { overflow-y: auto; max-height: calc(32px * 4); flex-shrink: 0; }
.cart-table-body table { margin: 0; width: 100%; table-layout: fixed; font-size: 12px; }
.cart-table-body td    { padding: 3px 6px; vertical-align: middle; }

.cart-footer { border-top: 1px solid #ddd; padding-top: 6px; flex: 1; overflow-y: auto; min-height: 0; padding-right: 4px; }
.cart-footer::-webkit-scrollbar        { width: 5px; }
.cart-footer::-webkit-scrollbar-track  { background: #f1f1f1; }
.cart-footer::-webkit-scrollbar-thumb  { background: #ccc; border-radius: 3px; }
.total-row   { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3px; }

.trx-today-header { display: flex; align-items: center; gap: 7px; flex-shrink: 0; }
.pending-badge {
    display: inline-flex; align-items: center; justify-content: center;
    background: #dc3545; color: #fff; font-size: 10px; font-weight: 700;
    border-radius: 20px; padding: 2px 7px; min-width: 20px; height: 18px; line-height: 1;
    animation: pulse-badge 1.5s infinite;
}
.pending-badge.hidden { display: none; }
@keyframes pulse-badge {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: .8; transform: scale(1.08); }
}

.method-select {
    width: 100%; font-size: 12px; padding: 5px 8px;
    border: 1.5px solid #dee2e6; border-radius: 6px;
    background: #fff; cursor: pointer; transition: border-color .2s;
}
.method-select:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }

.panel-notice {
    background: #fffbe6; border: 1px solid #ffe58f; border-radius: 6px;
    padding: 6px 10px; font-size: 11px; color: #7a5400; margin-bottom: 5px;
}

.kredit-panel-full {
    background: linear-gradient(135deg, #fff8f0, #fff3e0);
    border: 2px solid #ffcc80; border-radius: 8px;
    padding: 8px 10px; margin-bottom: 5px;
    animation: slideDown .2s ease;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}

.kredit-header { display: flex; align-items: center; gap: 6px; margin-bottom: 7px; padding-bottom: 6px; border-bottom: 1px dashed #ffcc80; }
.kredit-header-icon  { font-size: 16px; }
.kredit-header-title { font-size: 12px; font-weight: 800; color: #c45c00; }
.kredit-header-sub   { font-size: 10px; color: #a06000; margin-top: 1px; }

.kredit-total-banner {
    background: #fff; border: 1.5px solid #ffb347; border-radius: 6px;
    padding: 5px 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px;
}
.kredit-total-banner .ktb-label  { font-size: 11px; color: #7a3b00; font-weight: 600; }
.kredit-total-banner .ktb-amount { font-size: 15px; font-weight: 900; color: #e67e00; }

.dp-box { background: #fff; border: 1.5px solid #a7f3d0; border-radius: 7px; padding: 8px 10px; margin-bottom: 7px; }
.dp-box-title { font-size: 10px; font-weight: 700; color: #065f46; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 5px; }
.dp-sisa-info {
    display: none; margin-top: 6px;
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;
    padding: 5px 10px; font-size: 11px; color: #166534;
}

.kredit-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; margin-bottom: 5px; }
.kredit-form-grid .kfg-full { grid-column: 1 / -1; }

.kredit-field label { font-size: 10px; font-weight: 700; color: #7a3b00; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: .3px; }
.kredit-field input, .kredit-field select, .kredit-field textarea {
    width: 100%; font-size: 12px; padding: 3px 7px;
    border: 1.5px solid #ffcc80; border-radius: 6px; background: #fffdf9; color: #333; transition: border-color .15s; outline: none;
}
.kredit-field input:focus, .kredit-field select:focus, .kredit-field textarea:focus {
    border-color: #e67e00; background: #fff; box-shadow: 0 0 0 2px rgba(230,126,0,.15);
}
.kredit-field textarea { resize: none; height: 38px; font-family: inherit; }
.dp-field input, .dp-field select { border-color: #6ee7b7 !important; }
.dp-field input:focus, .dp-field select:focus { border-color: #059669 !important; box-shadow: 0 0 0 2px rgba(5,150,105,.15) !important; }

.jatuh-tempo-chips { display: flex; gap: 3px; flex-wrap: wrap; margin-top: 3px; }
.jt-chip { background: #fff; border: 1.5px solid #ffcc80; border-radius: 20px; padding: 1px 7px; font-size: 10px; font-weight: 700; color: #8a4000; cursor: pointer; transition: all .12s; white-space: nowrap; }
.jt-chip:hover  { background: #ffe0b2; border-color: #e67e00; }
.jt-chip.active { background: #e67e00; border-color: #e67e00; color: #fff; }
.jt-info { background: #fff8e6; border: 1px solid #ffe0b2; border-radius: 5px; padding: 3px 8px; font-size: 10px; color: #7a3b00; display: flex; justify-content: space-between; align-items: center; margin-top: 3px; }
.jt-info .jt-date { font-weight: 700; color: #c45c00; }
.angsuran-info { background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 5px; padding: 3px 8px; font-size: 11px; color: #1b5e20; margin-top: 3px; display: none; }

.kredit-success-box { background: linear-gradient(135deg, #fff8f0, #fff3e0); border: 2px solid #e67e00; border-radius: 10px; padding: 14px; text-align: center; }
.kredit-success-box .ks-icon  { font-size: 28px; margin-bottom: 4px; }
.kredit-success-box .ks-title { font-weight: 800; color: #e67e00; font-size: 14px; margin-bottom: 2px; }
.kredit-success-box .ks-trx   { font-size: 11px; color: #aaa; margin-bottom: 4px; }
.kredit-success-box .ks-total { font-size: 12px; color: #7a3b00; margin-bottom: 6px; }
.kredit-success-box .ks-due   { font-size: 11px; background:#fff8e6; border:1px solid #ffe0b2; border-radius:5px; padding:4px 8px; color:#c45c00; margin-bottom:8px; display:inline-block; }
.kredit-success-box .ks-btns  { display: flex; gap: 6px; }
.kredit-success-box .ks-btns a, .kredit-success-box .ks-btns button {
    flex: 1; font-size: 11px; padding: 7px 4px; border-radius: 7px; border: none; cursor: pointer; font-weight: 700;
    text-decoration: none; display: flex; align-items: center; justify-content: center;
}

.form-control-xs { font-size: 12px; padding: 3px 7px; height: 28px; }
.form-control-xs:focus { outline: none; border-color: #86b7fe; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }
.alert-xs { font-size: 12px; padding: 4px 9px; margin-bottom: 0; border-radius: 4px; }
.btn-qty  { padding: 1px 6px; font-size: 12px; line-height: 1.5; }

#barcode.adding, #search.adding {
    background-color: #fff8e1 !important;
    border-color: #ffc107 !important;
}

/* ============================================================
   PANEL BAYAR TAGIHAN LAIN — OVERLAY SLIDE-IN
   ============================================================ */
#tagihanOverlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1050;
    display: none;
    align-items: stretch;
    justify-content: flex-end;
    backdrop-filter: blur(2px);
}
#tagihanOverlay.show { display: flex; }

#tagihanPanel {
    width: 460px;
    max-width: 98vw;
    background: #fff;
    box-shadow: -6px 0 32px rgba(0,0,0,.18);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideInRight .22s ease;
}
@keyframes slideInRight {
    from { transform: translateX(60px); opacity: 0; }
    to   { transform: translateX(0);    opacity: 1; }
}

/* Header panel */
.tg-header {
    background: linear-gradient(135deg, #fd7e14, #e96b05);
    color: #fff;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.tg-header-title { font-size: 14px; font-weight: 800; }
.tg-header-sub   { font-size: 11px; opacity: .85; margin-top: 1px; }
.tg-close-btn {
    background: rgba(255,255,255,.22); border: none; color: #fff;
    border-radius: 6px; padding: 4px 10px; cursor: pointer; font-size: 16px; line-height: 1; transition: background .15s;
}
.tg-close-btn:hover { background: rgba(255,255,255,.38); }

/* Content scroll */
.tg-content {
    flex: 1; overflow-y: auto; padding: 14px 16px;
    display: flex; flex-direction: column; gap: 10px;
}
.tg-content::-webkit-scrollbar { width: 5px; }
.tg-content::-webkit-scrollbar-track { background: #f1f1f1; }
.tg-content::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }

/* Kategori chips */
.tg-kategori-wrap {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 6px;
}
.tg-kategori-chip {
    border: 1.5px solid #fde2c2;
    border-radius: 8px;
    padding: 7px 4px;
    text-align: center;
    cursor: pointer;
    background: #fff9f5;
    transition: all .15s;
    font-size: 11px;
    color: #7a3b00;
    font-weight: 600;
}
.tg-kategori-chip:hover  { border-color: #fd7e14; background: #fff0e0; }
.tg-kategori-chip.active { border-color: #fd7e14; background: #fd7e14; color: #fff; box-shadow: 0 2px 8px rgba(253,126,20,.25); }
.tg-kategori-chip .tg-chip-icon { font-size: 18px; display: block; margin-bottom: 2px; }

/* Section title */
.tg-section-title {
    font-size: 11px; font-weight: 800; color: #555;
    text-transform: uppercase; letter-spacing: .4px;
    border-bottom: 1.5px solid #f0e8d8; padding-bottom: 5px;
    display: flex; align-items: center; gap: 5px;
}

/* Form fields */
.tg-field { margin-bottom: 0; }
.tg-field label {
    font-size: 11px; font-weight: 700; color: #555;
    display: block; margin-bottom: 3px;
}
.tg-field input, .tg-field select, .tg-field textarea {
    width: 100%; font-size: 12px; padding: 7px 10px;
    border: 1.5px solid #dee2e6; border-radius: 7px;
    outline: none; transition: border-color .15s; background: #fff;
    font-family: inherit;
}
.tg-field input:focus, .tg-field select:focus, .tg-field textarea:focus {
    border-color: #fd7e14; box-shadow: 0 0 0 2px rgba(253,126,20,.15);
}
.tg-field textarea { resize: none; height: 56px; }

.tg-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }

/* Nominal besar */
.tg-nominal-wrap { position: relative; }
.tg-nominal-prefix {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    font-size: 13px; font-weight: 700; color: #888; pointer-events: none;
}
.tg-nominal-input {
    padding-left: 34px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    color: #333 !important;
}

/* Quick nominal chips */
.tg-quick-nominal {
    display: flex; gap: 5px; flex-wrap: wrap; margin-top: 4px;
}
.tg-qn-chip {
    background: #fff; border: 1.5px solid #fde2c2; border-radius: 20px;
    padding: 2px 10px; font-size: 11px; font-weight: 700; color: #c45c00;
    cursor: pointer; transition: all .12s; white-space: nowrap;
}
.tg-qn-chip:hover  { background: #fff0e0; border-color: #fd7e14; }
.tg-qn-chip.active { background: #fd7e14; border-color: #fd7e14; color: #fff; }

/* Metode bayar pills */
.tg-method-pills { display: flex; gap: 6px; }
.tg-method-pill {
    flex: 1; text-align: center; padding: 7px 4px;
    border: 1.5px solid #dee2e6; border-radius: 8px;
    font-size: 11px; font-weight: 700; color: #555; cursor: pointer;
    transition: all .15s; background: #fff;
}
.tg-method-pill:hover  { border-color: #fd7e14; background: #fff9f5; }
.tg-method-pill.active { border-color: #fd7e14; background: #fd7e14; color: #fff; box-shadow: 0 2px 6px rgba(253,126,20,.2); }
.tg-method-pill .tg-pill-icon { font-size: 16px; display: block; margin-bottom: 2px; }

/* Ringkasan tagihan */
.tg-summary {
    background: linear-gradient(135deg, #fff9f0, #fff3e0);
    border: 2px solid #fde2c2; border-radius: 10px;
    padding: 12px 14px;
}
.tg-summary-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 12px; }
.tg-summary-row:last-child { margin-bottom: 0; }
.tg-summary-label { color: #7a3b00; font-weight: 600; }
.tg-summary-val   { font-weight: 700; color: #333; }
.tg-summary-total-row {
    display: flex; justify-content: space-between; align-items: center;
    border-top: 1px dashed #fde2c2; margin-top: 8px; padding-top: 8px;
}
.tg-summary-total-label { font-size: 13px; font-weight: 700; color: #c45c00; }
.tg-summary-total-val   { font-size: 18px; font-weight: 900; color: #fd7e14; }

/* Tombol simpan */
.tg-submit-btn {
    width: 100%; padding: 12px; font-size: 14px; font-weight: 800; color: #fff;
    background: linear-gradient(135deg, #fd7e14, #e96b05);
    border: none; border-radius: 10px; cursor: pointer; transition: all .15s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.tg-submit-btn:hover    { box-shadow: 0 4px 16px rgba(253,126,20,.35); transform: translateY(-1px); }
.tg-submit-btn:disabled { background: #adb5bd; cursor: not-allowed; box-shadow: none; transform: none; }

/* History tagihan mini */
.tg-history-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 7px 10px; border: 1px solid #fde2c2; border-radius: 7px;
    background: #fff9f5; font-size: 11px;
}
.tg-history-name   { font-weight: 700; color: #7a3b00; }
.tg-history-amount { font-weight: 800; color: #fd7e14; }
.tg-history-meta   { color: #aaa; font-size: 10px; margin-top: 1px; }

/* Success state */
.tg-success-box {
    background: linear-gradient(135deg, #fff9f0, #fff3e0);
    border: 2px solid #fd7e14; border-radius: 14px;
    padding: 24px 20px; text-align: center;
    animation: slideDown .2s ease;
}
.tg-success-box .tgs-icon  { font-size: 44px; margin-bottom: 8px; }
.tg-success-box .tgs-title { font-size: 16px; font-weight: 900; color: #c45c00; margin-bottom: 4px; }
.tg-success-box .tgs-trx   { font-size: 11px; color: #aaa; margin-bottom: 10px; }
.tg-success-detail {
    background: #fff; border: 1.5px solid #fde2c2; border-radius: 8px;
    padding: 10px 14px; margin-bottom: 12px; text-align: left;
}
.tg-success-detail-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; }
.tg-success-detail-row:last-child { margin-bottom: 0; }
.tg-success-detail-row .sdl { color: #7a3b00; font-weight: 600; }
.tg-success-detail-row .sdv { font-weight: 700; }
.tg-success-btns { display: flex; gap: 7px; }
.tg-success-btns button {
    flex: 1; font-size: 12px; padding: 9px 4px;
    border-radius: 8px; border: none; cursor: pointer; font-weight: 700;
}

/* Loading */
.tg-loading {
    display: flex; align-items: center; justify-content: center;
    gap: 8px; padding: 16px; color: #6c757d; font-size: 12px;
}
.tg-spinner {
    width: 18px; height: 18px;
    border: 2px solid #dee2e6; border-top-color: #fd7e14;
    border-radius: 50%; animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty history */
.tg-empty { text-align: center; padding: 12px; color: #ccc; font-size: 11px; }

/* ============================================================
   PANEL JURNAL UMUM — OVERLAY SLIDE-IN (ungu)
   ============================================================ */
#jurnalOverlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    z-index: 1060;                  /* lebih tinggi dari tagihan */
    display: none;
    align-items: stretch;
    justify-content: flex-end;
    backdrop-filter: blur(2px);
}
#jurnalOverlay.show { display: flex; }

#jurnalPanel {
    width: 82vw;                    /* lebih lebar agar tabel jurnal nyaman */
    max-width: 1100px;
    background: #fff;
    box-shadow: -6px 0 32px rgba(0,0,0,.22);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideInRight .22s ease;
}

/* Header panel jurnal */
.jr-header {
    background: linear-gradient(135deg, #6d28d9, #5b21b6);
    color: #fff;
    padding: 12px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.jr-header-title { font-size: 15px; font-weight: 800; }
.jr-header-sub   { font-size: 11px; opacity: .82; margin-top: 2px; }
.jr-close-btn {
    background: rgba(255,255,255,.22); border: none; color: #fff;
    border-radius: 6px; padding: 5px 12px; cursor: pointer; font-size: 16px; line-height: 1;
    transition: background .15s; white-space: nowrap;
}
.jr-close-btn:hover { background: rgba(255,255,255,.38); }

/* Toolbar iframe */
.jr-toolbar {
    background: #f3f0ff;
    border-bottom: 1.5px solid #ddd6fe;
    padding: 6px 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
    font-size: 12px;
    color: #5b21b6;
}
.jr-toolbar a {
    background: #6d28d9; color: #fff;
    padding: 4px 12px; border-radius: 5px; text-decoration: none;
    font-size: 12px; font-weight: 700; transition: background .15s;
}
.jr-toolbar a:hover { background: #5b21b6; }

/* Iframe jurnal — mengisi sisa tinggi panel */
#jurnalFrame {
    flex: 1;
    border: none;
    width: 100%;
    min-height: 0;
}

/* Spinner saat iframe loading */
.jr-loading {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 40px; color: #6c757d; font-size: 13px;
    position: absolute; inset: 60px 0 0 0; background: #fff; z-index: 1;
}
.jr-spinner {
    width: 24px; height: 24px;
    border: 3px solid #ddd6fe; border-top-color: #6d28d9;
    border-radius: 50%; animation: spin .7s linear infinite;
}
</style>

<div class="pos-wrapper">

    {{-- HEADER --}}
    <div class="trx-header">
        <div class="trx-left">
            <span class="trx-number">{{ $trx->trx_number }}</span>
            <span class="trx-time">{{ $trx->created_at->format('d M Y') }} • {{ $trx->created_at->format('H:i:s') }}</span>
        </div>
        <div class="trx-right">
            {{-- ★ TOMBOL JURNAL BARU ★ --}}
            <button class="jurnal-btn" onclick="openJurnal()">
                📒 Jurnal Umum
            </button>
            {{-- <button class="bayar-tagihan-btn" onclick="openTagihan()">
                🧾 Bayar Tagihan
            </button> --}}
            <button class="new-transaction-btn" onclick="createNewTransaction()">
                + Transaksi Baru
            </button>
        </div>
    </div>

    <div class="pos-container">

        {{-- ========== KOLOM KIRI ========== --}}
        <div class="pos-left">

            <input type="hidden" id="warehouse_id" value="{{ $activeWarehouse->id }}">

            <div class="alert alert-info alert-xs">
                Gudang: <strong>{{ $activeWarehouse->name }}</strong>
            </div>

            <input type="text" id="barcode" class="form-control form-control-xs"
                   placeholder="① Scan barcode / Enter untuk lanjut">
            <input type="text" id="search"  class="form-control form-control-xs"
                   placeholder="② Cari produk — ↑↓ pilih, ←→ geser kolom, Enter ambil">

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
                                <th style="min-width:58px; text-align:center;" title="{{ $wh->name }}">
                                    Stok {{ chr(65 + $idx) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="searchResult">
                        <tr>
                            <td colspan="{{ 4 + count($warehouses) }}" class="text-center text-muted"
                                style="font-size:11px; padding:6px;">
                                Ketik minimal 2 karakter untuk mencari produk
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="search-nav-hint" id="searchNavHint">
                ↑↓ Pilih baris &nbsp;|&nbsp; ←→ Geser kolom &nbsp;|&nbsp; Enter Ambil &nbsp;|&nbsp; Esc Tutup
            </div>

            {{-- MEMBER --}}
            <div style="flex-shrink:0;">
                <span class="section-label">③ Member</span>
                <input type="text" id="member" class="form-control form-control-xs locked mt-1"
                       placeholder="Klik untuk input member" readonly onclick="unlockMember()">
                <div id="memberResult" class="border mt-1" style="max-height:65px; overflow:auto;"></div>
                <div id="memberInfo" class="mt-1 member-info"></div>
            </div>

            {{-- TRANSAKSI HARI INI --}}
            <div class="trx-today-header">
                <span class="section-label">Transaksi Hari Ini</span>
                @php $pendingCount = $todayTransactions->where('status','pending')->count(); @endphp
                <span class="pending-badge {{ $pendingCount == 0 ? 'hidden' : '' }}"
                      id="pendingBadge" title="{{ $pendingCount }} transaksi pending">
                    {{ $pendingCount }} Pending
                </span>
            </div>

            <div class="pos-box" style="flex:1; overflow-y:auto; min-height:0;">
                <table class="table table-sm pos-table mb-0">
                    <thead>
                        <tr>
                            <th>No</th><th>Transaksi</th><th>Jam</th><th>Total</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todayTransactions as $t)
                        <tr style="font-size:11px; cursor:pointer;"
                            onclick="{{ $t->status=='pending' ? "openPending({$t->id})" : "openPaidTransaction({$t->id})" }}"
                            title="{{ $t->status=='paid' ? 'Klik untuk buka kembali transaksi (butuh password)' : 'Klik untuk lanjutkan transaksi' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $t->trx_number }}</td>
                            <td>{{ $t->created_at->format('H:i') }}</td>
                            <td>Rp {{ number_format($t->total) }}</td>
                            <td>
                                @if($t->status=='paid')
                                    <span class="badge bg-success" style="font-size:10px;">✓ Paid</span>
                                @elseif($t->status=='kredit')
                                    <span class="badge bg-warning text-dark" style="font-size:10px;">💳 Kredit</span>
                                @elseif($t->status=='bayar_tagihan')
                                    <span class="badge bg-info text-dark" style="font-size:10px;">🧾 Tagihan</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size:10px;">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- ========== KOLOM KANAN ========== --}}
        <div class="pos-right">

            {{-- KERANJANG --}}
            <div class="cart-section">
                <div class="cart-table-header">
                    <table class="table table-sm mb-0">
                        <colgroup>
                            <col style="width:28px"><col>
                            <col style="width:78px"><col style="width:125px"><col style="width:95px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>*</th><th>Nama Produk</th><th>Satuan</th>
                                <th>Qty</th><th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <div class="cart-table-body">
                    <table class="table table-bordered table-sm mb-0">
                        <colgroup>
                            <col style="width:28px"><col>
                            <col style="width:78px"><col style="width:125px"><col style="width:95px">
                        </colgroup>
                        <tbody id="cartBody">
                            @php $total = 0; @endphp
                            @foreach($trx->items as $i)
                            @php $sub = $i->price * $i->qty; $total += $sub; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $i->unit->product->name }}
                                    <br><small class="text-muted" style="font-size:10px;">{{ $i->unit->barcode ?? '-' }}</small>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm unit-select"
                                            style="font-size:11px; padding:1px 3px;"
                                            onchange="updateUnit({{ $i->id }},this.value)">
                                        @foreach($i->unit->product->units as $u)
                                        <option value="{{ $u->id }}" {{ $u->id==$i->product_unit_id ? 'selected':'' }}>
                                            {{ $u->unit_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <button class="btn btn-sm btn-outline-secondary btn-qty"
                                                onclick="minusQty({{ $i->id }})">−</button>
                                        <input type="number" class="form-control form-control-sm qty-input"
                                               value="{{ $i->qty }}"
                                               onchange="updateQtyManual({{ $i->id }},this.value)">
                                        <button class="btn btn-sm btn-outline-secondary btn-qty"
                                                onclick="plusQty({{ $i->id }})">+</button>
                                        <button class="btn btn-sm btn-danger btn-qty"
                                                onclick="removeItemWithAuth({{ $i->id }}, '{{ addslashes($i->unit->product->name) }}')">🗑</button>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold" style="font-size:12px;">
                                    Rp {{ number_format($sub) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- FOOTER PEMBAYARAN --}}
            <div class="cart-footer">

                <div class="total-row">
                    <span style="font-size:14px; color:#6c757d; font-weight:600;">Total</span>
                    <span class="big-total" id="totalText"
                          data-total="{{ $total }}" data-original="{{ $total }}">
                        Rp {{ number_format($total) }}
                    </span>
                </div>

                <div class="total-row">
                    <span style="font-size:12px;">④ Diskon (Rp):</span>
                    <input type="number" id="discount_rp" class="form-control locked"
                           style="width:95px; font-size:12px; padding:2px 7px; height:28px;"
                           placeholder="Diskon (Rp)" readonly onclick="unlockDiscountRp()">
                </div>

                <div class="total-row">
                    <span style="font-size:12px;">⑤ Diskon (%):</span>
                    <input type="number" id="discount_percent" class="form-control locked"
                           style="width:95px; font-size:12px; padding:2px 7px; height:28px;"
                           placeholder="Diskon (%)" readonly onclick="unlockDiscountPercent()">
                </div>

                <div style="margin-bottom:6px;">
                    <label style="font-size:11px; font-weight:700; color:#555; display:block; margin-bottom:3px;">
                        ⑥ Metode Pembayaran
                    </label>
                    <select id="paymentMethod" class="method-select" onchange="onMethodChange(this.value)">
                        <option value="cash">💵 Cash / Tunai</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="qris">📱 QRIS</option>
                        <option value="kredit">📋 Kredit / Hutang</option>
                    </select>
                </div>

                <div id="panelNotice" class="panel-notice" style="display:none;"></div>

                <div id="panelCash">
                    <input type="number" id="paid" class="form-control form-control-xs"
                           placeholder="⑦ Jumlah bayar → Enter untuk bayar">
                    <div class="total-row mt-1">
                        <span style="font-size:13px;">Kembalian:</span>
                        <span id="changeText" class="big-total" style="color:#28a745; font-size:15px;">Rp 0</span>
                    </div>
                </div>

                <div id="panelKredit" style="display:none;">
                    <div class="kredit-panel-full">
                        <div class="kredit-header">
                            <div class="kredit-header-icon">📋</div>
                            <div>
                                <div class="kredit-header-title">Transaksi Kredit / Hutang</div>
                                <div class="kredit-header-sub">Stok dikurangi · Pembayaran ditangguhkan · Catat sebagai piutang</div>
                            </div>
                        </div>
                        <div class="kredit-total-banner">
                            <span class="ktb-label">💰 Total Belanja</span>
                            <span class="ktb-amount" id="kreditTotalBelanja">Rp 0</span>
                        </div>
                        <div class="dp-box">
                            <div class="dp-box-title">💵 Uang Muka / DP (opsional)</div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:6px;">
                                <div class="kredit-field dp-field">
                                    <label>Jumlah DP</label>
                                    <input type="number" id="kreditDP" min="0" placeholder="0 = tidak ada DP" oninput="updateKreditSisa()">
                                </div>
                                <div class="kredit-field dp-field">
                                    <label>Metode DP</label>
                                    <select id="kreditDPMethod">
                                        <option value="cash">💵 Cash</option>
                                        <option value="transfer">🏦 Transfer</option>
                                        <option value="qris">📱 QRIS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="dp-sisa-info" id="kreditSisaBox">
                                DP dibayar: <strong id="kreditDPText">Rp 0</strong> &nbsp;→&nbsp;
                                Sisa hutang: <strong id="kreditSisaText" style="color:#dc2626;">Rp 0</strong>
                            </div>
                        </div>
                        <div class="kredit-total-banner" style="border-color:#fca5a5; background:#fff5f5;">
                            <span class="ktb-label" style="color:#991b1b;">📌 Sisa Hutang</span>
                            <span class="ktb-amount" id="kreditTotal" style="color:#dc2626;">Rp 0</span>
                        </div>
                        <div class="kredit-form-grid">
                            <div class="kredit-field kfg-full">
                                <label>👤 Nama Peminjam / Pelanggan</label>
                                <input type="text" id="kreditNama" placeholder="Nama pelanggan (opsional jika ada member)">
                            </div>
                            <div class="kredit-field">
                                <label>📱 No. Telepon</label>
                                <input type="text" id="kreditTelp" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="kredit-field">
                                <label>💳 Rencana Cara Bayar</label>
                                <select id="kreditCaraBayar">
                                    <option value="cash">💵 Cash / Tunai</option>
                                    <option value="transfer">🏦 Transfer Bank</option>
                                    <option value="qris">📱 QRIS</option>
                                    <option value="cicilan">📆 Cicilan</option>
                                </select>
                            </div>
                            <div class="kredit-field kfg-full" id="kreditCicilanGroup" style="display:none;">
                                <label>📆 Jumlah Cicilan (kali)</label>
                                <input type="number" id="kreditCicilan" min="2" max="36" value="3" placeholder="Misal: 3" oninput="hitungCicilan()">
                                <div class="angsuran-info" id="angsuranInfo"></div>
                            </div>
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
                            <div class="jt-info" id="jtInfo">
                                <span>⏳ Jatuh tempo:</span>
                                <span class="jt-date" id="jtInfoDate">—</span>
                            </div>
                        </div>
                        <div class="kredit-field" style="margin-bottom:0; margin-top:0;">
                            <label>📝 Catatan / Keterangan</label>
                            <textarea id="kreditCatatan" placeholder="Misal: pembayaran saat gajian, transfer ke BCA 123xxx..."></textarea>
                        </div>
                    </div>
                </div>

                <button id="btnPay" class="btn btn-primary btn-sm w-100 mt-1"
                        style="font-size:13px;" onclick="processPay()">
                    💳 Simpan / Bayar
                </button>
                <button id="btnKredit" class="btn btn-sm w-100 mt-1"
                        style="font-size:13px; display:none; color:#fff; background:#e67e00; border:none; border-radius:6px; font-weight:700; padding:8px;"
                        onclick="processPay()">
                    📋 Simpan sebagai Kredit / Hutang
                </button>

            </div>

        </div>
    </div>
</div>

{{-- ============================================================
     OVERLAY BAYAR TAGIHAN
     ============================================================ --}}
<div id="tagihanOverlay" onclick="handleTagihanOverlayClick(event)">
    <div id="tagihanPanel">

        <div class="tg-header">
            <div>
                <div class="tg-header-title">🧾 Bayar Tagihan</div>
                <div class="tg-header-sub">Listrik · Air · Internet · TV · Gas · dan lainnya</div>
            </div>
            <button class="tg-close-btn" onclick="closeTagihan()">✕</button>
        </div>

        <div class="tg-content" id="tgContent">

            <div id="tgStateForm">

                <div>
                    <div class="tg-section-title">📂 Jenis Tagihan</div>
                    <div class="tg-kategori-wrap" style="margin-top:8px;">
                        <div class="tg-kategori-chip active" data-kategori="Listrik" onclick="tgSetKategori(this,'Listrik')">
                            <span class="tg-chip-icon">⚡</span>Listrik
                        </div>
                        <div class="tg-kategori-chip" data-kategori="Air" onclick="tgSetKategori(this,'Air')">
                            <span class="tg-chip-icon">💧</span>Air
                        </div>
                        <div class="tg-kategori-chip" data-kategori="Internet" onclick="tgSetKategori(this,'Internet')">
                            <span class="tg-chip-icon">📶</span>Internet
                        </div>
                        <div class="tg-kategori-chip" data-kategori="TV Kabel" onclick="tgSetKategori(this,'TV Kabel')">
                            <span class="tg-chip-icon">📺</span>TV Kabel
                        </div>
                        <div class="tg-kategori-chip" data-kategori="Gas" onclick="tgSetKategori(this,'Gas')">
                            <span class="tg-chip-icon">🔥</span>Gas
                        </div>
                        <div class="tg-kategori-chip" data-kategori="Telepon" onclick="tgSetKategori(this,'Telepon')">
                            <span class="tg-chip-icon">📞</span>Telepon
                        </div>
                        <div class="tg-kategori-chip" data-kategori="BPJS" onclick="tgSetKategori(this,'BPJS')">
                            <span class="tg-chip-icon">🏥</span>BPJS
                        </div>
                        <div class="tg-kategori-chip" data-kategori="Lainnya" onclick="tgSetKategori(this,'Lainnya')">
                            <span class="tg-chip-icon">📋</span>Lainnya
                        </div>
                    </div>
                </div>

                <div id="tgNamaCustomWrap" style="display:none;">
                    <div class="tg-field">
                        <label>📝 Nama Tagihan</label>
                        <input type="text" id="tgNamaCustom" placeholder="Contoh: Iuran RT, Sewa Tempat, dll">
                    </div>
                </div>

                <div class="tg-grid-2">
                    <div class="tg-field">
                        <label>🔢 No. Rekening / ID Pelanggan</label>
                        <input type="text" id="tgNoRek" placeholder="Opsional">
                    </div>
                    <div class="tg-field">
                        <label>📅 Periode / Bulan</label>
                        <input type="month" id="tgPeriode">
                    </div>
                </div>

                <div>
                    <div class="tg-section-title">💰 Nominal Tagihan</div>
                    <div class="tg-nominal-wrap" style="margin-top:8px;">
                        <span class="tg-nominal-prefix">Rp</span>
                        <input type="number" id="tgNominal" class="tg-field tg-nominal-input"
                               style="width:100%; font-size:15px; padding:7px 10px 7px 34px; border:1.5px solid #dee2e6; border-radius:7px; outline:none;"
                               placeholder="0" oninput="tgUpdateSummary()" min="0">
                    </div>
                    <div class="tg-quick-nominal" id="tgQuickNominal">
                        <span class="tg-qn-chip" onclick="tgSetNominal(50000)">50rb</span>
                        <span class="tg-qn-chip" onclick="tgSetNominal(100000)">100rb</span>
                        <span class="tg-qn-chip" onclick="tgSetNominal(150000)">150rb</span>
                        <span class="tg-qn-chip" onclick="tgSetNominal(200000)">200rb</span>
                        <span class="tg-qn-chip" onclick="tgSetNominal(300000)">300rb</span>
                        <span class="tg-qn-chip" onclick="tgSetNominal(500000)">500rb</span>
                    </div>
                </div>

                <div class="tg-field">
                    <label>🏷️ Biaya Admin / Jasa (opsional)</label>
                    <input type="number" id="tgBiayaAdmin" placeholder="0" min="0" oninput="tgUpdateSummary()">
                </div>

                <div>
                    <div class="tg-section-title">💳 Metode Pembayaran</div>
                    <div class="tg-method-pills" style="margin-top:8px;">
                        <div class="tg-method-pill active" data-method="cash" onclick="tgSetMethod(this,'cash')">
                            <span class="tg-pill-icon">💵</span>Cash
                        </div>
                        <div class="tg-method-pill" data-method="transfer" onclick="tgSetMethod(this,'transfer')">
                            <span class="tg-pill-icon">🏦</span>Transfer
                        </div>
                        <div class="tg-method-pill" data-method="qris" onclick="tgSetMethod(this,'qris')">
                            <span class="tg-pill-icon">📱</span>QRIS
                        </div>
                    </div>
                </div>

                <div class="tg-field">
                    <label>👤 Nama Pembayar (opsional)</label>
                    <input type="text" id="tgNamaBayar" placeholder="Nama pelanggan / pemilik tagihan">
                </div>

                <div class="tg-field">
                    <label>📝 Catatan (opsional)</label>
                    <textarea id="tgCatatan" placeholder="Misal: PLN prabayar token 200rb, tagihan bulan Maret..."></textarea>
                </div>

                <div class="tg-summary" id="tgSummary">
                    <div class="tg-section-title" style="margin-bottom:8px;">📋 Ringkasan Pembayaran</div>
                    <div class="tg-summary-row">
                        <span class="tg-summary-label">Jenis Tagihan</span>
                        <span class="tg-summary-val" id="tgSumKategori">Listrik</span>
                    </div>
                    <div class="tg-summary-row">
                        <span class="tg-summary-label">Nominal Tagihan</span>
                        <span class="tg-summary-val" id="tgSumNominal">Rp 0</span>
                    </div>
                    <div class="tg-summary-row" id="tgSumAdminRow" style="display:none;">
                        <span class="tg-summary-label">Biaya Admin</span>
                        <span class="tg-summary-val" id="tgSumAdmin">Rp 0</span>
                    </div>
                    <div class="tg-summary-row">
                        <span class="tg-summary-label">Metode Bayar</span>
                        <span class="tg-summary-val" id="tgSumMethod">💵 Cash</span>
                    </div>
                    <div class="tg-summary-total-row">
                        <span class="tg-summary-total-label">💰 Total Bayar</span>
                        <span class="tg-summary-total-val" id="tgSumTotal">Rp 0</span>
                    </div>
                </div>

                <button class="tg-submit-btn" id="tgSubmitBtn" onclick="tgProcessPay()">
                    🧾 Simpan Pembayaran Tagihan
                </button>

                <div>
                    <div class="tg-section-title">🕐 Tagihan Hari Ini</div>
                    <div id="tgHistoryList" style="margin-top:8px; display:flex; flex-direction:column; gap:5px;">
                        <div class="tg-loading" id="tgHistoryLoading" style="display:none;">
                            <div class="tg-spinner"></div> Memuat...
                        </div>
                        <div class="tg-empty" id="tgHistoryEmpty">Belum ada pembayaran tagihan hari ini.</div>
                    </div>
                </div>

            </div>

            <div id="tgStateSuccess" style="display:none;">
                <div class="tg-success-box">
                    <div class="tgs-icon">✅</div>
                    <div class="tgs-title">Tagihan Berhasil Dibayar!</div>
                    <div class="tgs-trx" id="tgSuccessTrxNum">—</div>
                    <div class="tg-success-detail" id="tgSuccessDetail">—</div>
                    <div class="tg-success-btns">
                        <button onclick="tgPrintStruk()" style="background:#fd7e14; color:#fff;">
                            🖨️ Cetak Struk
                        </button>
                        <button onclick="tgReset()" style="background:#28a745; color:#fff;">
                            ✚ Tagihan Baru
                        </button>
                        <button onclick="closeTagihan()" style="background:#6c757d; color:#fff;">
                            ✕ Tutup
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ============================================================
     OVERLAY JURNAL UMUM — slide-in dengan iframe
     ============================================================ --}}
<div id="jurnalOverlay" onclick="handleJurnalOverlayClick(event)">
    <div id="jurnalPanel">

        {{-- Header --}}
        <div class="jr-header">
            <div>
                <div class="jr-header-title">📒 Jurnal Umum Detail</div>
                <div class="jr-header-sub">Data transaksi dalam format jurnal akuntansi · Halaman kasir tetap aktif</div>
            </div>
            <div style="display:flex; gap:8px; align-items:center;">
                {{-- Tombol buka di tab baru (opsional) --}}
                <a id="jurnalNewTabBtn" href="{{ route('reports.journal') }}" target="_blank"
                   style="background:rgba(255,255,255,.22); color:#fff; border-radius:6px; padding:5px 12px;
                          font-size:12px; font-weight:700; text-decoration:none; white-space:nowrap;">
                    ↗ Tab Baru
                </a>
                <button class="jr-close-btn" onclick="closeJurnal()">✕ Tutup</button>
            </div>
        </div>

        {{-- Spinner saat iframe load --}}
        <div class="jr-loading" id="jurnalLoading">
            <div class="jr-spinner"></div> Memuat jurnal...
        </div>

        {{-- Iframe — menampilkan halaman journal.blade.php --}}
        <iframe id="jurnalFrame"
                src="about:blank"
                onload="onJurnalFrameLoad()"
                style="display:none;">
        </iframe>

    </div>
</div>

<script>
let TRX  = {{ $trx->id }};
const csrf = '{{ csrf_token() }}';
const warehouseList = @json($warehousesJson);

/* Route jurnal — sesuaikan jika nama route berbeda */
const JURNAL_URL = '{{ route("reports.journal") }}';

function getWarehouseId() { return document.getElementById('warehouse_id').value; }

const jsonHeaders = {
    'Content-Type' : 'application/json',
    'X-CSRF-TOKEN' : csrf,
    'Accept'       : 'application/json'
};

let memberUnlocked        = false;
let manualDiscountRp      = 0;
let manualDiscountPercent = 0;
let memberDiscount        = 0;
let selectedPaymentMethod = 'cash';
let isAdding              = false;
let isScanPending         = false;
let selectedSearchIdx     = -1;

/* ── Search highlight ─────────────────────────────────────── */
function updateSearchHighlight() {
    const rows = document.querySelectorAll('#searchResult tr[data-unit-id]');
    rows.forEach((row, i) => row.classList.toggle('search-row-active', i === selectedSearchIdx));
    if (selectedSearchIdx >= 0 && rows[selectedSearchIdx])
        rows[selectedSearchIdx].scrollIntoView({ block: 'nearest' });
}
function showSearchHint(show) { document.getElementById('searchNavHint').classList.toggle('show', show); }
function resetSearchSelection() { selectedSearchIdx = -1; updateSearchHighlight(); showSearchHint(false); }

const NAV_ORDER = ['barcode','search','member','discount_rp','discount_percent','paid'];

function focusNext(currentId) {
    const idx = NAV_ORDER.indexOf(currentId);
    if (idx === -1) return;
    if (idx === NAV_ORDER.length - 1) { document.getElementById('btnPay').click(); return; }
    const nextId = NAV_ORDER[idx + 1];
    const nextEl = document.getElementById(nextId);
    if (!nextEl) return;
    if (nextEl.readOnly || nextEl.classList.contains('locked')) { focusNext(nextId); return; }
    nextEl.focus(); nextEl.select && nextEl.select(); highlightActive(nextId);
}

function highlightActive(activeId) {
    NAV_ORDER.forEach(id => { const el = document.getElementById(id); if (el) el.classList.remove('input-active'); });
    const el = document.getElementById(activeId);
    if (el) el.classList.add('input-active');
}

/* ── Barcode ──────────────────────────────────────────────── */
document.getElementById('barcode').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    if (isScanPending || isAdding) return;
    const code = this.value.trim();
    if (code === '') { focusNext('barcode'); return; }
    isScanPending = true;
    document.getElementById('barcode').classList.add('adding');
    document.getElementById('search').classList.add('adding');
    const barcodeEl = this;
    fetch('/pos/scan', { method:'POST', headers:jsonHeaders, body: JSON.stringify({ code, warehouse_id: getWarehouseId() }) })
    .then(r => r.json())
    .then(r => {
        isScanPending = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        if (!r.success) { alert(r.message); return; }
        barcodeEl.value = '';
        add(r.id);
        barcodeEl.focus(); highlightActive('barcode');
    })
    .catch(err => {
        isScanPending = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        console.error('Scan error:', err);
        alert('Gagal scan barcode. Coba lagi.');
    });
});
document.getElementById('barcode').addEventListener('focus', () => highlightActive('barcode'));

let isFromKeyboard = false;

/* ── Search product ───────────────────────────────────────── */
document.getElementById('search').addEventListener('keydown', function (e) {
    const box  = document.getElementById('searchBox');
    const rows = document.querySelectorAll('#searchResult tr[data-unit-id]');
    const rc   = rows.length;
    if (e.key === 'ArrowDown') { e.preventDefault(); if (rc) { selectedSearchIdx = Math.min(selectedSearchIdx+1, rc-1); updateSearchHighlight(); } return; }
    if (e.key === 'ArrowUp')   { e.preventDefault(); if (rc) { selectedSearchIdx = selectedSearchIdx <= 0 ? -1 : selectedSearchIdx-1; updateSearchHighlight(); } return; }
    if (e.key === 'ArrowRight'){ e.preventDefault(); box.scrollLeft += 80; return; }
    if (e.key === 'ArrowLeft') { e.preventDefault(); box.scrollLeft -= 80; return; }
    if (e.key === 'Escape') {
        e.preventDefault();
        document.getElementById('searchResult').innerHTML = `<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:11px;padding:6px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
        this.value = ''; resetSearchSelection(); return;
    }
    if (e.key === 'Enter') {
        e.preventDefault();
        if (isScanPending || isAdding) return;
        if (selectedSearchIdx >= 0 && rows[selectedSearchIdx]) {
            isFromKeyboard = true; addFromSearch(Number(rows[selectedSearchIdx].dataset.unitId));
            setTimeout(() => { isFromKeyboard = false; }, 300); return;
        }
        if (rc === 1) { isFromKeyboard = true; addFromSearch(Number(rows[0].dataset.unitId)); setTimeout(() => { isFromKeyboard = false; }, 300); return; }
        if (this.value.trim() === '' || rc === 0) focusNext('search');
    }
});

document.getElementById('search').addEventListener('keyup', function (e) {
    if (['Enter','ArrowDown','ArrowUp','ArrowLeft','ArrowRight','Escape'].includes(e.key)) return;
    const q = this.value.trim();
    if (q.length < 2) {
        document.getElementById('searchResult').innerHTML = `<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:11px;padding:6px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
        resetSearchSelection(); document.getElementById('searchBox').scrollLeft = 0; return;
    }
    fetch(`/pos/search?q=${encodeURIComponent(q)}&warehouse_id=${getWarehouseId()}`)
        .then(r => r.json())
        .then(items => {
            selectedSearchIdx = -1;
            let html = '';
            items.forEach((p, i) => {
                let sc = '';
                (p.stocks || []).forEach(s => {
                    const col = s > 0 ? '#155724' : '#666', bg = s > 0 ? '#d4edda' : '#e9ecef';
                    sc += `<td style="text-align:center;min-width:58px;"><span style="background:${bg};color:${col};padding:1px 6px;border-radius:4px;font-size:11px;font-weight:600;">${s}</span></td>`;
                });
                html += `<tr style="cursor:pointer;" data-unit-id="${p.id}" onclick="if(!isFromKeyboard) addFromSearch(${p.id})">
                    <td style="width:28px;">${i+1}</td>
                    <td style="min-width:110px;">${p.barcode??'-'}</td>
                    <td style="min-width:140px;">${p.name}</td>
                    <td style="min-width:50px;">${p.unit}</td>${sc}</tr>`;
            });
            const cc = 4 + warehouseList.length;
            document.getElementById('searchResult').innerHTML = html || `<tr><td colspan="${cc}" class="text-center text-muted" style="font-size:11px;padding:6px;">Tidak ada hasil untuk "<strong>${q}</strong>"</td></tr>`;
            document.getElementById('searchBox').scrollLeft = 0;
            showSearchHint(items.length > 0);
        });
});

document.getElementById('search').addEventListener('focus', function () {
    highlightActive('search');
    if (document.querySelectorAll('#searchResult tr[data-unit-id]').length > 0) showSearchHint(true);
});
document.getElementById('search').addEventListener('blur', () => setTimeout(() => showSearchHint(false), 200));

['member','discount_rp','discount_percent','paid'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('keydown', e => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        if (id === 'paid') processPay(); else focusNext(id);
    });
    el.addEventListener('focus', () => highlightActive(id));
});

window.addEventListener('load', () => {
    document.getElementById('barcode').focus();
    highlightActive('barcode');
    setJatuhTempo(30);
    const now = new Date();
    document.getElementById('tgPeriode').value = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}`;
});

function createNewTransaction() { window.location.href = '/pos?new_transaction=1'; }

/* ── Unlock owner fields ──────────────────────────────────── */
function unlockMember() {
    if (memberUnlocked) return;
    const pwd = prompt("Masukkan password owner:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r=>r.json()).then(r=>{
            if (!r.success) { alert("Password salah"); return; }
            memberUnlocked = true;
            const el = document.getElementById('member');
            el.readOnly = false; el.classList.remove('locked'); el.focus(); highlightActive('member');
        });
}
function unlockDiscountRp() {
    const el = document.getElementById('discount_rp'); if (!el.classList.contains('locked')) return;
    const pwd = prompt("Masukkan password owner:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r=>r.json()).then(r=>{
            if (!r.success) { alert("Password salah"); return; }
            el.readOnly = false; el.classList.remove('locked'); el.focus(); highlightActive('discount_rp');
        });
}
function unlockDiscountPercent() {
    const el = document.getElementById('discount_percent'); if (!el.classList.contains('locked')) return;
    const pwd = prompt("Masukkan password owner:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r=>r.json()).then(r=>{
            if (!r.success) { alert("Password salah"); return; }
            el.readOnly = false; el.classList.remove('locked'); el.focus(); highlightActive('discount_percent');
        });
}

document.getElementById('discount_rp').addEventListener('input', function () {
    const val = this.value.trim();
    if (val === '' || Number(val) <= 0) { manualDiscountRp = 0; this.value = ''; }
    else { manualDiscountRp = Number(val); manualDiscountPercent = 0; document.getElementById('discount_percent').value = ''; }
    applyDiscountLive();
});
document.getElementById('discount_percent').addEventListener('input', function () {
    const val = this.value.trim();
    if (val === '' || Number(val) <= 0) { manualDiscountPercent = 0; this.value = ''; }
    else { manualDiscountPercent = Number(val); manualDiscountRp = 0; document.getElementById('discount_rp').value = ''; }
    applyDiscountLive();
});

/* ── Payment method ───────────────────────────────────────── */
function onMethodChange(method) {
    selectedPaymentMethod = method;
    const panelCash   = document.getElementById('panelCash');
    const panelKredit = document.getElementById('panelKredit');
    const panelNotice = document.getElementById('panelNotice');
    const btnPay      = document.getElementById('btnPay');
    const btnKredit   = document.getElementById('btnKredit');
    const total       = Number(document.getElementById('totalText').dataset.total);
    panelCash.style.display = panelKredit.style.display = panelNotice.style.display = 'none';
    btnPay.style.display = ''; btnKredit.style.display = 'none';
    if (method === 'kredit') {
        panelKredit.style.display = ''; btnPay.style.display = 'none'; btnKredit.style.display = '';
        document.getElementById('kreditTotalBelanja').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('kreditTotal').innerText        = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('kreditDP').value = ''; document.getElementById('kreditSisaBox').style.display = 'none';
        const memberEl = document.getElementById('member');
        if (memberEl.value && memberEl.value.trim() !== '') { const kNama = document.getElementById('kreditNama'); if (!kNama.value) kNama.value = memberEl.value; }
    } else if (method === 'transfer') {
        panelCash.style.display = ''; panelNotice.style.display = '';
        panelNotice.innerHTML = '⚠️ Pastikan bukti <strong>transfer bank</strong> sudah diterima sebelum proses bayar.';
        document.getElementById('paid').value = total; updateKembalian();
    } else if (method === 'qris') {
        panelCash.style.display = ''; panelNotice.style.display = '';
        panelNotice.innerHTML = '📱 Pastikan notifikasi <strong>QRIS</strong> sudah diterima sebelum proses bayar.';
        document.getElementById('paid').value = total; updateKembalian();
    } else {
        panelCash.style.display = ''; document.getElementById('paid').value = ''; updateKembalian();
    }
}

function updateKreditSisa() {
    const total = Number(document.getElementById('totalText').dataset.total);
    const dp    = parseFloat(document.getElementById('kreditDP').value) || 0;
    const sisa  = Math.max(total - dp, 0);
    const box   = document.getElementById('kreditSisaBox');
    if (dp > 0) {
        box.style.display = '';
        document.getElementById('kreditDPText').innerText   = 'Rp ' + dp.toLocaleString('id-ID');
        document.getElementById('kreditSisaText').innerText = 'Rp ' + sisa.toLocaleString('id-ID');
        document.getElementById('kreditTotal').innerText    = 'Rp ' + sisa.toLocaleString('id-ID');
    } else {
        box.style.display = 'none';
        document.getElementById('kreditTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }
    hitungCicilan();
}

function setJatuhTempo(days) {
    const d = new Date(); d.setDate(d.getDate() + days);
    const yyyy = d.getFullYear(), mm = String(d.getMonth()+1).padStart(2,'0'), dd = String(d.getDate()).padStart(2,'0');
    document.getElementById('kreditJatuhTempo').value = `${yyyy}-${mm}-${dd}`;
    document.querySelectorAll('.jt-chip').forEach(c => c.classList.remove('active'));
    const map = {7:0,14:1,30:2,60:3,90:4};
    if (map[days] !== undefined) document.querySelectorAll('.jt-chip')[map[days]].classList.add('active');
    updateJatuhTempoInfo();
}

function updateJatuhTempoInfo() {
    const val = document.getElementById('kreditJatuhTempo').value; if (!val) return;
    const today = new Date(); today.setHours(0,0,0,0);
    const due   = new Date(val); due.setHours(0,0,0,0);
    const diffDay = Math.round((due - today) / (1000*60*60*24));
    const tglStr  = due.toLocaleDateString('id-ID', {day:'numeric',month:'long',year:'numeric'});
    const hariStr = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'][due.getDay()];
    document.getElementById('jtInfoDate').innerText = `${hariStr}, ${tglStr} (${diffDay} hari lagi)`;
    const chipDays = [7,14,30,60,90];
    const matched  = chipDays.find(d => { const t = new Date(); t.setDate(t.getDate()+d); t.setHours(0,0,0,0); return t.getTime()===due.getTime(); });
    document.querySelectorAll('.jt-chip').forEach(c => c.classList.remove('active'));
    if (matched !== undefined) { const map={7:0,14:1,30:2,60:3,90:4}; document.querySelectorAll('.jt-chip')[map[matched]]?.classList.add('active'); }
    hitungCicilan();
}

document.getElementById('kreditCaraBayar').addEventListener('change', function () {
    const isCicilan = this.value === 'cicilan';
    document.getElementById('kreditCicilanGroup').style.display = isCicilan ? '' : 'none';
    if (isCicilan) hitungCicilan(); else document.getElementById('angsuranInfo').style.display = 'none';
});

function hitungCicilan() {
    if (document.getElementById('kreditCaraBayar').value !== 'cicilan') return;
    const total    = Number(document.getElementById('totalText').dataset.total);
    const dp       = parseFloat(document.getElementById('kreditDP').value) || 0;
    const sisa     = Math.max(total - dp, 0);
    const n        = parseInt(document.getElementById('kreditCicilan').value) || 1;
    const perCicil = Math.ceil(sisa / n);
    const infoEl   = document.getElementById('angsuranInfo');
    const jtVal    = document.getElementById('kreditJatuhTempo').value;
    let cicilanDates = '';
    if (jtVal) {
        const dueDate = new Date(jtVal), today = new Date(); today.setHours(0,0,0,0);
        const diffDay  = Math.round((dueDate - today) / (1000*60*60*24));
        const interval = Math.round(diffDay / n);
        const parts = [];
        for (let i = 1; i <= Math.min(n,4); i++) { const d = new Date(today); d.setDate(d.getDate()+interval*i); parts.push(d.toLocaleDateString('id-ID',{day:'numeric',month:'short'})); }
        if (n > 4) parts.push('...');
        cicilanDates = ` <small style="color:#555">(${parts.join(' · ')})</small>`;
    }
    infoEl.innerHTML = `📆 ${n}x cicilan = <strong>Rp ${perCicil.toLocaleString('id-ID')}</strong>/cicilan${cicilanDates}`;
    infoEl.style.display = '';
}

/* ── Cart ─────────────────────────────────────────────────── */
function addFromSearch(id) {
    if (isScanPending || isAdding) return;
    add(id);
    document.getElementById('search').value = '';
    document.getElementById('searchResult').innerHTML = `<tr><td colspan="${4+warehouseList.length}" class="text-center text-muted" style="font-size:11px;padding:6px;">Ketik minimal 2 karakter untuk mencari produk</td></tr>`;
    resetSearchSelection(); document.getElementById('searchBox').scrollLeft = 0;
    document.getElementById('barcode').focus(); highlightActive('barcode');
}

function add(id, overridePassword = null) {
    if (isAdding) return;
    isAdding = true;
    document.getElementById('barcode').classList.add('adding');
    document.getElementById('search').classList.add('adding');
    fetch('/pos/add-item', { method:'POST', headers:jsonHeaders, body: JSON.stringify({ trx_id:TRX, product_unit_id:id, warehouse_id:getWarehouseId(), override_password:overridePassword }) })
    .then(r => r.json())
    .then(r => {
        isAdding = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        if (r.need_override) { const pwd = prompt("Stok tidak cukup!\nMasukkan password owner:"); if (!pwd) return; add(id, pwd); return; }
        if (!r.success) { alert(r.message); return; }
        loadCart();
    })
    .catch(err => {
        isAdding = false;
        document.getElementById('barcode').classList.remove('adding');
        document.getElementById('search').classList.remove('adding');
        console.error(err); alert('Terjadi error saat menambah item. Coba lagi.');
    });
}

function loadCart() {
    fetch(`/pos?trx_id=${TRX}`).then(r => r.text()).then(html => {
        const doc    = new DOMParser().parseFromString(html, 'text/html');
        const totalEl = doc.querySelector('#totalText');
        const orig   = Math.round(Number(totalEl.dataset.total));
        document.querySelector('#cartBody').innerHTML     = doc.querySelector('#cartBody').innerHTML;
        document.getElementById('totalText').dataset.original = orig;
        document.getElementById('totalText').dataset.total    = orig;
        document.getElementById('totalText').innerText        = 'Rp ' + orig.toLocaleString('id-ID');
        const nb = doc.querySelector('#pendingBadge');
        if (nb) { const b = document.getElementById('pendingBadge'); b.innerText = nb.innerText; b.classList.toggle('hidden', nb.classList.contains('hidden')); }
        applyDiscountLive(); updateKembalian();
        if (selectedPaymentMethod === 'kredit') {
            const t = Number(document.getElementById('totalText').dataset.total);
            document.getElementById('kreditTotalBelanja').innerText = 'Rp ' + t.toLocaleString('id-ID');
            updateKreditSisa();
        }
    });
}

function applyDiscountLive() {
    const totalEl = document.getElementById('totalText');
    const awal    = Math.round(Number(totalEl.dataset.original));
    let   akhir   = awal;
    if (manualDiscountRp > 0)           akhir = awal - Math.round(manualDiscountRp);
    else if (manualDiscountPercent > 0) akhir = awal - Math.round(awal * manualDiscountPercent / 100);
    else if (memberDiscount > 0)        akhir = awal - Math.round(awal * memberDiscount / 100);
    if (akhir < 0) akhir = 0;
    totalEl.innerText     = 'Rp ' + akhir.toLocaleString('id-ID');
    totalEl.dataset.total = akhir;
    updateKembalian();
    if (selectedPaymentMethod === 'kredit') {
        document.getElementById('kreditTotalBelanja').innerText = 'Rp ' + akhir.toLocaleString('id-ID');
        updateKreditSisa();
    }
}

function plusQty(id)  { updateQtyManual(id, getQty(id) + 1); }
function minusQty(id) { updateQtyManual(id, Math.max(getQty(id) - 1, 1)); }
function getQty(id)   { return Number(document.querySelector(`input[onchange="updateQtyManual(${id},this.value)"]`).value); }

function updateQtyManual(itemId, qty, overridePassword = null) {
    fetch('/pos/update-qty-manual', { method:'POST', headers:jsonHeaders,
        body: JSON.stringify({ trx_id:TRX, item_id:itemId, qty, warehouse_id:getWarehouseId(), override_password:overridePassword })
    }).then(r => r.json()).then(r => {
        if (r.need_override) { const pwd = prompt("Stok tidak cukup!\nMasukkan password owner:"); if (!pwd) return; updateQtyManual(itemId, qty, pwd); return; }
        loadCart();
    });
}

function updateUnit(itemId, unitId) {
    fetch('/pos/update-unit', { method:'POST', headers:jsonHeaders,
        body: JSON.stringify({ trx_id:TRX, item_id:itemId, product_unit_id:unitId, warehouse_id:getWarehouseId() })
    }).then(() => loadCart());
}

function removeItemWithAuth(itemId, productName) {
    const pwd = prompt("🔐 Masukkan password owner untuk menghapus item:"); if (!pwd) return;
    fetch('/pos/override-owner', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ password:pwd }) })
        .then(r => r.json()).then(r => {
            if (!r.success) { alert("❌ Password salah!"); return; }
            if (!confirm("⚠️ Hapus item:\n" + productName + "\n\nYakin?")) return;
            fetch('/pos/remove-item', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, item_id:itemId }) })
                .then(res => res.json()).then(res => { if (res.success) loadCart(); else alert("Gagal menghapus item."); });
        });
}

document.getElementById('paid').addEventListener('input', updateKembalian);
function updateKembalian() {
    const total = Number(document.getElementById('totalText').dataset.total);
    const bayar = Number(document.getElementById('paid').value || 0);
    document.getElementById('changeText').innerText = 'Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID');
}

/* ── Process pay ──────────────────────────────────────────── */
async function processPay() {
    const total         = Number(document.getElementById('totalText').dataset.total);
    const paymentMethod = selectedPaymentMethod;
    const bayar         = paymentMethod === 'kredit' ? 0 : Number(document.getElementById('paid').value || 0);
    const memberId      = document.getElementById('member').dataset.memberId || null;
    if (paymentMethod !== 'kredit' && bayar <= 0) { alert('Masukkan jumlah bayar terlebih dahulu!'); document.getElementById('paid').focus(); return; }
    if (paymentMethod === 'kredit') {
        const jtVal = document.getElementById('kreditJatuhTempo').value;
        if (!jtVal) { alert('Tentukan jatuh tempo terlebih dahulu!'); return; }
        const dp = parseFloat(document.getElementById('kreditDP').value) || 0;
        if (dp >= total) { alert('DP tidak boleh sama atau melebihi total belanja. Gunakan pembayaran biasa.'); return; }
    }
    const kreditData = paymentMethod === 'kredit' ? {
        nama_peminjam : document.getElementById('kreditNama').value.trim(),
        telepon       : document.getElementById('kreditTelp').value.trim(),
        cara_bayar    : document.getElementById('kreditCaraBayar').value,
        cicilan       : document.getElementById('kreditCaraBayar').value === 'cicilan' ? parseInt(document.getElementById('kreditCicilan').value) || 1 : null,
        jatuh_tempo   : document.getElementById('kreditJatuhTempo').value,
        catatan       : document.getElementById('kreditCatatan').value.trim(),
        dp            : parseFloat(document.getElementById('kreditDP').value) || 0,
        dp_method     : document.getElementById('kreditDPMethod').value,
    } : null;
    const strukWindow = paymentMethod !== 'kredit' ? window.open('', '_blank') : null;
    try {
        const res = await fetch('/pos/pay', { method:'POST', headers:jsonHeaders, body: JSON.stringify({ trx_id:TRX, paid:bayar, member_id:memberId, payment_method:paymentMethod, frontend_total:total, kredit_data:kreditData }) });
        const r   = await res.json();
        if (r.success) {
            if (r.is_kredit) { showKreditSuccess(r.trx_id, total, kreditData); return; }
            if (r.paid_off) {
                const labels = { cash:'💵 Cash / Tunai', transfer:'🏦 Transfer Bank', qris:'📱 QRIS' };
                alert('✅ Transaksi lunas!\nMetode   : ' + (labels[paymentMethod] || paymentMethod) + '\nKembalian: Rp ' + Math.max(bayar - total, 0).toLocaleString('id-ID'));
                if (strukWindow) strukWindow.location.href = `/transactions/${r.trx_id}/struk`;
                setTimeout(() => { window.location.href = '/pos?new_transaction=1'; }, 500);
            } else {
                alert('Transaksi pending, sisa: Rp ' + (total - bayar).toLocaleString('id-ID'));
                if (strukWindow) strukWindow.close();
            }
        } else { alert(r.message || 'Gagal menyimpan transaksi'); if (strukWindow) strukWindow.close(); }
    } catch (err) { alert('Terjadi error: ' + err.message); if (strukWindow) strukWindow.close(); }
}

function showKreditSuccess(trxId, total, kd) {
    const panelKredit = document.getElementById('panelKredit');
    let dueStr = '—';
    if (kd && kd.jatuh_tempo) { const d = new Date(kd.jatuh_tempo); dueStr = d.toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' }); }
    const caraBayarLabel = { cash:'💵 Cash', transfer:'🏦 Transfer', qris:'📱 QRIS', cicilan:'📆 Cicilan' };
    const dp = kd?.dp || 0, sisa = Math.max(total - dp, 0);
    let dpInfo = '';
    if (dp > 0) {
        const metodeDp = { cash:'💵 Cash', transfer:'🏦 Transfer', qris:'📱 QRIS' };
        dpInfo = `<div style="background:#d1fae5;border:1px solid #a7f3d0;border-radius:6px;padding:6px 10px;margin-bottom:5px;font-size:11px;color:#065f46;">💰 DP dibayar: <strong>Rp ${dp.toLocaleString('id-ID')}</strong> (${metodeDp[kd.dp_method]||kd.dp_method})<br>📌 Sisa hutang: <strong style="color:#dc2626;">Rp ${sisa.toLocaleString('id-ID')}</strong></div>`;
    }
    let extraInfo = '';
    if (kd && kd.cara_bayar === 'cicilan' && kd.cicilan) {
        const perCicil = Math.ceil(sisa / kd.cicilan);
        extraInfo = `<div style="font-size:11px;color:#7a3b00;margin-bottom:4px;">📆 ${kd.cicilan}x cicilan = <strong>Rp ${perCicil.toLocaleString('id-ID')}</strong>/cicilan</div>`;
    }
    panelKredit.innerHTML = `<div class="kredit-success-box"><div class="ks-icon">✅</div><div class="ks-title">Kredit Berhasil Disimpan!</div><div class="ks-trx">Transaksi #${trxId}</div><div class="ks-total">Total Belanja: <strong>Rp ${total.toLocaleString('id-ID')}</strong></div>${dpInfo}${extraInfo}<div class="ks-due">📅 Jatuh Tempo: ${dueStr}</div>${kd?.cara_bayar?`<div style="font-size:11px;color:#888;margin-bottom:6px;">Rencana bayar: ${caraBayarLabel[kd.cara_bayar]||kd.cara_bayar}</div>`:''}<div class="ks-btns"><a href="/pos/kredit/${trxId}" style="background:#e67e00;color:#fff;">📋 Detail Kredit</a><button onclick="window.location.href='/pos?new_transaction=1'" style="background:#28a745;color:#fff;">✚ Transaksi Baru</button></div></div>`;
    document.getElementById('btnKredit').style.display = 'none';
}

/* ── Member search ────────────────────────────────────────── */
const memberBox  = document.getElementById('memberResult');
const memberInfo = document.getElementById('memberInfo');

document.getElementById('member').addEventListener('keyup', function (e) {
    if (!memberUnlocked || e.key === 'Enter') return;
    const q = this.value; if (q.length < 2) { memberBox.innerHTML = ''; return; }
    fetch(`/pos/search-member?q=${q}`).then(r=>r.json()).then(items => {
        memberBox.innerHTML = '';
        items.forEach(m => { memberBox.innerHTML += `<div class="p-1 border-bottom" style="cursor:pointer;font-size:12px;" onclick="selectMember(${m.id})"><strong>${m.name}</strong> — <small class="text-muted">${m.phone}</small></div>`; });
    });
});

function selectMember(id) {
    manualDiscountRp = manualDiscountPercent = 0;
    fetch(`/pos/get-member?id=${id}`).then(r=>r.json()).then(m => {
        const el = document.getElementById('member');
        el.value = m.name; el.dataset.memberId = m.id; memberBox.innerHTML = '';
        memberDiscount = Number(m.discount || 0);
        document.getElementById('discount_rp').value = '';
        document.getElementById('discount_percent').value = memberDiscount > 0 ? memberDiscount : '';
        const dp = document.getElementById('discount_percent'); dp.readOnly = false; dp.classList.remove('locked');
        memberInfo.innerHTML = `<strong>Nama:</strong> ${m.name} | <strong>Level:</strong> ${m.level} | <strong>Disc:</strong> ${m.discount}% | <strong>Poin:</strong> ${m.points}`;
        const kNama = document.getElementById('kreditNama'); if (!kNama.value) kNama.value = m.name;
        applyDiscountLive();
        fetch('/pos/set-member', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, member_id:m.id }) })
            .then(() => fetch('/pos/set-discount', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:TRX, discount:getFinalDiscount() }) }))
            .then(() => loadCart());
    });
}

function getFinalDiscount() {
    const total = Number(document.getElementById('totalText').dataset.original || 0); if (total <= 0) return 0;
    if (manualDiscountPercent > 0) return manualDiscountPercent;
    if (manualDiscountRp > 0) return (manualDiscountRp / total) * 100;
    if (memberDiscount > 0) return memberDiscount;
    return 0;
}

function openPending(trxId) { if (!trxId) return; if (confirm("Lanjutkan transaksi ini?")) window.location.href = `/pos?trx_id=${trxId}`; }

function openPaidTransaction(trxId) {
    if (!trxId) return;
    const pwd = prompt("🔐 Masukkan password owner untuk membuka kembali transaksi ini:"); if (!pwd) return;
    document.body.style.cursor = 'wait';
    fetch('/pos/reopen-transaction', { method:'POST', headers:jsonHeaders, body:JSON.stringify({ trx_id:trxId, password:pwd }) })
        .then(r => r.json()).then(r => {
            document.body.style.cursor = '';
            if (!r.success) { alert("❌ " + (r.message || "Gagal membuka transaksi")); return; }
            window.location.href = `/pos?trx_id=${r.trx_id}`;
        })
        .catch(err => { document.body.style.cursor = ''; console.error(err); alert("Terjadi error. Coba lagi."); });
}

/* ══════════════════════════════════════════════════════════
   JURNAL OVERLAY
   ══════════════════════════════════════════════════════════ */
let jurnalLoaded = false;   /* hindari reload berulang */

function openJurnal() {
    document.getElementById('jurnalOverlay').classList.add('show');

    const frame   = document.getElementById('jurnalFrame');
    const loading = document.getElementById('jurnalLoading');

    /* Muat iframe hanya jika belum pernah dimuat */
    if (!jurnalLoaded) {
        loading.style.display = 'flex';
        frame.style.display   = 'none';
        frame.src             = JURNAL_URL;
        jurnalLoaded          = true;
    }
}

function closeJurnal() {
    document.getElementById('jurnalOverlay').classList.remove('show');
}

/* Tampilkan iframe setelah selesai load, sembunyikan spinner */
function onJurnalFrameLoad() {
    const frame   = document.getElementById('jurnalFrame');
    const loading = document.getElementById('jurnalLoading');
    /* Hanya jika src bukan about:blank */
    if (frame.src && frame.src !== 'about:blank') {
        loading.style.display = 'none';
        frame.style.display   = '';
    }
}

/* Klik backdrop → tutup jurnal */
function handleJurnalOverlayClick(e) {
    if (e.target === document.getElementById('jurnalOverlay')) closeJurnal();
}

/* Tombol reload manual di toolbar (refresh isi jurnal) */
function reloadJurnal() {
    const frame   = document.getElementById('jurnalFrame');
    const loading = document.getElementById('jurnalLoading');
    loading.style.display = 'flex';
    frame.style.display   = 'none';
    frame.src             = JURNAL_URL + '?_t=' + Date.now(); /* cache-bust */
}

/* ══════════════════════════════════════════════════════════
   TAGIHAN OVERLAY
   ══════════════════════════════════════════════════════════ */
let tgKategori       = 'Listrik';
let tgMethodSelected = 'cash';
let tgSuccessTrxId   = null;

const tgMethodLabels = { cash:'💵 Cash', transfer:'🏦 Transfer', qris:'📱 QRIS' };

function openTagihan() {
    document.getElementById('tagihanOverlay').classList.add('show');
    tgReset();
    tgLoadHistory();
    setTimeout(() => document.getElementById('tgNominal').focus(), 200);
}

function closeTagihan() {
    document.getElementById('tagihanOverlay').classList.remove('show');
}

function handleTagihanOverlayClick(e) {
    if (e.target === document.getElementById('tagihanOverlay')) closeTagihan();
}

/* ESC menutup overlay yang sedang terbuka */
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    if (document.getElementById('jurnalOverlay').classList.contains('show'))  { closeJurnal();   return; }
    if (document.getElementById('tagihanOverlay').classList.contains('show')) { closeTagihan();  return; }
});

function tgSetKategori(el, kategori) {
    tgKategori = kategori;
    document.querySelectorAll('.tg-kategori-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('tgNamaCustomWrap').style.display = (kategori === 'Lainnya') ? '' : 'none';
    tgUpdateSummary();
}

function tgSetMethod(el, method) {
    tgMethodSelected = method;
    document.querySelectorAll('.tg-method-pill').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    tgUpdateSummary();
}

function tgSetNominal(val) {
    document.getElementById('tgNominal').value = val;
    document.querySelectorAll('.tg-qn-chip').forEach(c => {
        c.classList.toggle('active', Number(c.getAttribute('onclick').match(/\d+/)[0]) === val);
    });
    tgUpdateSummary();
}

function tgUpdateSummary() {
    const nominal    = parseFloat(document.getElementById('tgNominal').value) || 0;
    const biayaAdmin = parseFloat(document.getElementById('tgBiayaAdmin').value) || 0;
    const total      = nominal + biayaAdmin;
    const namaTagihan = tgKategori === 'Lainnya'
        ? (document.getElementById('tgNamaCustom').value.trim() || 'Lainnya')
        : tgKategori;
    document.getElementById('tgSumKategori').innerText = namaTagihan;
    document.getElementById('tgSumNominal').innerText  = 'Rp ' + nominal.toLocaleString('id-ID');
    document.getElementById('tgSumMethod').innerText   = tgMethodLabels[tgMethodSelected] || tgMethodSelected;
    document.getElementById('tgSumTotal').innerText    = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('tgSumAdminRow').style.display = biayaAdmin > 0 ? '' : 'none';
    document.getElementById('tgSumAdmin').innerText = 'Rp ' + biayaAdmin.toLocaleString('id-ID');
}

document.getElementById('tgNominal').addEventListener('input', function () {
    const val = parseFloat(this.value) || 0;
    document.querySelectorAll('.tg-qn-chip').forEach(c => {
        const chipVal = Number(c.getAttribute('onclick').match(/\d+/)[0]);
        c.classList.toggle('active', chipVal === val);
    });
    tgUpdateSummary();
});

document.getElementById('tgNamaCustom').addEventListener('input', tgUpdateSummary);

async function tgProcessPay() {
    const nominal    = parseFloat(document.getElementById('tgNominal').value) || 0;
    const biayaAdmin = parseFloat(document.getElementById('tgBiayaAdmin').value) || 0;
    const total      = nominal + biayaAdmin;

    if (nominal <= 0) {
        alert('Masukkan nominal tagihan terlebih dahulu!');
        document.getElementById('tgNominal').focus();
        return;
    }

    const namaTagihan = tgKategori === 'Lainnya'
        ? (document.getElementById('tgNamaCustom').value.trim() || 'Lainnya')
        : tgKategori;

    const payload = {
        kategori     : namaTagihan,
        nominal      : nominal,
        biaya_admin  : biayaAdmin,
        total        : total,
        metode_bayar : tgMethodSelected,
        no_rekening  : document.getElementById('tgNoRek').value.trim(),
        periode      : document.getElementById('tgPeriode').value,
        nama_bayar   : document.getElementById('tgNamaBayar').value.trim(),
        catatan      : document.getElementById('tgCatatan').value.trim(),
    };

    const btn = document.getElementById('tgSubmitBtn');
    btn.disabled  = true;
    btn.innerHTML = '<div class="tg-spinner" style="border-top-color:#fff; width:16px; height:16px;"></div> Menyimpan...';

    try {
        const res = await fetch('/pos/bayar-tagihan', { method:'POST', headers:jsonHeaders, body:JSON.stringify(payload) });
        const r   = await res.json();

        if (r.success) {
            tgSuccessTrxId = r.trx_id;
            document.getElementById('tgSuccessTrxNum').innerText = r.trx_number || `#${r.trx_id}`;

            const periodeStr = payload.periode
                ? (() => { const [y,m] = payload.periode.split('-'); return new Date(y,m-1).toLocaleDateString('id-ID',{month:'long',year:'numeric'}); })()
                : '—';

            document.getElementById('tgSuccessDetail').innerHTML = `
                <div class="tg-success-detail-row"><span class="sdl">Jenis Tagihan</span><span class="sdv">${namaTagihan}</span></div>
                ${payload.no_rekening ? `<div class="tg-success-detail-row"><span class="sdl">No. Rekening</span><span class="sdv">${payload.no_rekening}</span></div>` : ''}
                <div class="tg-success-detail-row"><span class="sdl">Periode</span><span class="sdv">${periodeStr}</span></div>
                <div class="tg-success-detail-row"><span class="sdl">Nominal Tagihan</span><span class="sdv">Rp ${nominal.toLocaleString('id-ID')}</span></div>
                ${biayaAdmin > 0 ? `<div class="tg-success-detail-row"><span class="sdl">Biaya Admin</span><span class="sdv">Rp ${biayaAdmin.toLocaleString('id-ID')}</span></div>` : ''}
                <div class="tg-success-detail-row" style="border-top:1px dashed #fde2c2; margin-top:5px; padding-top:5px;">
                    <span class="sdl" style="font-weight:800; color:#c45c00;">Total Bayar</span>
                    <span class="sdv" style="color:#fd7e14; font-size:15px;">Rp ${total.toLocaleString('id-ID')}</span>
                </div>
                <div class="tg-success-detail-row"><span class="sdl">Metode</span><span class="sdv">${tgMethodLabels[tgMethodSelected]}</span></div>
                ${payload.nama_bayar ? `<div class="tg-success-detail-row"><span class="sdl">Pembayar</span><span class="sdv">${payload.nama_bayar}</span></div>` : ''}
            `;

            document.getElementById('tgStateForm').style.display    = 'none';
            document.getElementById('tgStateSuccess').style.display = '';
            loadCart();

            /* Reload jurnal agar data terbaru muncul jika panel sedang terbuka */
            if (document.getElementById('jurnalOverlay').classList.contains('show')) reloadJurnal();

        } else {
            alert('❌ ' + (r.message || 'Gagal menyimpan tagihan'));
        }
    } catch (err) {
        console.error(err);
        alert('Terjadi error: ' + err.message);
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '🧾 Simpan Pembayaran Tagihan';
    }
}

function tgPrintStruk() {
    if (!tgSuccessTrxId) return;
    window.open(`/transactions/${tgSuccessTrxId}/struk`, '_blank');
}

async function tgLoadHistory() {
    const listEl    = document.getElementById('tgHistoryList');
    const loadingEl = document.getElementById('tgHistoryLoading');
    const emptyEl   = document.getElementById('tgHistoryEmpty');
    loadingEl.style.display = 'flex';
    emptyEl.style.display   = 'none';
    [...listEl.children].forEach(c => { if (c !== loadingEl && c !== emptyEl) c.remove(); });

    try {
        const res  = await fetch('/pos/tagihan-today', { headers:{ 'Accept':'application/json', 'X-CSRF-TOKEN':csrf } });
        const data = await res.json();
        const list = data.tagihanList || [];
        loadingEl.style.display = 'none';
        if (list.length === 0) { emptyEl.style.display = ''; return; }
        list.forEach(item => {
            const div = document.createElement('div');
            div.className = 'tg-history-item';
            div.innerHTML = `
                <div>
                    <div class="tg-history-name">${item.kategori}</div>
                    <div class="tg-history-meta">
                        ${item.no_rekening ? `🔢 ${item.no_rekening} &nbsp;·&nbsp; ` : ''}
                        ${tgMethodLabels[item.metode_bayar] || item.metode_bayar}
                        &nbsp;·&nbsp; 🕐 ${item.time}
                        ${item.nama_bayar ? ` &nbsp;·&nbsp; 👤 ${item.nama_bayar}` : ''}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="tg-history-amount">Rp ${Number(item.total).toLocaleString('id-ID')}</div>
                    <div style="font-size:10px; color:#aaa;">${item.trx_number}</div>
                </div>`;
            listEl.insertBefore(div, emptyEl);
        });
    } catch {
        loadingEl.style.display = 'none';
        emptyEl.style.display   = '';
        emptyEl.textContent     = 'Gagal memuat history.';
    }
}

function tgReset() {
    tgKategori       = 'Listrik';
    tgMethodSelected = 'cash';
    tgSuccessTrxId   = null;
    document.querySelectorAll('.tg-kategori-chip').forEach(c => c.classList.toggle('active', c.dataset.kategori === 'Listrik'));
    document.querySelectorAll('.tg-method-pill').forEach(c => c.classList.toggle('active', c.dataset.method === 'cash'));
    document.querySelectorAll('.tg-qn-chip').forEach(c => c.classList.remove('active'));
    document.getElementById('tgNominal').value    = '';
    document.getElementById('tgBiayaAdmin').value = '';
    document.getElementById('tgNoRek').value      = '';
    document.getElementById('tgNamaBayar').value  = '';
    document.getElementById('tgCatatan').value    = '';
    document.getElementById('tgNamaCustom').value = '';
    document.getElementById('tgNamaCustomWrap').style.display = 'none';
    const now = new Date();
    document.getElementById('tgPeriode').value = `${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}`;
    tgUpdateSummary();
    document.getElementById('tgStateForm').style.display    = '';
    document.getElementById('tgStateSuccess').style.display = 'none';
}
</script>

@endsection