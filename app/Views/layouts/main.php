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

<?= $this->renderSection('konten') ?>

<footer class="dasar">
  <div class="wadah">
    <p>Harga dapat berbeda sewaktu-waktu. Status halal belum semuanya terverifikasi.</p>
    <p>Peta &copy; kontributor OpenStreetMap.</p>
  </div>
</footer>
<?= $this->renderSection('skrip') ?>
<script src="/js/pengguna.js" defer></script>
</body>
</html>
