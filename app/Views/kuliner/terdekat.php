<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Restoran Terdekat - Kuliner Khas Medan</title>
<style>
body{font-family:system-ui,sans-serif;margin:0;background:#faf7f2;color:#222}
header{background:#8b1e1e;color:#fff;padding:20px 16px}
header a{color:#fff}
main{max-width:800px;margin:0 auto;padding:16px}
.box{background:#fff;border-radius:10px;padding:16px;margin-bottom:14px;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.baris{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
select{padding:10px;border:1px solid #ccc;border-radius:8px;background:#fff}
button{padding:10px 16px;border:0;background:#8b1e1e;color:#fff;border-radius:8px;cursor:pointer}
button.gps{background:#2f6f3e}
.kartu{display:block;background:#fff;border-radius:10px;padding:14px 16px;margin-bottom:10px;text-decoration:none;color:inherit;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.kartu h3{margin:0 0 4px}
.kartu small{color:#666}
.jarak{float:right;font-weight:bold;color:#2f6f3e}
.catatan{color:#666;font-size:13px}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css">
<style>#peta{height:360px;border-radius:10px;margin-bottom:12px}</style>
</head>
<body>
<header>
  <a href="/">&larr; Kembali</a>
  <h1 style="margin:6px 0 0">Restoran Terdekat</h1>
</header>
<main>
  <div class="box">
    <form method="get" action="/terdekat">
      <div class="baris">
        <select name="patokan">
          <option value="">Pilih titik patokan...</option>
          <?php foreach ($patokan as $kode => $p): ?>
            <option value="<?= esc($kode) ?>" <?= $pilihan === $kode ? 'selected' : '' ?>><?= esc($p[0]) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="radius" id="radius">
          <?php foreach ([1, 2, 3, 5] as $o): ?>
            <option value="<?= $o ?>" <?= (float) $o === (float) $radius ? 'selected' : '' ?>>Radius <?= $o ?> km</option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Cari</button>
      </div>
    </form>
    <button type="button" class="gps" id="gps">Gunakan lokasi saya</button>
    <p id="pesan" class="catatan"></p>
  </div>

  <?php if ($hasil !== null): ?>
    <p><b><?= count($hasil) ?> restoran</b> dalam radius <?= esc($radius) ?> km dari <b><?= esc($asal) ?></b></p>
    <p class="catatan">Jarak dihitung garis lurus, bukan jarak tempuh lewat jalan.</p>

    <?php if (empty($hasil)): ?>
      <p>Belum ada restoran di radius ini. Coba perbesar radius atau pilih titik patokan.</p>
    <?php endif; ?>

    <div id="peta"></div>
    <?php foreach ($hasil as $r): ?>
      <?php $j = (float) $r['jarak_km']; ?>
      <a class="kartu" href="/restoran/<?= esc($r['slug']) ?>">
        <span class="jarak"><?= $j < 1 ? round($j * 1000) . ' m' : number_format($j, 1, ',', '.') . ' km' ?></span>
        <h3><?= esc($r['nama']) ?></h3>
        <small><?= esc($r['kategori_khas']) ?> &middot; <?= esc($r['kecamatan']) ?> &middot; &#9733; <?= esc($r['rating']) ?></small>
      </a>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="catatan">Pilih titik patokan atau gunakan lokasimu, lalu lihat restoran di sekitarnya.</p>
  <?php endif; ?>
</main>

<script>
document.getElementById('gps').addEventListener('click', function () {
  var pesan = document.getElementById('pesan');
  if (!navigator.geolocation) {
    pesan.textContent = 'Browser ini tidak mendukung lokasi.';
    return;
  }
  pesan.textContent = 'Mengambil lokasi...';
  navigator.geolocation.getCurrentPosition(function (p) {
    var r = document.getElementById('radius').value;
    window.location = '/terdekat?lat=' + p.coords.latitude + '&lng=' + p.coords.longitude + '&radius=' + r;
  }, function () {
    pesan.textContent = 'Lokasi tidak bisa diambil. Izinkan akses lokasi di browser, atau pilih titik patokan.';
  }, { timeout: 10000 });
});
</script>
<?php if ($hasil !== null && $lat !== null): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
<?php $f = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT; ?>
var asal = <?= json_encode(['lat' => (float) $lat, 'lng' => (float) $lng, 'nama' => $asal], $f) ?>;
var data = <?= json_encode(array_map(fn($r) => [
    'nama' => $r['nama'], 'slug' => $r['slug'], 'kategori_khas' => $r['kategori_khas'],
    'latitude' => $r['latitude'], 'longitude' => $r['longitude'], 'jarak_km' => $r['jarak_km'],
], $hasil), $f) ?>;

var peta = L.map('peta').setView([asal.lat, asal.lng], 15);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; kontributor OpenStreetMap'
}).addTo(peta);

L.circleMarker([asal.lat, asal.lng], {radius: 9, color: '#2f6f3e', fillColor: '#2f6f3e', fillOpacity: 0.9})
  .addTo(peta).bindTooltip(asal.nama, {permanent: false});

var batas = [[asal.lat, asal.lng]];
data.forEach(function (r) {
  var lat = parseFloat(r.latitude), lng = parseFloat(r.longitude);
  var el = document.createElement('div');
  var a = document.createElement('a');
  a.href = '/restoran/' + encodeURIComponent(r.slug);
  a.textContent = r.nama;
  var s = document.createElement('div');
  s.textContent = r.kategori_khas + ' \u00b7 ' + r.jarak_km + ' km';
  el.appendChild(a);
  el.appendChild(s);
  L.marker([lat, lng]).addTo(peta).bindPopup(el);
  batas.push([lat, lng]);
});
if (batas.length > 1) { peta.fitBounds(batas, {padding: [30, 30]}); }
</script>
<?php endif; ?>
</body>
</html>
