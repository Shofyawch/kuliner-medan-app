<?php $aktif = trim(uri_string(), '/'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($judul ?? 'Kuliner Khas Medan') ?></title>
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<meta name="description" content="<?= esc($metaDeskripsi ?? 'Panduan kuliner autentik Kota Medan — menu, harga, peta lokasi, status halal, dan ulasan tempat makan terbaik.') ?>">
<meta property="og:title" content="<?= esc($judul ?? 'Kuliner Khas Medan') ?>">
<meta property="og:description" content="<?= esc($metaDeskripsi ?? 'Panduan kuliner autentik Kota Medan — menu, harga, peta lokasi, status halal, dan ulasan tempat makan terbaik.') ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= esc(current_url()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
      <?php if (session()->get('role') === 'admin'): ?><a href="/admin">Admin</a><?php endif; ?>
      <?php if (session()->has('pengguna_id')): ?>
        <span class="nav-user">Halo, <b><?= esc(session()->get('username')) ?></b></span>
        <form method="post" action="/logout" class="nav-keluar-form">
          <?= csrf_field() ?>
          <button type="submit" class="nav-keluar" title="Keluar dari akun">Keluar</button>
        </form>
      <?php else: ?>
        <a href="/login" class="nav-masuk <?= $aktif === 'login' ? 'aktif' : '' ?>">Masuk</a>
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
          <li><a href="https://wa.me/6283823253829" target="_blank" rel="noopener">083823253829</a></li>
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

<button id="btn-atas" class="btn-atas" type="button" aria-label="Kembali ke atas" title="Kembali ke atas" hidden>&uarr;</button>

<dialog id="lightbox" class="lightbox" aria-label="Pratinjau foto">
  <button type="button" class="lightbox-tutup" aria-label="Tutup">&times;</button>
  <img id="lightbox-img" src="" alt="">
</dialog>

<script>
(function () {
  var btnAtas = document.getElementById('btn-atas');
  if (btnAtas) {
    var cekScroll = function () { btnAtas.hidden = window.scrollY < 320; };
    window.addEventListener('scroll', cekScroll, { passive: true });
    cekScroll();
    btnAtas.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  var lb = document.getElementById('lightbox');
  var lbImg = document.getElementById('lightbox-img');
  if (lb && lbImg) {
    document.addEventListener('click', function (ev) {
      var img = ev.target.closest('.foto-besar img, .menu-foto');
      if (img && img.src) {
        lbImg.src = img.src;
        lbImg.alt = img.alt || '';
        lb.showModal();
        return;
      }
      if (ev.target === lb || ev.target.closest('.lightbox-tutup')) {
        lb.close();
      }
    });
  }

  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('[data-salin-link]');
    if (!btn) return;
    var teksAsli = btn.textContent;
    var selesai = function () {
      btn.textContent = 'Link tersalin!';
      setTimeout(function () { btn.textContent = teksAsli; }, 2000);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(window.location.href).then(selesai).catch(selesai);
    } else {
      selesai();
    }
  });
})();
</script>
<?= $this->renderSection('skrip') ?>
</body>
</html>
