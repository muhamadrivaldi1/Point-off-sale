<?php

namespace App\Observers;

use App\Models\TransactionItem;

class TransactionItemObserver
{
    /**
     * ✅ JANGAN ubah stok di sini.
     *
     * Observer ini dulu mengurangi stok -1 setiap kali item ditambah ke keranjang,
     * yang menyebabkan:
     *   1. Stok berkurang -1 saat item pertama kali dibuat (qty=1)
     *   2. Update qty jadi 3 tidak tercatat → stok berkurang -1 saja padahal harusnya -3
     *   3. pay() juga decrement stok lagi → stok berkurang dobel
     *   4. Mutasi hanya tercatat qty=1, bukan qty final
     *
     * Solusi: stok HANYA dikurangi saat transaksi DIBAYAR (di PosController::pay())
     * dengan qty final yang benar, sekaligus mencatat StockMutation.
     */
    public function created(TransactionItem $item): void
    {
        // Sengaja kosong — stok dikelola di PosController::pay()
    }
}