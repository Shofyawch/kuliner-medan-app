<?php
/**
 * @var array $h
 * @var array|null $kulinerSekitar
 * @var array|null $ulasan
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<?php
$foto = !empty($h['foto']) ? $h['foto'] : null;
$sarapanTeks = trim((string) ($h['sarapan'] ?? ''));
$adaSarapan = !empty($sarapanTeks) && (strcasecmp($sarapanTeks, 'ya') === 0 || stripos($sarapanTeks, 'all-in') !== false);
$lat = isset($h['latitude']) && is_numeric($h['latitude']) ? (float) $h['latitude'] : null;
$lng = isset($h['longitude']) && is_numeric($h['longitude']) ? (float) $h['longitude'] : null;
$fmtJarak = static fn ($j) => $j < 1 ? round($j * 1000) . ' m' : number_format($j, 1, ',', '.') . ' km';
?>
<div class="wadah">
  <a class="kembali" href="/hotel">&larr; Semua penginapan</a>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-sukses">
      <?= esc(session()->getFlashdata('success')) ?>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-galat">
      <?= esc(session()->getFlashdata('error')) ?>
    </div>
  <?php endif; ?>

  <div class="detail-atas">
    <div class="foto-besar">
      <?php if ($foto): ?>
        <img src="<?= esc($foto) ?>" alt="<?= esc($h['nama']) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
        <span class="inisial" style="display:none;"><?= esc(mb_strtoupper(mb_substr($h['nama'], 0, 1))) ?></span>
      <?php else: ?>
        <span class="inisial"><?= esc(mb_strtoupper(mb_substr($h['nama'], 0, 1))) ?></span>
      <?php endif; ?>
    </div>

    <div class="detail-info">
      <span class="sub">Hotel &middot; Kec. <?= esc($h['kecamatan'] ?? 'Medan') ?> &middot; <?= esc($h['kota'] ?? 'Medan') ?></span>
      <h1><?= esc($h['nama']) ?></h1>
      <p class="rating">&#9733; <?= esc($h['rating'] ?? '0') ?> <small>(<?= esc($h['jumlah_ulasan'] ?? '0') ?> ulasan)</small></p>
      <p><?= esc($h['alamat'] ?? '') ?></p>
      <p>
        <?php if ($adaSarapan): ?>
          <span class="label" style="background:#e8f5e9;color:#2e7d32;font-weight:600;">Sarapan Tersedia (<?= esc($sarapanTeks) ?>)</span>
        <?php else: ?>
          <span class="label belum">Tanpa Sarapan</span>
        <?php endif; ?>
      </p>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;">
        <?php if (!empty($h['url'])): ?>
          <a class="tombol" href="<?= esc($h['url'], 'attr') ?>" target="_blank" rel="noopener">Kunjungi Halaman Asli</a>
        <?php endif; ?>
        <?php if ($lat && $lng): ?>
          <a class="tombol" style="background:#2f6f3e;" href="https://www.google.com/maps/search/?api=1&query=<?= $lat ?>,<?= $lng ?>" target="_blank" rel="noopener">Petunjuk Arah Maps</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($lat && $lng): ?>
    <section class="panel">
      <h2>Lokasi Hotel</h2>
      <div id="peta" class="peta"></div>
    </section>
  <?php endif; ?>

  <!-- Cross-integration: Kuliner Terdekat dari Hotel Ini -->
  <?php if (!empty($kulinerSekitar)): ?>
    <section class="panel sekitar">
      <h2>🍽️ Kuliner Khas Medan Terdekat dari Hotel Ini</h2>
      <p class="catatan">Rekomendasi tempat makan terdekat dalam radius 3 km dari <?= esc($h['nama']) ?>.</p>
      <?php foreach ($kulinerSekitar as $s): $j = (float) $s['jarak_km']; ?>
        <a href="/restoran/<?= esc($s['slug']) ?>">
          <span>
            <b><?= esc($s['nama']) ?></b><br>
            <span class="sub"><?= esc($s['kategori_khas']) ?> &middot; <?= esc($s['kecamatan']) ?> &middot; &#9733; <?= esc($s['rating'] ?? '-') ?></span>
          </span>
          <span class="jarak"><?= $fmtJarak($j) ?></span>
        </a>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <!-- Ulasan Hotel via Singgah Medan API -->
  <section class="panel">
    <h2>⭐ Ulasan Hotel (API Singgah Medan)</h2>
    <p class="catatan">Ulasan berikut disinkronkan secara realtime dengan server penginapan Singgah Medan.</p>

    <?php if (empty($ulasan)): ?>
      <p style="color:var(--abu);">Belum ada ulasan untuk hotel ini. Jadilah yang pertama memberikan ulasan!</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;">
        <?php foreach ($ulasan as $u): ?>
          <div style="border-bottom:1px solid var(--garis);padding-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
              <b><?= esc($u['penulis'] ?? 'Pengunjung Web Kuliner') ?></b>
              <span class="rating">&#9733; <?= esc($u['rating'] ?? '5') ?></span>
            </div>
            <?php if (!empty($u['judul'])): ?>
              <p style="margin:4px 0 2px;font-weight:600;font-size:15px;"><?= esc($u['judul']) ?></p>
            <?php endif; ?>
            <p style="margin:2px 0 6px;color:#444;font-size:14px;"><?= esc($u['isi'] ?? '') ?></p>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
              <small style="color:var(--abu);font-size:12px;"><?= esc($u['created_at'] ?? '') ?></small>
              <?php if (isset($u['id'])): ?>
                <form method="post" action="/hotel/<?= esc($h['id']) ?>/ulasan/<?= esc($u['id']) ?>/hapus" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ulasan ini dari server penginapan?');" style="margin:0;">
                  <?= csrf_field() ?>
                  <button type="submit" style="background:none;border:1px solid #ffcdd2;color:#c62828;border-radius:6px;cursor:pointer;font-size:11px;padding:2px 8px;">Hapus Ulasan</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Form Kirim Ulasan -->
    <div style="background:#fdfaf5;border:1px solid var(--garis);padding:16px;border-radius:12px;margin-top:20px;">
      <h3 style="margin-top:0;">Tulis Ulasan untuk <?= esc($h['nama']) ?></h3>
      <form method="post" action="/hotel/<?= esc($h['id']) ?>/ulasan">
        <?= csrf_field() ?>
        <div style="margin-bottom:12px;">
          <label for="rating" style="display:block;font-weight:600;margin-bottom:4px;font-size:14px;">Rating:</label>
          <select name="rating" id="rating" style="padding:8px 12px;border:1px solid var(--garis);border-radius:8px;background:#fff;" required>
            <option value="5">⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
            <option value="4">⭐⭐⭐⭐ (4 - Bagus)</option>
            <option value="3">⭐⭐⭐ (3 - Cukup)</option>
            <option value="2">⭐⭐ (2 - Kurang)</option>
            <option value="1">⭐ (1 - Buruk)</option>
          </select>
        </div>

        <div style="margin-bottom:12px;">
          <label for="judul" style="display:block;font-weight:600;margin-bottom:4px;font-size:14px;">Judul Ulasan:</label>
          <input type="text" name="judul" id="judul" placeholder="Contoh: Lokasi strategis dekat kuliner!" style="width:100%;max-width:500px;padding:9px 12px;border:1px solid var(--garis);border-radius:8px;" required>
        </div>

        <div style="margin-bottom:14px;">
          <label for="isi" style="display:block;font-weight:600;margin-bottom:4px;font-size:14px;">Isi Ulasan:</label>
          <textarea name="isi" id="isi" rows="3" placeholder="Ceritakan pengalaman Anda menginap di sini..." style="width:100%;max-width:600px;padding:9px 12px;border:1px solid var(--garis);border-radius:8px;" required></textarea>
        </div>

        <button type="submit" class="tombol">Kirim Ulasan ke Server Hotel</button>
      </form>
    </div>
  </section>
</div>
<?= $this->endSection() ?>

<?= $this->section('skrip') ?>
<?php if ($lat && $lng): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
var lat = <?= $lat ?>, lng = <?= $lng ?>;
var nama = <?= json_encode($h['nama'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var peta = L.map('peta').setView([lat, lng], 16);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; kontributor OpenStreetMap'
}).addTo(peta);
var isi = document.createElement('b');
isi.textContent = nama;
L.marker([lat, lng]).addTo(peta).bindPopup(isi).openPopup();
window.addEventListener('load', function () { peta.invalidateSize(); peta.setView([lat, lng], 16); });
</script>
<?php endif; ?>
<?= $this->endSection() ?>

