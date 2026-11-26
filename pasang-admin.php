<?php
// Menyambungkan dashboard admin ke kode yang sudah ada. Jalankan sekali: php pasang-admin.php
$akar = __DIR__;

// [file, penanda "sudah terpasang", pola yang dicari, pengganti]
$ubah = [
    ['app/Controllers/Auth.php', "role'] === 'admin'",
        '/^([ \t]*)(return redirect\(\)->to\(\'\/\'\)->with\(\'success\', \'Selamat datang kembali)/m',
        "\$1if (\$user['role'] === 'admin') {\n\$1    return redirect()->to('/admin');\n\$1}\n\n\$1\$2"],
    ['app/Controllers/Auth.php', "get('role') === 'admin' ?",
        '/(function login\(\)\s*\{\s*if \(session\(\)->has\(\'pengguna_id\'\)\) \{\s*return redirect\(\)->to\()\'\/\'\)/',
        "\$1session()->get('role') === 'admin' ? '/admin' : '/')"],
    ['app/Config/Routes.php', "'Admin::index'",
        '/\z/',
        "\n// ---- Dashboard Admin ----\n\$routes->get('admin', 'Admin::index');\n\$routes->post('admin/keluar', 'Admin::keluar');\n"],
    ['app/Views/layouts/main.php', 'href="/admin"',
        '/^([ \t]*)(<a href="\/hotel" class=.*)$/m',
        "\$1\$2\n\$1<?php if (session()->get('role') === 'admin'): ?><a href=\"/admin\">Admin</a><?php endif; ?>"],
];

foreach (['app/Controllers/Admin.php', 'app/Views/admin/index.php'] as $f) {
    if (! is_file("$akar/$f")) {
        exit("GAGAL: $f belum ada. Ekstrak zip ke folder proyek dulu.\n");
    }
}

// Hitung semua perubahan dulu; kalau ada yang tidak cocok, tidak ada file yang ditulis
$asli = $baru = [];
foreach ($ubah as [$file, $tanda, $pola, $ganti]) {
    if (! isset($baru[$file])) {
        $isi = @file_get_contents("$akar/$file");
        if ($isi === false) {
            exit("GAGAL: $file tidak ditemukan. Tidak ada yang diubah.\n");
        }
        $asli[$file] = $baru[$file] = $isi;
    }
    if (str_contains($baru[$file], $tanda)) {
        continue;
    }
    $eol  = str_contains($baru[$file], "\r\n") ? "\r\n" : "\n";
    $teks = str_replace("\r\n", "\n", $baru[$file]);
    $teks = preg_replace($pola, $ganti, $teks, 1, $jumlah);
    if ($jumlah !== 1) {
        exit("GAGAL: isi $file berbeda dari perkiraan. Tidak ada yang diubah.\n");
    }
    $baru[$file] = str_replace("\n", $eol, $teks);
}

foreach ($baru as $file => $isi) {
    if ($isi === $asli[$file]) {
        echo "Sudah terpasang: $file\n";
        continue;
    }
    file_put_contents("$akar/$file", $isi);
    echo "Diubah: $file\n";
}
echo "SELESAI. Jalankan server, lalu login sebagai admin.\n";
