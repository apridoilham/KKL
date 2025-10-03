<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Waktu Kunci Transaksi (dalam menit)
    |--------------------------------------------------------------------------
    |
    | Menentukan berapa lama (dalam menit) sebuah transaksi bisa dihapus
    | setelah dibuat. Setelah waktu ini terlewat, transaksi akan terkunci
    | untuk menjaga integritas data historis.
    |
    */
    'transaction_lock_time' => 10,

    /*
    |--------------------------------------------------------------------------
    | Durasi Cache Statistik (dalam detik)
    |--------------------------------------------------------------------------
    |
    | Menentukan berapa lama data statistik di dashboard akan disimpan di cache
    | untuk mengurangi beban query ke database. (Contoh: 300 detik = 5 menit).
    |
    */
    'stats_cache_duration' => 300,
];