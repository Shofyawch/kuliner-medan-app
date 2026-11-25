<?php $aktif = trim(uri_string(), '/'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul ?? 'Kuliner Khas Medan') ?></title>
<link rel="stylesheet" href="/css/app.css">
<?= $this->renderSection('head') ?>
</head>
<body>
<header class="topbar">
  <div class="wadah">
    <a class="merek" href="/">Kuliner <span>Khas</span> Medan</a>
    <nav class="menu">
      <a href="/" class="<?= $aktif === '' ? 'aktif' : '' ?>">Beranda</a>
      <a href="/terdekat" class="<?= $aktif === 'terdekat' ? 'aktif' : '' ?>">Terdekat</a>
      <a href="/hotel" class="<?= str_starts_with($aktif, 'hotel') ? 'aktif' : '' ?>">Penginapan</a>
      <?php if (session()->has('pengguna_id')): ?>
        <span style="color:var(--emas);margin-left:16px;font-size:14px;">Halo, <b><?= esc(session()->get('username')) ?></b></span>
        <a href="/logout" style="color:#f8d7da;margin-left:10px;" title="Keluar dari akun">Keluar</a>
      <?php else: ?>
        <a href="/login" class="<?= $aktif === 'login' ? 'aktif' : '' ?>" style="margin-left:16px;border:1px solid rgba(255,255,255,0.3);border-radius:8px;padding:4px 12px;">Masuk</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="utama">
  <?= $this->renderSection('konten') ?>
</main>

<footer class="dasar">
  <div class="wadah">
    <div class="footer-grid">
      <div class="footer-kolom">
        <a class="merek" href="/">Kuliner <span>Khas</span> Medan</a>
        <p class="footer-desk">Panduan kuliner autentik Kota Medan lengkap dengan peta lokasi, daftar menu, estimasi harga, status halal, dan ulasan pengunjung.</p>
      </div>

      <div class="footer-kolom">
        <h3>Jelajahi</h3>
        <ul>
          <li><a href="/">Beranda</a></li>
          <li><a href="/terdekat">Kuliner Terdekat</a></li>
          <li><a href="/hotel">Penginapan</a></li>
          <?php if (!session()->has('pengguna_id')): ?>
            <li><a href="/login">Masuk / Daftar</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="footer-kolom">
        <h3>Informasi</h3>
        <ul>
          <li><a href="/tentang">Tentang Kami</a></li>
          <li><a href="/faq">FAQ (Tanya Jawab)</a></li>
          <li><a href="/kontak">Hubungi Kami</a></li>
          <li><a href="/ketentuan">Kebijakan &amp; Ketentuan</a></li>
        </ul>
      </div>

      <div class="footer-kolom">
        <h3>Kontak</h3>
        <ul class="footer-kontak">
          <li>Medan, Sumatera Utara</li>
          <li><a href="mailto:halo@kulinermedan.id">halo@kulinermedan.id</a></li>
          <li>Senin – Minggu, 08.00 – 21.00 WIB</li>
          <li><a href="/kontak" class="footer-cta">Kirim Pesan &rarr;</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bawah">
      <p>&copy; <?= date('Y') ?> Kuliner Khas Medan. Seluruh hak cipta dilindungi.</p>
      <p class="footer-catatan">Harga dapat berbeda sewaktu-waktu. Status halal belum semuanya terverifikasi. Peta &copy; kontributor OpenStreetMap.</p>
    </div>
  </div>
</footer>
<?= $this->renderSection('skrip') ?>
</body>
</html>
