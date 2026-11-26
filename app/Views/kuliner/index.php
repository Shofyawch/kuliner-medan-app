<?php

/**
 * @var array $daftarKategori
 * @var array $daftarKecamatan
 * @var string $kategori
 * @var string $kecamatan
 * @var string $cari
 * @var string $harga
 * @var array|string|null $urut
 * @var array $restoran
 * @var \CodeIgniter\Pager\Pager $pager
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&display=swap" rel="stylesheet">
<style>
  body {
    --kunyit: #f6bc1c;
    --daun: #14533a;
    --cabai: #b93418;
    --piring: #fffdf8;
    --arang: #22160f;
    background: var(--piring);
    color: var(--arang)
  }

  .br-j {
    font-family: "Bricolage Grotesque", var(--font-teks);
    font-weight: 800;
    letter-spacing: -.025em;
    line-height: 1.02
  }

  .br-atas {
    background: var(--kunyit);
    overflow: hidden
  }

  .br-atas .wadah {
    display: grid;
    grid-template-columns: 1.1fr .9fr;
    gap: 24px;
    align-items: center;
    min-height: 430px
  }

  .br-atas h1 {
    font-size: clamp(42px, 6.4vw, 76px);
    margin: 0 0 16px;
    max-width: 10ch
  }

  .br-atas p {
    font-size: 17px;
    margin: 0 0 24px;
    max-width: 42ch;
    font-weight: 500
  }

  .br-cari {
    display: flex;
    gap: 6px;
    background: #fff;
    border: 2px solid var(--arang);
    border-radius: 999px;
    padding: 5px;
    max-width: 520px;
    box-shadow: 5px 5px 0 var(--arang)
  }

  .br-cari input {
    flex: 1;
    min-width: 0;
    border: 0;
    background: none;
    padding: 10px 18px;
    font: inherit;
    font-size: 16px;
    color: var(--arang);
    border-radius: 999px
  }

  .br-cari button {
    border: 0;
    border-radius: 999px;
    background: var(--cabai);
    color: #fff;
    font: inherit;
    font-weight: 700;
    padding: 0 26px;
    cursor: pointer
  }

  .br-cari button:hover {
    background: #9a2a12
  }

  .br-dekat {
    display: inline-block;
    margin-top: 20px;
    color: var(--daun);
    font-weight: 700;
    text-underline-offset: 4px
  }

  .br-saji {
    position: relative;
    aspect-ratio: 1;
    width: 100%;
    max-width: 430px;
    justify-self: end;
    margin: 22px 0
  }

  .br-saji img {
    position: absolute;
    border-radius: 50%;
    object-fit: cover;
    aspect-ratio: 1;
    border: 9px solid #fff;
    box-shadow: 0 18px 34px rgba(80, 45, 0, .3);
    animation: br-putar .9s cubic-bezier(.2, .8, .2, 1) both
  }

  .br-saji img:nth-child(1) {
    width: 78%;
    right: 0;
    top: 4%
  }

  .br-saji img:nth-child(2) {
    width: 40%;
    left: 0;
    bottom: 2%;
    animation-delay: .12s
  }

  .br-saji img:nth-child(3) {
    width: 29%;
    left: 7%;
    top: 0;
    animation-delay: .22s
  }

  @keyframes br-putar {
    from {
      opacity: 0;
      transform: rotate(-40deg) scale(.85)
    }

    to {
      opacity: 1;
      transform: none
    }
  }

  #pilih {
    scroll-margin-top: 72px
  }

  .br-judul {
    font-size: clamp(26px, 3.4vw, 36px);
    margin: 44px 0 22px
  }

  .br-piring {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 22px 10px
  }

  .br-piring a {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-align: center;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 600;
    line-height: 1.3
  }

  .br-bulat {
    flex: none;
    width: min(100%, 112px);
    aspect-ratio: 1;
    border-radius: 50%;
    padding: 4px;
    border: 3px solid transparent;
    transition: transform .25s, border-color .2s
  }

  .br-bulat img,
  .br-bulat b {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 50%;
    object-fit: cover;
    display: flex;
    align-items: center;
    justify-content: center
  }

  .br-bulat b {
    background: var(--daun);
    color: #fff;
    font-size: 28px
  }

  .br-piring small {
    display: block;
    font-weight: 500;
    color: var(--abu)
  }

  .br-piring a:hover .br-bulat {
    transform: rotate(8deg);
    border-color: var(--kunyit)
  }

  .br-piring a.aktif .br-bulat {
    border-color: var(--cabai)
  }

  .br-piring a.aktif .br-nama {
    color: var(--cabai)
  }

  /* ---------- bar filter ---------- */
  .br-alat {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    margin: 44px 0 20px;
    padding: 14px 16px;
    background: var(--daun);
    border-radius: 18px;
    color: #fff
  }

  .br-alat h2 {
    font-size: 21px;
    margin: 0 auto 0 4px;
    font-family: "Bricolage Grotesque", var(--font-teks)
  }

  .br-alat select,
  .multi summary {
    box-sizing: border-box;
    width: 200px;
    height: 40px;
    font: inherit;
    font-size: 14px;
    line-height: 1.2;
    padding: 0 12px;
    border: 0;
    border-radius: 10px;
    background: #fff;
    color: var(--arang);
    cursor: pointer
  }

  .br-alat a {
    color: var(--kunyit);
    font-weight: 700;
    font-size: 14px
  }

  /* dropdown urutan (boleh pilih lebih dari satu) */
  .multi {
    position: relative;
    width: 200px
  }

  .multi summary {
    list-style: none;
    display: flex;
    align-items: center;
    position: relative;
    padding-right: 34px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap
  }

  .multi summary::-webkit-details-marker {
    display: none
  }

  .multi summary::marker {
    content: ""
  }

  .multi summary::after {
    content: "\25BE";
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%)
  }

  .multi[open] summary {
    outline: 3px solid var(--kunyit)
  }

  .multi-isi {
    position: absolute;
    z-index: 50;
    top: calc(100% + 6px);
    left: 0;
    min-width: 100%;
    width: max-content;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 6px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, .25)
  }

  .multi-isi label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 8px;
    cursor: pointer;
    color: var(--arang);
    font-size: 14px;
    white-space: nowrap
  }

  .multi-isi label:hover {
    background: #f1f1f1
  }

  .multi-isi input[type=checkbox] {
    width: 18px;
    height: 18px;
    margin: 0;
    accent-color: var(--daun)
  }

  .multi-isi .paling-cocok {
    border-bottom: 1px solid #eee;
    border-radius: 8px 8px 0 0;
    margin-bottom: 4px
  }

  /* ---------- daftar restoran ---------- */
  .br-daftar {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    column-gap: 30px
  }

  .br-baris {
    display: grid;
    grid-template-columns: 150px minmax(0, 1fr);
    gap: 18px;
    align-items: center;
    padding: 16px 0;
    border-bottom: 2px dotted #e3d6bd;
    text-decoration: none
  }

  .br-foto {
    width: 150px;
    height: 132px;
    border-radius: 18px;
    overflow: hidden;
    background: #f1e4c6;
    display: flex;
    align-items: center;
    justify-content: center
  }

  .br-foto img {
    width: 100%;
    height: 100%;
    object-fit: cover
  }

  .br-foto span {
    font-size: 44px;
    color: var(--daun)
  }

  .br-baris h3 {
    font-family: "Bricolage Grotesque", var(--font-teks);
    font-size: 20px;
    margin: 0 0 2px;
    line-height: 1.2;
    overflow-wrap: anywhere
  }

  .br-baris:hover h3 {
    color: var(--cabai);
    text-decoration: underline;
    text-underline-offset: 4px
  }

  .br-baris .sub {
    display: block
  }

  .br-angka {
    display: flex;
    flex-wrap: wrap;
    gap: 4px 14px;
    align-items: baseline;
    margin: 8px 0 2px;
    font-size: 14px
  }

  .br-angka b {
    background: var(--kunyit);
    border-radius: 8px;
    padding: 1px 8px
  }

  .br-angka small {
    color: var(--abu)
  }

  .br-atas a:focus-visible,
  .br-cari button:focus-visible,
  .br-cari input:focus-visible,
  .br-piring a:focus-visible,
  .br-baris:focus-visible,
  .br-alat select:focus-visible,
  .multi summary:focus-visible {
    outline: 3px solid var(--daun);
    outline-offset: 2px
  }

  .br-alat select:focus-visible,
  .multi summary:focus-visible {
    outline-color: var(--kunyit)
  }

  @media (max-width:900px) {
    .br-atas .wadah {
      grid-template-columns: minmax(0, 1fr);
      padding-top: 36px;
      gap: 6px
    }

    .br-cari button {
      padding: 0 18px
    }

    .br-saji {
      justify-self: center;
      max-width: 300px
    }

    .br-piring {
      grid-template-columns: none;
      grid-auto-flow: column;
      grid-auto-columns: 96px;
      overflow-x: auto;
      padding-bottom: 10px
    }

    .br-daftar {
      grid-template-columns: minmax(0, 1fr)
    }
  }

  @media (max-width:640px) {
    #pilih {
      scroll-margin-top: 140px
    }
  }

  @media (max-width:480px) {
    .br-baris {
      grid-template-columns: 104px minmax(0, 1fr);
      gap: 14px
    }

    .br-foto {
      width: 104px;
      height: 112px
    }

    .br-alat h2 {
      width: 100%
    }

    .br-alat select,
    .multi,
    .multi summary {
      width: 100%
    }

    .multi {
      flex: 1 1 100%
    }

    .br-alat select {
      flex: 1 1 100%
    }
  }

  @media (prefers-reduced-motion:reduce) {
    .br-saji img {
      animation: none
    }

    .br-bulat {
      transition: none
    }
  }
