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
    </nav>
  </div>
</header>

<?= $this->renderSection('konten') ?>

<footer class="dasar">
  <div class="wadah">
    <p>Harga menu masih estimasi, bukan harga asli. Status halal belum semuanya terverifikasi.</p>
    <p>Peta &copy; kontributor OpenStreetMap.</p>
  </div>
</footer>
<?= $this->renderSection('skrip') ?>
</body>
</html>
