<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($r['nama']) ?> - Kuliner Khas Medan</title>
<style>
body{font-family:system-ui,sans-serif;margin:0;background:#faf7f2;color:#222}
header{background:#8b1e1e;color:#fff;padding:20px 16px}
header a{color:#fff}
main{max-width:800px;margin:0 auto;padding:16px}
.box{background:#fff;border-radius:10px;padding:16px;margin-bottom:14px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
h1{margin:6px 0 0}
table{width:100%;border-collapse:collapse}
td,th{padding:8px 4px;border-bottom:1px solid #eee;text-align:left}
td.harga{text-align:right;white-space:nowrap}
.catatan{color:#a05a00;font-size:14px}
</style>
</head>
<body>
<header>
  <a href="/">&larr; Kembali</a>
  <h1><?= esc($r['nama']) ?></h1>
</header>
<main>
  <div class="box">
    <p><b><?= esc($r['kategori_khas']) ?></b> &middot; <?= esc($r['kecamatan']) ?> &middot; &#9733; <?= esc($r['rating']) ?> (<?= esc($r['jumlah_ulasan']) ?> ulasan)</p>
    <p><?= esc($r['alamat']) ?></p>
    <?php if (!empty($r['deskripsi'])): ?><p><?= esc($r['deskripsi']) ?></p><?php endif; ?>
    <?php if (!empty($r['link_maps'])): ?><p><a href="<?= esc($r['link_maps'], 'url') ?>" target="_blank" rel="noopener">Buka di Google Maps</a></p><?php endif; ?>
  </div>

  <div class="box">
    <h2>Menu</h2>
    <p class="catatan">Harga masih estimasi, bukan harga asli.</p>
    <?php if (empty($menu)): ?>
      <p>Belum ada data menu.</p>
    <?php else: ?>
      <table>
        <?php foreach ($menu as $m): ?>
          <tr>
            <td><?= esc($m['nama_menu']) ?><br><small><?= esc($m['kategori']) ?></small></td>
            <td class="harga">Rp <?= number_format((int) $m['harga'], 0, ',', '.') ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
