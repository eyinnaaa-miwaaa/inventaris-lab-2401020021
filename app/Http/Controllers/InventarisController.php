<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class InventarisController extends Controller
{
    private const PESAN_AMAN = 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.';

    /** Modul 3: sanitasi input teks (trim + strip_tags). */
    private function bersihkan(Request $request): array
    {
        return [
            'kode_barang'     => trim(strip_tags((string) $request->input('kode_barang'))),
            'nama_barang'     => trim(strip_tags((string) $request->input('nama_barang'))),
            'nomor_aset'      => trim(strip_tags((string) $request->input('nomor_aset'))),
            'tahun_perolehan' => trim(strip_tags((string) $request->input('tahun_perolehan'))),
            'ruang_id'        => trim(strip_tags((string) $request->input('ruang_id'))),
        ];
    }

    /** Modul 3: validasi server-side atas data yang sudah dibersihkan. */
    private function validasi(array $data, string $kodeLama = null)
    {
        return validator($data, [
            'kode_barang'     => ['required', 'size:10', 'regex:/^[A-Za-z0-9]+$/'],
            'nama_barang'     => ['required', 'min:3', 'max:100'],
            'nomor_aset'      => ['required', 'min:5', 'max:30'],
            'tahun_perolehan' => ['required', 'integer', 'between:2000,2026'],
            'ruang_id'        => ['required', 'integer'],
        ], [
            'kode_barang.required'     => 'Kode barang wajib diisi.',
            'kode_barang.size'         => 'Kode barang harus tepat 10 karakter.',
            'kode_barang.regex'        => 'Kode barang hanya boleh huruf dan angka.',
            'nama_barang.required'     => 'Nama barang wajib diisi.',
            'nama_barang.min'          => 'Nama barang minimal 3 karakter.',
            'nama_barang.max'          => 'Nama barang maksimal 100 karakter.',
            'nomor_aset.required'      => 'Nomor aset wajib diisi.',
            'nomor_aset.min'           => 'Nomor aset minimal 5 karakter.',
            'nomor_aset.max'           => 'Nomor aset maksimal 30 karakter.',
            'tahun_perolehan.required' => 'Tahun perolehan wajib diisi.',
            'tahun_perolehan.integer'  => 'Tahun perolehan harus berupa angka.',
            'tahun_perolehan.between'  => 'Tahun perolehan harus antara 2000 dan 2026.',
            'ruang_id.required'        => 'Ruang wajib dipilih.',
            'ruang_id.integer'         => 'Ruang tidak valid.',
        ]);
    }

    /** Modul 5: PDO dari koneksi Laravel. */
    private function pdo(): \PDO
    {
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /** Cek duplikat kode/nomor aset (prepared statement). $kecualiId dipakai saat update. */
    private function cekDuplikat(\PDO $pdo, string $kode, string $aset, ?int $kecualiId = null): array
    {
        $pesan = [];

        $sql = 'SELECT COUNT(*) FROM inventaris WHERE kode_barang = :kode AND id <> :id';
        $st = $pdo->prepare($sql);
        $st->execute([':kode' => $kode, ':id' => $kecualiId ?? 0]);
        if ((int) $st->fetchColumn() > 0) {
            $pesan['kode_barang'] = 'Kode barang sudah digunakan.';
        }

        $sql = 'SELECT COUNT(*) FROM inventaris WHERE nomor_aset = :aset AND id <> :id';
        $st = $pdo->prepare($sql);
        $st->execute([':aset' => $aset, ':id' => $kecualiId ?? 0]);
        if ((int) $st->fetchColumn() > 0) {
            $pesan['nomor_aset'] = 'Nomor aset sudah digunakan.';
        }

        return $pesan;
    }

    private function ruangAda(\PDO $pdo, int $id): bool
    {
        $st = $pdo->prepare('SELECT COUNT(*) FROM ruang WHERE id = :id');
        $st->execute([':id' => $id]);
        return (int) $st->fetchColumn() > 0;
    }

    // ---------------------------------------------------------------- READ
    public function index(Request $request)
    {
        $q       = trim(strip_tags((string) $request->query('q', '')));
        $ruangId = trim(strip_tags((string) $request->query('ruang_id', '')));

        try {
            $pdo   = $this->pdo();
            $ruang = $pdo->query('SELECT id, nama_ruang FROM ruang ORDER BY nama_ruang')->fetchAll(\PDO::FETCH_ASSOC);

            $sql = 'SELECT i.id, i.kode_barang, i.nama_barang, i.nomor_aset, i.tahun_perolehan, r.nama_ruang
                    FROM inventaris i
                    JOIN ruang r ON r.id = i.ruang_id
                    WHERE 1 = 1';
            $param = [];

            if ($q !== '') {
                $sql .= ' AND (i.kode_barang LIKE :q1 OR i.nama_barang LIKE :q2 OR i.nomor_aset LIKE :q3)';
                $like = '%' . $q . '%';
                $param[':q1'] = $like;
                $param[':q2'] = $like;
                $param[':q3'] = $like;
            }
            if ($ruangId !== '' && ctype_digit($ruangId)) {
                $sql .= ' AND i.ruang_id = :ruang_id';
                $param[':ruang_id'] = (int) $ruangId;
            }
            $sql .= ' ORDER BY i.id ASC';

            $st = $pdo->prepare($sql);
            $st->execute($param);
            $inventaris = $st->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            report($e);
            return response()->view('inventaris.index', [
                'inventaris' => [], 'ruang' => [], 'q' => $q, 'ruangId' => $ruangId,
                'galat' => self::PESAN_AMAN,
            ], 500);
        }

        return view('inventaris.index', compact('inventaris', 'ruang', 'q', 'ruangId'));
    }

    // -------------------------------------------------------------- CREATE
    public function store(Request $request)
    {
        $data = $this->bersihkan($request);
        $v = $this->validasi($data);
        $errors = $v->errors()->toArray();

        try {
            $pdo = $this->pdo();

            if (! $v->fails() && ! $this->ruangAda($pdo, (int) $data['ruang_id'])) {
                $errors['ruang_id'] = ['Ruang yang dipilih tidak tersedia.'];
            }
            if (! $v->fails()) {
                foreach ($this->cekDuplikat($pdo, $data['kode_barang'], $data['nomor_aset']) as $k => $m) {
                    $errors[$k] = [$m];
                }
            }

            if ($v->fails() || $errors) {
                return redirect('/inventaris')->withInput($data)->withErrors($errors);
            }

            $sql = 'INSERT INTO inventaris (kode_barang, nama_barang, nomor_aset, tahun_perolehan, ruang_id, created_at, updated_at)
                    VALUES (:kode, :nama, :aset, :tahun, :ruang, NOW(), NOW())';
            $st = $pdo->prepare($sql);
            $st->execute([
                ':kode'  => $data['kode_barang'],
                ':nama'  => $data['nama_barang'],
                ':aset'  => $data['nomor_aset'],
                ':tahun' => (int) $data['tahun_perolehan'],
                ':ruang' => (int) $data['ruang_id'],
            ]);
        } catch (Throwable $e) {
            report($e);
            return redirect('/inventaris')->withInput($data)->with('galat', self::PESAN_AMAN);
        }

        return redirect('/inventaris')->with('sukses', 'Data inventaris berhasil ditambahkan.');
    }

    // ---------------------------------------------------------------- EDIT
    public function edit(int $id)
    {
        try {
            $pdo = $this->pdo();
            $st = $pdo->prepare('SELECT id, kode_barang, nama_barang, nomor_aset, tahun_perolehan, ruang_id
                                 FROM inventaris WHERE id = :id');
            $st->execute([':id' => $id]);
            $item = $st->fetch(\PDO::FETCH_ASSOC);
            $ruang = $pdo->query('SELECT id, nama_ruang FROM ruang ORDER BY nama_ruang')->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            report($e);
            return redirect('/inventaris')->with('galat', self::PESAN_AMAN);
        }

        if (! $item) {
            return redirect('/inventaris')->with('galat', 'Data tidak ditemukan.');
        }

        return view('inventaris.edit', compact('item', 'ruang'));
    }

    // -------------------------------------------------------------- UPDATE
    public function update(Request $request, int $id)
    {
        $data = $this->bersihkan($request);
        $v = $this->validasi($data);
        $errors = $v->errors()->toArray();

        try {
            $pdo = $this->pdo();

            if (! $v->fails() && ! $this->ruangAda($pdo, (int) $data['ruang_id'])) {
                $errors['ruang_id'] = ['Ruang yang dipilih tidak tersedia.'];
            }
            if (! $v->fails()) {
                foreach ($this->cekDuplikat($pdo, $data['kode_barang'], $data['nomor_aset'], $id) as $k => $m) {
                    $errors[$k] = [$m];
                }
            }

            if ($v->fails() || $errors) {
                return redirect("/inventaris/{$id}/edit")->withInput($data)->withErrors($errors);
            }

            $sql = 'UPDATE inventaris
                    SET kode_barang = :kode, nama_barang = :nama, nomor_aset = :aset,
                        tahun_perolehan = :tahun, ruang_id = :ruang, updated_at = NOW()
                    WHERE id = :id';
            $st = $pdo->prepare($sql);
            $st->execute([
                ':kode'  => $data['kode_barang'],
                ':nama'  => $data['nama_barang'],
                ':aset'  => $data['nomor_aset'],
                ':tahun' => (int) $data['tahun_perolehan'],
                ':ruang' => (int) $data['ruang_id'],
                ':id'    => $id,
            ]);
        } catch (Throwable $e) {
            report($e);
            return redirect("/inventaris/{$id}/edit")->withInput($data)->with('galat', self::PESAN_AMAN);
        }

        return redirect('/inventaris')->with('sukses', 'Data inventaris berhasil diperbarui.');
    }

    // -------------------------------------------------------------- DELETE
    public function destroy(int $id)
    {
        try {
            $st = $this->pdo()->prepare('DELETE FROM inventaris WHERE id = :id');
            $st->execute([':id' => $id]);
            $terhapus = $st->rowCount();
        } catch (Throwable $e) {
            report($e);
            return redirect('/inventaris')->with('galat', self::PESAN_AMAN);
        }

        if ($terhapus === 0) {
            return redirect('/inventaris')->with('galat', 'Data tidak ditemukan.');
        }

        return redirect('/inventaris')->with('sukses', 'Data inventaris berhasil dihapus.');
    }
}
