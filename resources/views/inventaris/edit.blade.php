<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Inventaris</title>
</head>
<body>
    <h1>Edit Inventaris</h1>

    @if (session('galat'))
        <p><strong>{{ session('galat') }}</strong></p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="/inventaris/{{ $item['id'] }}">
        @csrf
        @method('PUT')
        <p>
            <label>Kode Barang (10 karakter)</label><br>
            <input type="text" name="kode_barang" value="{{ old('kode_barang', $item['kode_barang']) }}">
        </p>
        <p>
            <label>Nama Barang</label><br>
            <input type="text" name="nama_barang" value="{{ old('nama_barang', $item['nama_barang']) }}">
        </p>
        <p>
            <label>Nomor Aset</label><br>
            <input type="text" name="nomor_aset" value="{{ old('nomor_aset', $item['nomor_aset']) }}">
        </p>
        <p>
            <label>Tahun Perolehan (2000-2026)</label><br>
            <input type="text" name="tahun_perolehan" value="{{ old('tahun_perolehan', $item['tahun_perolehan']) }}">
        </p>
        <p>
            <label>Ruang</label><br>
            <select name="ruang_id">
                <option value="">-- Pilih Ruang --</option>
                @foreach ($ruang as $r)
                    <option value="{{ $r['id'] }}" @if ((string) old('ruang_id', $item['ruang_id']) === (string) $r['id']) selected @endif>
                        {{ $r['nama_ruang'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Perbarui</button>
        <a href="/inventaris">Batal</a>
    </form>
</body>
</html>
