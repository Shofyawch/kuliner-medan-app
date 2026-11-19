<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<?php
$foto  = (!empty($r['link_foto']) && is_file(FCPATH . $r['link_foto'])) ? '/' . $r['link_foto'] : null;
$min   = (int) ($r['harga_min'] ?? 0);
$max   = (int) ($r['harga_max'] ?? 0);
$halal = trim((string) ($r['info_halal'] ?? ''));

$fotoMenu = static function (string $nama): ?string {
    $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($nama)), '-');
    return ($slug !== '' && is_file(FCPATH . 'img/menu/' . $slug . '.jpg')) ? '/img/menu/' . $slug . '.jpg' : null;
};

$perKategori = [];
foreach ($menu as $m) {
    $nama = trim((string) ($m['kategori'] ?? ''));
    $perKategori[$nama !== '' ? $nama : 'Lainnya'][] = $m;
}
?>
<div class="wadah">
  <a class="kembali" href="/">&larr; Semua restoran</a>

  <div class="detail-atas">
    <div class="foto-besar">
      <?php if ($foto): ?>
        <img src="<?= esc($foto) ?>" alt="<?= esc($r['nama']) ?>">
      <?php else: ?>
        <span class="inisial"><?= esc(mb_strtoupper(mb_substr($r['nama'], 0, 1))) ?></span>
      <?php endif; ?>
    </div>

    <div class="detail-info">
      <span class="sub"><?= esc($r['kategori_khas']) ?> &middot; <?= esc($r['kecamatan']) ?></span>
      <h1><?= esc($r['nama']) ?></h1>
      <p class="rating">&#9733; <?= esc($r['rating']) ?> <small>(<?= esc($r['jumlah_ulasan']) ?> ulasan)</small></p>
      <p><?= esc($r['alamat']) ?></p>
      <p>
        <?php if ($max > 0): ?><span class="label">Rp <?= round($min / 1000) ?>&ndash;<?= round($max / 1000) ?> rb</span><?php endif; ?>
        <?php if ($halal !== ''): ?><span class="label <?= stripos($halal, 'belum') !== false ? 'belum' : (stripos($halal, 'non') !== false ? 'nonhalal' : '') ?>"><?= esc($halal) ?></span><?php endif; ?>
      </p>
      <?php if (!empty($r['deskripsi'])): ?><p><?= esc($r['deskripsi']) ?></p><?php endif; ?>
      <?php if (!empty($r['link_maps'])): ?>
        <p><a class="tombol" href="<?= esc($r['link_maps'], 'url') ?>" target="_blank" rel="noopener">Buka di Google Maps</a></p>
      <?php endif; ?>
    </div>
  </div>

  <section class="panel">
    <h2>Menu</h2>
    <?php if (empty($menu)): ?>
      <p>Belum ada data menu.</p>
    <?php else: ?>
      <?php foreach ($perKategori as $namaKat => $daftar): ?>
        <h3 class="menu-grup"><?= esc($namaKat) ?></h3>
        <?php foreach ($daftar as $m): $fm = $fotoMenu((string) $m['nama_menu']); ?>
          <div class="menu-baris">
            <span class="menu-nama">
              <?php if ($fm): ?><img class="menu-foto" src="<?= esc($fm) ?>" alt="<?= esc($m['nama_menu']) ?>" loading="lazy"><?php endif; ?>
              <span><?= esc($m['nama_menu']) ?></span>
            </span>
            <span class="menu-harga">Rp <?= number_format((int) $m['harga'], 0, ',', '.') ?></span>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <section class="panel">
    <h2>Lokasi</h2>
    <div id="peta" class="peta"></div>
  </section>

  <?php if (!empty($sekitar)): ?>
  <section class="panel sekitar">
    <h2>Restoran lain di sekitar</h2>
    <p class="catatan">Jarak garis lurus dari restoran ini.</p>
    <?php foreach ($sekitar as $s): $j = (float) $s['jarak_km']; ?>
      <a href="/restoran/<?= esc($s['slug']) ?>">
        <span><b><?= esc($s['nama']) ?></b><br><span class="sub"><?= esc($s['kategori_khas']) ?> &middot; <?= esc($s['kecamatan']) ?></span></span>
        <span class="jarak"><?= $j < 1 ? round($j * 1000) . ' m' : number_format($j, 1, ',', '.') . ' km' ?></span>
      </a>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('skrip') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
var lat = <?= (float) $r['latitude'] ?>, lng = <?= (float) $r['longitude'] ?>;
var nama = <?= json_encode($r['nama'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var peta = L.map('peta').setView([lat, lng], 16);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; kontributor OpenStreetMap'
}).addTo(peta);
var isi = document.createElement('b');
isi.textContent = nama;
L.marker([lat, lng]).addTo(peta).bindPopup(isi);
window.addEventListener('load', function () { peta.invalidateSize(); peta.setView([lat, lng], 16); });
</script>
<?= $this->endSection() ?>
