<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Kuliner Khas Medan</title>
<style>
body{font-family:system-ui,sans-serif;margin:0;background:#faf7f2;color:#222}
header{background:#8b1e1e;color:#fff;padding:24px 16px;text-align:center}
header h1{margin:0 0 4px}
main{max-width:960px;margin:0 auto;padding:16px}
form{display:flex;gap:8px;margin-bottom:12px}
input[type=text]{flex:1;padding:10px;border:1px solid #ccc;border-radius:8px}
button{padding:10px 16px;border:0;background:#8b1e1e;color:#fff;border-radius:8px;cursor:pointer}
.kat a{display:inline-block;margin:0 6px 8px 0;padding:6px 12px;border-radius:999px;background:#eee;color:#333;text-decoration:none;font-size:14px}
.kat a.aktif{background:#8b1e1e;color:#fff}
.kartu{display:block;background:#fff;border-radius:10px;padding:14px 16px;margin-bottom:10px;text-decoration:none;color:inherit;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.kartu h3{margin:0 0 4px}
.kartu small{color:#666}
.rating{float:right;font-weight:bold;color:#b8860b}
.pager{margin:16px 0;text-align:center}
</style>
</head>
<body>
<header>
  <h1>Kuliner Khas Medan</h1>
  <div>83 restoran pilihan di Kota Medan</div>
  <p><a href="/terdekat" style="color:#fff">Cari yang terdekat dari lokasimu</a></p>
</header>
<main>
  <form method="get" action="/">
    <?php if ($kategori): ?><input type="hidden" name="kategori" value="<?= esc($kategori) ?>"><?php endif; ?>
    <input type="text" name="q" placeholder="Cari nama restoran..." value="<?= esc($cari ?? '') ?>">
    <button type="submit">Cari</button>
  </form>

  <div class="kat">
    <a href="/" class="<?= $kategori ? '' : 'aktif' ?>">Semua</a>
    <?php foreach ($daftarKategori as $k): ?>
      <a href="/?kategori=<?= urlencode($k['kategori_khas']) ?>" class="<?= $kategori === $k['kategori_khas'] ? 'aktif' : '' ?>">
        <?= esc($k['kategori_khas']) ?> (<?= $k['jumlah'] ?>)
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($restoran)): ?>
    <p>Tidak ada restoran yang cocok.</p>
  <?php endif; ?>

  <?php foreach ($restoran as $r): ?>
    <a class="kartu" href="/restoran/<?= esc($r['slug']) ?>">
      <span class="rating">&#9733; <?= esc($r['rating']) ?></span>
      <h3><?= esc($r['nama']) ?></h3>
      <small><?= esc($r['kategori_khas']) ?> &middot; <?= esc($r['kecamatan']) ?></small>
    </a>
  <?php endforeach; ?>

  <div class="pager"><?= $pager->links() ?></div>
</main>
</body>
</html>
