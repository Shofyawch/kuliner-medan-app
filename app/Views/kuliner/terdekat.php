<?php
/**
 * @var array|null $hasil
 * @var float|null $lat
 * @var float|null $lng
 * @var array $patokan
 * @var string|null $pilihan
 * @var float|int|string $radius
 * @var string|null $asal
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<?php
$punyaAsal = $hasil !== null && $lat !== null;
$dasar     = ($pilihan && isset($patokan[$pilihan])) ? ['patokan' => $pilihan] : ['lat' => $lat, 'lng' => $lng];
$fmtJarak  = static fn ($j) => $j < 1 ? round($j * 1000) . ' m' : number_format($j, 1, ',', '.') . ' km';
?>
<div class="wadah">
  <h1 class="judul-halaman">Restoran Terdekat</h1>

  <section class="panel">
    <form method="get" action="/terdekat" class="kontrol">
      <select name="patokan" aria-label="Titik patokan">
        <option value="">Pilih titik patokan...</option>
        <?php foreach ($patokan as $kode => $p): ?>
          <option value="<?= esc($kode) ?>" <?= $pilihan === $kode ? 'selected' : '' ?>><?= esc($p[0]) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="hidden" name="radius" value="<?= esc($radius) ?>">
      <button type="submit" class="tombol">Cari</button>
      <button type="button" class="tombol hijau" id="gps">Gunakan lokasi saya</button>
    </form>
    <p id="pesan" class="catatan" role="status"></p>
    <?php if (!$punyaAsal): ?>
      <p class="catatan">Pilih titik patokan atau gunakan lokasimu, lalu lihat restoran di sekitarnya.</p>
    <?php endif; ?>
  </section>

  <?php if (!$punyaAsal): ?>
    <section class="patokan-area" aria-label="Pilihan cepat titik patokan">
      <h2 class="patokan-judul">Atau pilih langsung titik populer di Medan</h2>
      <div class="patokan-grid">
        <?php foreach ($patokan as $kode => $p): ?>
          <a class="patokan-kartu" href="/terdekat?<?= esc(http_build_query(['patokan' => $kode, 'radius' => $radius])) ?>">
            <span class="patokan-ikon" aria-hidden="true">&#128205;</span>
            <div>
              <strong><?= esc($p[0]) ?></strong>
              <small>Cari kuliner dalam radius <?= esc($radius) ?> km &rarr;</small>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($punyaAsal): ?>
    <div class="pil" aria-label="Radius pencarian">
      <?php foreach ([1, 2, 3, 5] as $o): ?>
        <a href="/terdekat?<?= esc(http_build_query($dasar + ['radius' => $o])) ?>" class="<?= (float) $o === (float) $radius ? 'aktif' : '' ?>"><?= $o ?> km</a>
      <?php endforeach; ?>
    </div>

    <p><b><?= count($hasil) ?> restoran</b> dalam radius <?= esc($radius) ?> km dari <b><?= esc($asal) ?></b>.
      <span class="sub">Jarak garis lurus, bukan jarak tempuh lewat jalan.</span></p>

    <div class="tab-hp">
      <button type="button" class="aktif" data-tampil="peta">Peta</button>
      <button type="button" data-tampil="daftar">Daftar</button>
    </div>

    <div class="dua-kolom" id="dua-kolom" data-tampil="peta">
      <div class="kolom-peta"><div id="peta" class="peta"></div></div>

      <div class="daftar sekitar panel">
        <?php if (empty($hasil)): ?>
          <p class="catatan">Belum ada restoran di radius ini. Coba perbesar radius atau pilih titik patokan lain.</p>
        <?php endif; ?>
        <?php foreach ($hasil as $r): ?>
          <a href="/restoran/<?= esc($r['slug']) ?>">
            <span><b><?= esc($r['nama']) ?></b><br>
              <span class="sub"><?= esc($r['kategori_khas']) ?> &middot; <?= esc($r['kecamatan']) ?> &middot; &#9733; <?= esc($r['rating']) ?></span></span>
            <span class="jarak"><?= $fmtJarak((float) $r['jarak_km']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('skrip') ?>
<script>
document.getElementById('gps').addEventListener('click', function () {
  var tombol = this;
  var pesan = document.getElementById('pesan');
  if (!navigator.geolocation) {
    pesan.textContent = 'Browser ini tidak mendukung lokasi.';
    return;
  }
  tombol.disabled = true;
  pesan.textContent = 'Mengambil lokasimu...';
  navigator.geolocation.getCurrentPosition(function (p) {
    var r = document.querySelector('input[name=radius]').value;
    window.location = '/terdekat?lat=' + p.coords.latitude + '&lng=' + p.coords.longitude + '&radius=' + r;
  }, function () {
    tombol.disabled = false;
    pesan.textContent = 'Lokasi tidak bisa diambil. Izinkan akses lokasi di browser, atau pilih titik patokan.';
  }, { timeout: 10000 });
});
</script>

<?php if ($punyaAsal): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
<?php $f = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT; ?>
var asal = <?= json_encode(['lat' => (float) $lat, 'lng' => (float) $lng, 'nama' => $asal, 'radius' => (float) $radius], $f) ?>;
var data = <?= json_encode(array_map(static fn ($r) => [
    'nama' => $r['nama'], 'slug' => $r['slug'], 'kategori_khas' => $r['kategori_khas'],
    'rating' => $r['rating'] ?? null,
    'foto' => (!empty($r['link_foto']) && is_file(FCPATH . $r['link_foto'])) ? '/' . $r['link_foto'] : null,
    'latitude' => $r['latitude'], 'longitude' => $r['longitude'], 'jarak_km' => $r['jarak_km'],
], $hasil), $f) ?>;

var peta = L.map('peta').setView([asal.lat, asal.lng], 15);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; kontributor OpenStreetMap'
}).addTo(peta);

var lingkaran = L.circle([asal.lat, asal.lng], {radius: asal.radius * 1000, color: '#2f6f3e', weight: 1.5, fillColor: '#2f6f3e', fillOpacity: 0.06}).addTo(peta);
L.circleMarker([asal.lat, asal.lng], {radius: 9, color: '#fff', weight: 2, fillColor: '#2f6f3e', fillOpacity: 1})
  .addTo(peta).bindTooltip(asal.nama);

data.forEach(function (r) {
  var j = parseFloat(r.jarak_km);
  var teksJarak = j < 1 ? Math.round(j * 1000) + ' m' : j.toFixed(1).replace('.', ',') + ' km';
  var el = document.createElement('div');
  el.className = 'popup-resto';
  if (r.foto) {
    var img = document.createElement('img');
    img.src = r.foto;
    img.alt = r.nama;
    img.onerror = function () { this.remove(); };
    el.appendChild(img);
  }
  var a = document.createElement('a');
  a.className = 'popup-resto-nama';
  a.href = '/restoran/' + encodeURIComponent(r.slug);
  a.textContent = r.nama;
  var s = document.createElement('div');
  s.className = 'popup-resto-info';
  var bintang = r.rating ? '\u2605 ' + r.rating + ' \u00b7 ' : '';
  s.textContent = bintang + r.kategori_khas + ' \u00b7 ' + teksJarak;
  el.appendChild(a);
  el.appendChild(s);
  L.marker([parseFloat(r.latitude), parseFloat(r.longitude)]).addTo(peta).bindPopup(el, { maxWidth: 230 });
});
peta.fitBounds(lingkaran.getBounds(), {padding: [20, 20]});

var wadah = document.getElementById('dua-kolom');
var tabs = document.querySelectorAll('.tab-hp button');
tabs.forEach(function (b) {
  b.addEventListener('click', function () {
    wadah.setAttribute('data-tampil', b.dataset.tampil);
    tabs.forEach(function (x) { x.classList.toggle('aktif', x === b); });
    if (b.dataset.tampil === 'peta') { setTimeout(function () { peta.invalidateSize(); }, 50); }
  });
});
window.addEventListener('load', function () { peta.invalidateSize(); peta.fitBounds(lingkaran.getBounds(), {padding: [20, 20]}); });
</script>
<?php endif; ?>
<?= $this->endSection() ?>
