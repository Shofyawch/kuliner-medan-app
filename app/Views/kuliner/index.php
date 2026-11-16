<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<section class="hero">
  <div class="wadah">
    <h1>Kuliner Khas Medan</h1>
    <p><?= array_sum(array_column($daftarKategori, 'jumlah')) ?> restoran pilihan di Kota Medan</p>
    <form class="cari" method="get" action="/">
      <?php if ($kategori): ?><input type="hidden" name="kategori" value="<?= esc($kategori) ?>"><?php endif; ?>
      <input type="text" name="q" placeholder="Cari nama restoran..." value="<?= esc($cari ?? '') ?>" aria-label="Cari nama restoran">
      <button type="submit">Cari</button>
    </form>
    <a class="tautan" href="/terdekat">Atau cari yang terdekat dari lokasimu &rarr;</a>
  </div>
</section>

<div class="wadah">
  <nav class="kategori" aria-label="Kategori">
    <a href="/" class="<?= $kategori ? '' : 'aktif' ?>">Semua</a>
    <?php foreach ($daftarKategori as $k): ?>
      <a href="/?kategori=<?= urlencode($k['kategori_khas']) ?>" class="<?= $kategori === $k['kategori_khas'] ? 'aktif' : '' ?>">
        <?= esc($k['kategori_khas']) ?> (<?= $k['jumlah'] ?>)
      </a>
    <?php endforeach; ?>
  </nav>

  <p class="info-hasil"><?= $pager->getTotal() ?> restoran ditemukan</p>

  <?php if (empty($restoran)): ?>
    <div class="kosong">
      <p><b>Belum ada restoran yang cocok.</b></p>
      <p>Coba kata kunci lain atau pilih kategori &ldquo;Semua&rdquo;.</p>
    </div>
  <?php endif; ?>

  <div class="grid">
  <?php foreach ($restoran as $r):
    $foto  = is_file(FCPATH . 'img/restoran/' . $r['id'] . '.jpg') ? '/img/restoran/' . $r['id'] . '.jpg' : null;
    $min   = (int) ($r['harga_min'] ?? 0);
    $max   = (int) ($r['harga_max'] ?? 0);
    $halal = trim((string) ($r['info_halal'] ?? ''));
  ?>
    <a class="kartu" href="/restoran/<?= esc($r['slug']) ?>">
      <div class="foto">
        <?php if ($foto): ?>
          <img src="<?= esc($foto) ?>" alt="<?= esc($r['nama']) ?>" loading="lazy">
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
          <?php if ($max > 0): ?><span class="label">Harga estimasi</span><?php endif; ?>
          <?php if ($halal !== ''): ?>
            <span class="label <?= stripos($halal, 'belum') !== false ? 'belum' : '' ?>"><?= esc($halal) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
  </div>

  <?= $pager->links() ?>
</div>
<?= $this->endSection() ?>
