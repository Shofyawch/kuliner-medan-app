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

  <?php if (session()->getFlashdata('success')): ?>
    <div style="background:#e8f5e9;border:1px solid #c8e6c9;color:#2e7d32;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
      <?= esc(session()->getFlashdata('success')) ?>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div style="background:#ffebee;border:1px solid #ffcdd2;color:#c62828;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
      <?= esc(session()->getFlashdata('error')) ?>
    </div>
  <?php endif; ?>

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
      <?php if (!empty($r['link_maps']) && preg_match('#^https?://#i', $r['link_maps'])): ?>
        <p><a class="tombol" href="<?= esc($r['link_maps'], 'attr') ?>" target="_blank" rel="noopener">Buka di Google Maps</a></p>
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

  <section class="panel" id="panel-ulasan" data-restoran-id="<?= (int) $r['id'] ?>">
    <h2>Ulasan pengguna</h2>
    <p class="catatan">Memuat ulasan...</p>
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

  <!-- Section Ulasan Pengunjung Restoran -->
  <section class="panel">
    <h2>⭐ Ulasan Pengunjung</h2>
    <p class="catatan">Pendapat dan pengalaman pengunjung di <?= esc($r['nama']) ?>.</p>

    <?php if (empty($ulasan)): ?>
      <p style="color:var(--abu);">Belum ada ulasan untuk restoran ini. Jadilah yang pertama memberikan ulasan!</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:14px;margin-bottom:24px;">
        <?php foreach ($ulasan as $u): ?>
          <div style="border-bottom:1px solid var(--garis);padding-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <span style="font-weight:bold;color:var(--teks);"><?= esc($u['username']) ?></span>
              <span class="rating">&#9733; <?= esc($u['rating']) ?></span>
            </div>
            <?php if (!empty($u['komentar'])): ?>
              <p style="margin:6px 0 4px;font-size:14px;color:#333;line-height:1.4;"><?= nl2br(esc($u['komentar'])) ?></p>
            <?php endif; ?>
            <small style="color:var(--abu);font-size:12px;"><?= date('d M Y, H:i', strtotime($u['created_at'])) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Kolom Buat Ulasan -->
    <div style="background:#fdfaf5;border:1px solid var(--garis);padding:18px;border-radius:12px;margin-top:20px;">
      <?php if (session()->has('pengguna_id')): ?>
        <h3 style="margin-top:0;font-size:18px;color:var(--marun);">
          <?= $ulasanSaya ? '✏️ Ubah Ulasan Anda' : '✍️ Tulis Ulasan untuk ' . esc($r['nama']) ?>
        </h3>
        <form method="post" action="/restoran/<?= esc($r['slug']) ?>/ulasan">
          <?= csrf_field() ?>
          <div style="margin-bottom:12px;">
            <label for="rating" style="display:block;font-weight:600;margin-bottom:4px;font-size:14px;">Beri Rating:</label>
            <select name="rating" id="rating" style="padding:8px 12px;border:1px solid var(--garis);border-radius:8px;background:#fff;font-family:inherit;" required>
              <option value="5" <?= ($ulasanSaya['rating'] ?? 5) === 5 ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ (5 - Sangat Enak / Rekomendasi)</option>
              <option value="4" <?= ($ulasanSaya['rating'] ?? 0) === 4 ? 'selected' : '' ?>>⭐⭐⭐⭐ (4 - Enak & Puas)</option>
              <option value="3" <?= ($ulasanSaya['rating'] ?? 0) === 3 ? 'selected' : '' ?>>⭐⭐⭐ (3 - Cukup Enak)</option>
              <option value="2" <?= ($ulasanSaya['rating'] ?? 0) === 2 ? 'selected' : '' ?>>⭐⭐ (2 - Kurang Puas)</option>
              <option value="1" <?= ($ulasanSaya['rating'] ?? 0) === 1 ? 'selected' : '' ?>>⭐ (1 - Kecewa)</option>
            </select>
          </div>

          <div style="margin-bottom:14px;">
            <label for="komentar" style="display:block;font-weight:600;margin-bottom:4px;font-size:14px;">Komentar / Pengalaman Kuliner:</label>
            <textarea name="komentar" id="komentar" rows="3" placeholder="Ceritakan rasa masakan, suasana, atau pelayanan di sini... (maks 500 karakter)" style="width:100%;max-width:600px;padding:9px 12px;border:1px solid var(--garis);border-radius:8px;font-family:inherit;font-size:14px;" maxlength="500"><?= esc($ulasanSaya['komentar'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="tombol">
            <?= $ulasanSaya ? 'Perbarui Ulasan' : 'Kirim Ulasan' ?>
          </button>
        </form>
      <?php else: ?>
        <div style="text-align:center;padding:12px;">
          <p style="margin:0 0 8px;font-weight:600;">Ingin menulis ulasan untuk restoran ini?</p>
          <p style="margin:0;font-size:14px;color:var(--abu);">
            Silakan <a href="/login" style="color:var(--marun);font-weight:bold;">Masuk ke akun</a> atau <a href="/daftar" style="color:var(--marun);font-weight:bold;">Daftar akun baru</a> terlebih dahulu.
          </p>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php if (!empty($hotelSekitar)): ?>
  <section class="panel sekitar">
    <h2>🏨 Penginapan Terdekat di Sekitar Sini</h2>
    <p class="catatan">Hotel terdekat terintegrasi dari API Singgah Medan.</p>
    <?php foreach ($hotelSekitar as $hs):
      $jh = (float) $hs['jarak_km'];
      $sarapanHs = trim((string) ($hs['sarapan'] ?? ''));
      $adaSarapanHs = !empty($sarapanHs) && (strcasecmp($sarapanHs, 'ya') === 0 || stripos($sarapanHs, 'all-in') !== false);
    ?>
      <a href="/hotel/<?= esc($hs['id']) ?>">
        <span>
          <b><?= esc($hs['nama']) ?></b>
          <?php if ($adaSarapanHs): ?>
            <span class="label" style="background:#e8f5e9;color:#2e7d32;font-size:10px;padding:1px 6px;margin-left:4px;">Sarapan</span>
          <?php endif; ?>
          <br>
          <span class="sub">Kec. <?= esc($hs['kecamatan'] ?? 'Medan') ?> &middot; &#9733; <?= esc($hs['rating'] ?? '0') ?> (<?= esc($hs['jumlah_ulasan'] ?? '0') ?> ulasan)</span>
        </span>
        <span class="jarak"><?= $jh < 1 ? round($jh * 1000) . ' m' : number_format($jh, 1, ',', '.') . ' km' ?></span>
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
