<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Inventaris Laboratorium</title>
</head>
<body>
    <h1>Sistem Inventaris Laboratorium</h1>

    @if (session('sukses'))
        <p><strong>{{ session('sukses') }}</strong></p>
    @endif
    @if (session('galat') ?? ($galat ?? null))
        <p><strong>{{ session('galat') ?? $galat }}</strong></p>
    @endif

    <h2>Tambah Inventaris</h2>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/inventaris">
        @csrf
        <p>
            <label>Kode Barang (10 karakter)</label><br>
            <input type="text" name="kode_barang" value="{{ old('kode_barang') }}">
        </p>
        <p>
            <label>Nama Barang</label><br>
            <input type="text" name="nama_barang" value="{{ old('nama_barang') }}">
        </p>
        <p>
            <label>Nomor Aset</label><br>
            <input type="text" name="nomor_aset" value="{{ old('nomor_aset') }}">
        </p>
        <p>
            <label>Tahun Perolehan (2000-2026)</label><br>
            <input type="text" name="tahun_perolehan" value="{{ old('tahun_perolehan') }}">
        </p>
        <p>
            <label>Ruang</label><br>
            <select name="ruang_id">
                <option value="">-- Pilih Ruang --</option>
                @foreach ($ruang as $r)
                    <option value="{{ $r['id'] }}" @if (old('ruang_id') == $r['id']) selected @endif>
                        {{ $r['nama_ruang'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Simpan</button>
    </form>

    <h2>Cari dan Filter</h2>
    <form method="GET" action="/inventaris">
        <input type="text" name="q" value="{{ $q }}" placeholder="Kode, nama, atau nomor aset">
        <select name="ruang_id">
            <option value="">-- Semua Ruang --</option>
            @foreach ($ruang as $r)
                <option value="{{ $r['id'] }}" @if ((string) $ruangId === (string) $r['id']) selected @endif>
                    {{ $r['nama_ruang'] }}
                </option>
            @endforeach
        </select>
        <button type="submit">Terapkan</button>
        <a href="/inventaris">Reset</a>
    </form>

    <h2>Daftar Inventaris</h2>
    <table border="1" cellpadding="4" cellspacing="0">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Nomor Aset</th>
                <th>Tahun</th>
                <th>Status Usia</th>
                <th>Ruang</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($inventaris as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row['kode_barang'] }}</td>
                    <td>{{ $row['nama_barang'] }}</td>
                    <td>{{ $row['nomor_aset'] }}</td>
                    <td>{{ $row['tahun_perolehan'] }}</td>
                    <td>{{ statusUsiaAset((int) $row['tahun_perolehan']) }}</td>
                    <td>{{ $row['nama_ruang'] }}</td>
                    <td>
                        <a href="/inventaris/{{ $row['id'] }}/edit">Edit</a>
                        <form method="POST" action="/inventaris/{{ $row['id'] }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">Data tidak ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
