<?php
/**
 * @var array $daftarKategori
 * @var array $daftarKecamatan
 * @var string $kategori
 * @var string $kecamatan
 * @var string $cari
 * @var string $harga
 * @var string $urut
 * @var array $restoran
 * @var \CodeIgniter\Pager\Pager $pager
 */
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<?php
$param = array_filter([
    'q' => $cari, 'kecamatan' => $kecamatan, 'harga' => $harga, 'urut' => $urut,
]);
$aktifFilter = $kategori || $param;
$linkKat = static function (?string $k) use ($param): string {
    $q = http_build_query($param + ($k ? ['kategori' => $k] : []));
    return '/' . ($q !== '' ? '?' . $q : '');
};
?>
<section class="hero">
  <div class="wadah">
    <h1>Kuliner Khas Medan</h1>
    <p><?= array_sum(array_column($daftarKategori, 'jumlah')) ?> restoran pilihan di Kota Medan</p>
    <form id="form-cari" class="cari" method="get" action="/">
      <?php if ($kategori): ?><input type="hidden" name="kategori" value="<?= esc($kategori) ?>"><?php endif; ?>
      <input type="text" name="q" placeholder="Cari nama restoran..." value="<?= esc($cari ?? '') ?>" aria-label="Cari nama restoran">
      <button type="submit">Cari</button>
    </form>
    <a class="tautan" href="/terdekat">Atau cari yang terdekat dari lokasimu &rarr;</a>
  </div>
</section>

<div class="wadah">
  <nav class="kategori" aria-label="Kategori">
    <a href="<?= esc($linkKat(null)) ?>" class="<?= $kategori ? '' : 'aktif' ?>">Semua</a>
    <?php foreach ($daftarKategori as $k): ?>
      <a href="<?= esc($linkKat($k['kategori_khas'])) ?>" class="<?= $kategori === $k['kategori_khas'] ? 'aktif' : '' ?>">
        <?= esc($k['kategori_khas']) ?> (<?= $k['jumlah'] ?>)
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="filter">
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
    <select name="urut" form="form-cari" aria-label="Urutkan" onchange="this.form.submit()">
      <option value="">Paling cocok</option>
      <option value="rating" <?= $urut === 'rating' ? 'selected' : '' ?>>Rating tertinggi</option>
      <option value="murah" <?= $urut === 'murah' ? 'selected' : '' ?>>Harga termurah</option>
    </select>
    <?php if ($aktifFilter): ?><a href="/">Atur ulang</a><?php endif; ?>
  </div>

  <p class="info-hasil"><?= $pager->getTotal() ?> restoran ditemukan</p>

  <?php if (empty($restoran)): ?>
    <div class="kosong">
      <p><b>Belum ada restoran yang cocok.</b></p>
      <p>Coba kurangi filter atau ganti kata kunci.</p>
    </div>
  <?php endif; ?>

  <div class="grid">
  <?php foreach ($restoran as $r):
    $foto  = (!empty($r['link_foto']) && is_file(FCPATH . $r['link_foto'])) ? '/' . $r['link_foto'] : null;
    $min   = (int) ($r['harga_min'] ?? 0);
    $max   = (int) ($r['harga_max'] ?? 0);
    $halal = trim((string) ($r['info_halal'] ?? ''));
  ?>
    <a class="kartu" href="/restoran/<?= esc($r['slug']) ?>">
      <div class="foto">
        <?php if ($foto): ?>
          <img src="<?= esc($foto) ?>" alt="<?= esc($r['nama']) ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
          <span class="inisial" style="display:none;"><?= esc(mb_strtoupper(mb_substr($r['nama'], 0, 1))) ?></span>
        <?php else: ?>
          <span class="inisial"><?= esc(mb_strtoupper(mb_substr($r['nama'], 0, 1))) ?></span>
        <?php endif; ?>
      </div>
      <div class="isi">
        <h3><?= esc($r['nama']) ?></h3>
        <span class="sub"><?= esc($r['kategori_khas']) ?> &middot; <?= esc($r['kecamatan']) ?></span>
        <div class="baris-kartu">
          <span class="rating">&#9733; <?= esc($r['rating']) ?> <small>(<?= esc($r['jumlah_ulasan']) ?>)</small></span>
          <?php if ($max > 0): ?>
            <span class="harga">Rp <?= round($min / 1000) ?>&ndash;<?= round($max / 1000) ?> rb</span>
          <?php endif; ?>
        </div>
        <div>
          <?php if ($halal !== ''): ?>
            <span class="label <?= stripos($halal, 'belum') !== false ? 'belum' : (stripos($halal, 'non') !== false ? 'nonhalal' : 'halal') ?>"><?= esc($halal) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
  </div>

  <?= $pager->links() ?>
</div>
<?= $this->endSection() ?>