</style>
<?= $this->endSection() ?>

<?= $this->section('konten') ?>
<?php
// urutan: bisa lebih dari satu (rating, harga). Kosong = "Paling cocok"
$urutDipilih = array_values(array_intersect((array) ($urut ?? []), ['rating', 'harga']));
$labelUrut = [
  'rating' => 'Rating Google tertinggi',
  'harga'  => 'Harga termurah',
];
$teksUrut = match (count($urutDipilih)) {
  0       => 'Paling cocok',
  1       => $labelUrut[$urutDipilih[0]],
  default => count($urutDipilih) . ' urutan dipilih',
};

$param = array_filter([
  'q' => $cari,
  'kecamatan' => $kecamatan,
  'harga' => $harga,
  'urut' => $urutDipilih,
]);
$aktifFilter = $kategori || $param;
$linkKat = static function (?string $k) use ($param): string {
  $q = http_build_query($param + ($k ? ['kategori' => $k] : []));
  return '/' . ($q !== '' ? '?' . $q : '') . '#pilih';
};
// Foto hidangan untuk tiap kategori (dicocokkan dari kata di nama kategori)
$fotoKategori = [
  'Seafood' => 'kepiting-saus-padang',
  'Mie' => 'mie-aceh-goreng',
  'Batak' => 'arsik-ikan-mas',
  'Durian' => 'pancake-durian',
  'Kopi' => 'kopi',
  'Oleh' => 'bika-ambon',
  'Lontong' => 'lontong-sayur',
  'Soto' => 'soto-medan-campur',
  'Peranakan' => 'fu-yung-hai',
  'Melayu' => 'nasi-gurih',
  'Sate' => 'sate-kacang',
  'India' => 'kari-bahan-sapi',
];
$piring = static function (string $nama) use ($fotoKategori): ?string {
  foreach ($fotoKategori as $kata => $berkas) {
    if (stripos($nama, $kata) !== false && is_file(FCPATH . 'img/menu/' . $berkas . '.jpg')) {
      return '/img/menu/' . $berkas . '.jpg';
    }
  }
  return null;
};
$total = array_sum(array_column($daftarKategori, 'jumlah'));
?>
<section class="br-atas">
  <div class="wadah">
    <div>
      <h1 class="br-j">Mau makan apa hari ini?</h1>
      <p><?= $total ?> tempat makan di Medan, dari lontong buat sarapan sampai kerang rebus buat nongkrong malam.</p>
      <form id="form-cari" class="br-cari" method="get" action="/#pilih">
        <?php if ($kategori): ?><input type="hidden" name="kategori" value="<?= esc($kategori) ?>"><?php endif; ?>
        <input type="text" name="q" placeholder="Cari nama restoran" value="<?= esc($cari ?? '') ?>" aria-label="Cari nama restoran">
        <button type="submit">Cari</button>
      </form>
      <a class="br-dekat" href="/terdekat">Cari yang terdekat dari lokasimu</a>
    </div>
    <div class="br-saji" aria-hidden="true">
      <img src="/img/menu/mie-aceh-kepiting.jpg" alt="">
      <img src="/img/menu/sate-kacang.jpg" alt="">
      <img src="/img/menu/es-campur.jpg" alt="">
    </div>
  </div>
