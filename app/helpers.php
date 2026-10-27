<?php

/**
 * Modul 2: fungsi sederhana status usia aset.
 * 'Baru' jika tahun >= 2023, selain itu 'Lama'.
 * Didaftarkan lewat composer.json -> autoload.files
 */
if (! function_exists('statusUsiaAset')) {
    function statusUsiaAset(int $tahun): string
    {
        if ($tahun >= 2023) {
            return 'Baru';
        }
        return 'Lama';
    }
}
