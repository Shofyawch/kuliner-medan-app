<?php
/**
 * @var string|null $cari
 * @var string|null $kecamatan
 * @var string|null $sarapan
 * @var string|null $ratingMin
 * @var array $daftarKecamatan
 * @var array $hotels
 * @var array|null $meta
 */
?>
<?= $this->extend('layouts/main') ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<?php
$param = array_filter([
    'q' => $cari, 'kecamatan' => $kecamatan, 'sarapan' => $sarapan, 'rating_min' => $ratingMin,
]);
$aktifFilter = !empty($param);
$page = (int) ($meta['page'] ?? 1);
$totalPages = (int) ($meta['total_pages'] ?? 1);
$totalHotel = (int) ($meta['total'] ?? count($hotels));

$linkPage = static function (int $p) use ($param): string {
    $q = http_build_query(array_merge($param, ['page' => $p]));
    return '/hotel' . ($q !== '' ? '?' . $q : '');
};
?>
<section class="hero">
  <div class="wadah">
    <h1>Penginapan di Medan</h1>
    <p>Data real-time terintegrasi langsung dari Web API <b>Singgah Medan</b></p>
    <form id="form-cari-hotel" class="cari" method="get" action="/hotel">
      <input type="text" name="q" placeholder="Cari nama hotel..." value="<?= esc($cari ?? '') ?>" aria-label="Cari nama hotel">
      <?php if ($kecamatan): ?><input type="hidden" name="kecamatan" value="<?= esc($kecamatan) ?>"><?php endif; ?>
      <?php if ($sarapan): ?><input type="hidden" name="sarapan" value="<?= esc($sarapan) ?>"><?php endif; ?>
      <?php if ($ratingMin): ?><input type="hidden" name="rating_min" value="<?= esc($ratingMin) ?>"><?php endif; ?>
      <button type="submit">Cari</button>
    </form>
    <a class="tautan" href="/">Kembali ke daftar kuliner &rarr;</a>
  </div>
</section>

<div class="wadah">
  <div class="filter" style="margin-top: 20px;">
    <select name="kecamatan" form="form-cari-hotel" aria-label="Kecamatan" onchange="this.form.submit()">
      <option value="">Semua kecamatan</option>
      <?php foreach ($daftarKecamatan as $kc): ?>
        <option value="<?= esc($kc['kecamatan']) ?>" <?= $kecamatan === $kc['kecamatan'] ? 'selected' : '' ?>>
          <?= esc($kc['kecamatan']) ?> (<?= esc($kc['jumlah']) ?>)
        </option>
      <?php endforeach; ?>
    </select>

    <select name="sarapan" form="form-cari-hotel" aria-label="Fasilitas Sarapan" onchange="this.form.submit()">
      <option value="">Semua tipe sarapan</option>
      <option value="ya" <?= $sarapan === 'ya' ? 'selected' : '' ?>>Termasuk Sarapan</option>
    </select>

    <select name="rating_min" form="form-cari-hotel" aria-label="Rating Minimal" onchange="this.form.submit()">
      <option value="">Semua rating</option>
      <option value="4" <?= $ratingMin === '4' ? 'selected' : '' ?>>Rating &ge; 4.0</option>
      <option value="4.5" <?= $ratingMin === '4.5' ? 'selected' : '' ?>>Rating &ge; 4.5</option>
    </select>

    <?php if ($aktifFilter): ?><a href="/hotel">Atur ulang</a><?php endif; ?>
  </div>

  <p class="info-hasil">
    <?= $totalHotel ?> hotel ditemukan &middot; Halaman <?= $page ?> dari <?= max(1, $totalPages) ?>
  </p>

  <?php if (empty($hotels)): ?>
    <div class="kosong">
      <p><b>Belum ada data penginapan yang sesuai kriteria.</b></p>
      <p>Coba ubah kata kunci atau pilih kecamatan lain.</p>
    </div>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($hotels as $h):
        $foto = !empty($h['foto']) ? $h['foto'] : null;
        $sarapanTeks = trim((string) ($h['sarapan'] ?? ''));
        $adaSarapan = !empty($sarapanTeks) && (strcasecmp($sarapanTeks, 'ya') === 0 || stripos($sarapanTeks, 'all-in') !== false);
      ?>
        <a class="kartu" href="/hotel/<?= esc($h['id']) ?>">
          <div class="foto">
            <?php if ($foto): ?>
              <img src="<?= esc($foto) ?>" alt="<?= esc($h['nama']) ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
              <span class="inisial" style="display:none;"><?= esc(mb_strtoupper(mb_substr($h['nama'], 0, 1))) ?></span>
            <?php else: ?>
              <span class="inisial"><?= esc(mb_strtoupper(mb_substr($h['nama'], 0, 1))) ?></span>
            <?php endif; ?>
          </div>
          <div class="isi">
            <h3><?= esc($h['nama']) ?></h3>
            <span class="sub">Kec. <?= esc($h['kecamatan'] ?? 'Medan') ?> &middot; <?= esc($h['kota'] ?? 'Medan') ?></span>
            <p class="alamat-singkat" style="font-size:12px;color:var(--abu);margin:4px 0;line-height:1.4;">
              <?= esc($h['alamat'] ?? '') ?>
            </p>
            <div class="baris-kartu">
              <span class="rating">&#9733; <?= esc($h['rating'] ?? '0') ?> <small>(<?= esc($h['jumlah_ulasan'] ?? '0') ?>)</small></span>
              <?php if ($adaSarapan): ?>
                <span class="label" style="background:#e8f5e9;color:#2e7d32;font-weight:600;">Sarapan Tersedia</span>
              <?php else: ?>
                <span class="label belum">Tanpa Sarapan</span>
              <?php endif; ?>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="pagination" style="margin-top:28px;">
        <?php if ($page > 1): ?>
          <a href="<?= esc($linkPage($page - 1)) ?>">&larr; Sebelumnya</a>
        <?php endif; ?>
        <span style="display:inline-block;padding:7px 14px;font-size:14px;color:var(--abu);">
          Halaman <?= $page ?> / <?= $totalPages ?>
        </span>
        <?php if ($page < $totalPages): ?>
          <a href="<?= esc($linkPage($page + 1)) ?>">Selanjutnya &rarr;</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