</section>

<div class="wadah">
  <section id="pilih">
    <h2 class="br-j br-judul">Pilih dari piringnya</h2>
    <nav class="br-piring" aria-label="Kategori">
      <a href="<?= esc($linkKat(null)) ?>" class="<?= $kategori ? '' : 'aktif' ?>">
        <span class="br-bulat"><b><?= $total ?></b></span>
        <span class="br-nama">Semua</span>
      </a>
      <?php foreach ($daftarKategori as $k): $gambar = $piring((string) $k['kategori_khas']); ?>
        <a href="<?= esc($linkKat($k['kategori_khas'])) ?>" class="<?= $kategori === $k['kategori_khas'] ? 'aktif' : '' ?>">
          <span class="br-bulat">
            <?php if ($gambar): ?><img src="<?= esc($gambar) ?>" alt="" loading="lazy">
            <?php else: ?><b><?= esc(mb_substr((string) $k['kategori_khas'], 0, 1)) ?></b><?php endif; ?>
          </span>
          <span><span class="br-nama"><?= esc($k['kategori_khas']) ?></span><small><?= (int) $k['jumlah'] ?> tempat</small></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </section>

  <div class="br-alat">
    <h2><?= $pager->getTotal() ?> restoran<?= $kategori ? ' ' . esc($kategori) : '' ?></h2>

    <select name="kecamatan" form="form-cari" aria-label="Kecamatan" onchange="this.form.submit()">
      <option value="">Semua kecamatan</option>
      <?php foreach ($daftarKecamatan as $kc): ?>
        <option value="<?= esc($kc['kecamatan']) ?>" <?= $kecamatan === $kc['kecamatan'] ? 'selected' : '' ?>><?= esc($kc['kecamatan']) ?> (<?= $kc['jumlah'] ?>)</option>
      <?php endforeach; ?>
    </select>

    <select name="harga" form="form-cari" aria-label="Harga" onchange="this.form.submit()">
      <option value="">Semua harga</option>
      <option value="murah" <?= $harga === 'murah' ? 'selected' : '' ?>>Murah (sampai Rp 25 rb)</option>
      <option value="sedang" <?= $harga === 'sedang' ? 'selected' : '' ?>>Sedang (Rp 25&ndash;60 rb)</option>
      <option value="mahal" <?= $harga === 'mahal' ? 'selected' : '' ?>>Mahal (di atas Rp 60 rb)</option>
    </select>

    <details class="multi" id="urut-multi">
      <summary aria-label="Urutkan"><?= esc($teksUrut) ?></summary>
      <div class="multi-isi">
        <label class="paling-cocok">
          <input type="checkbox" id="urut-default" <?= empty($urutDipilih) ? 'checked' : '' ?>>
          Paling cocok
        </label>
        <?php foreach ($labelUrut as $nilai => $teks): ?>
          <label>
            <input type="checkbox" name="urut[]" form="form-cari" value="<?= esc($nilai) ?>"
              <?= in_array($nilai, $urutDipilih, true) ? 'checked' : '' ?>>
            <?= esc($teks) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </details>

    <?php if ($aktifFilter): ?><a href="/#pilih">Atur ulang</a><?php endif; ?>
  </div>

  <?php if (empty($restoran)): ?>
    <div class="kosong">
      <p><b>Belum ada restoran yang cocok.</b></p>
      <p>Coba kurangi filter atau ganti kata kunci.</p>
    </div>
  <?php endif; ?>

  <div class="br-daftar">
    <?php foreach ($restoran as $r):
      $foto  = (!empty($r['link_foto']) && is_file(FCPATH . $r['link_foto'])) ? '/' . $r['link_foto'] : null;
      $min   = (int) ($r['harga_min'] ?? 0);
      $max   = (int) ($r['harga_max'] ?? 0);
      $halal = trim((string) ($r['info_halal'] ?? ''));
    ?>
      <a class="br-baris" href="/restoran/<?= esc($r['slug']) ?>">
        <div class="br-foto">
          <?php if ($foto): ?>
            <img src="<?= esc($foto) ?>" alt="" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.hidden=false;">
            <span class="br-j" hidden><?= esc(mb_strtoupper(mb_substr($r['nama'], 0, 1))) ?></span>
          <?php else: ?>
            <span class="br-j"><?= esc(mb_strtoupper(mb_substr($r['nama'], 0, 1))) ?></span>
          <?php endif; ?>
        </div>
        <div>
          <h3><?= esc($r['nama']) ?></h3>
          <span class="sub"><?= esc($r['kategori_khas']) ?><?= !empty($r['kecamatan']) ? ', ' . esc($r['kecamatan']) : '' ?></span>
          <div class="br-angka">
            <span><b>&#9733; <?= esc($r['rating'] ?? '-') ?></b> <small>Google (<?= (int) ($r['jumlah_ulasan'] ?? 0) ?>)</small></span>
            <?php if (!empty($r['jumlah_ulasan_web'])): ?>
              <span>&#9733; <?= esc(number_format((float) $r['rating_web'], 1)) ?> <small>pengunjung web (<?= (int) $r['jumlah_ulasan_web'] ?>)</small></span>
            <?php else: ?>
              <span><small>Belum ada ulasan web</small></span>
            <?php endif; ?>
            <?php if ($max > 0): ?><span>Rp <?= round($min / 1000) ?>&ndash;<?= round($max / 1000) ?> rb</span><?php endif; ?>
          </div>
          <?php if ($halal !== ''): ?>
            <span class="label <?= stripos($halal, 'belum') !== false ? 'belum' : (stripos($halal, 'non') !== false ? 'nonhalal' : 'halal') ?>"><?= esc($halal) ?></span>
          <?php endif; ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <?= $pager->links() ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('skrip') ?>
<script>
  (function() {
    var kotak = document.getElementById('urut-multi');
    var form = document.getElementById('form-cari');
    if (!kotak || !form) return;

    var semua = kotak.querySelectorAll('input[name="urut[]"]');
    var bawaan = document.getElementById('urut-default');

    // "Paling cocok" dipilih -> lepas semua pilihan lain
    bawaan.addEventListener('change', function() {
      semua.forEach(function(c) { c.checked = false; });
      bawaan.checked = true;
      form.submit();
    });

    // pilihan lain berubah -> "Paling cocok" ikut menyesuaikan
    semua.forEach(function(c) {
      c.addEventListener('change', function() {
        var ada = Array.prototype.some.call(semua, function(x) { return x.checked; });
        bawaan.checked = !ada;
        form.submit();
      });
    });

    // tutup dropdown kalau klik di luar
    document.addEventListener('click', function(e) {
      if (!kotak.contains(e.target)) kotak.removeAttribute('open');
    });
  })();
</script>
<?= $this->endSection() ?>