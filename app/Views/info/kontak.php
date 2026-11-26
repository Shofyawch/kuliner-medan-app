<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<div class="wadah halaman-info">
  <a class="kembali" href="/">&larr; Kembali ke Beranda</a>
  <h1 class="judul-halaman">Hubungi Kami</h1>
  <p class="info-sub">Punya saran, koreksi data restoran, atau pertanyaan seputar Kuliner Khas Medan? Silakan hubungi kami.</p>

  <div class="kontak-grid">
    <section class="panel">
      <h2>Informasi Kontak</h2>
      <ul class="kontak-daftar">
        <li>
          <strong>Alamat</strong>
          <span>Kota Medan, Sumatera Utara, Indonesia</span>
        </li>
        <li>
          <strong>Email</strong>
          <span><a href="mailto:halo@kulinermedan.id">halo@kulinermedan.id</a></span>
        </li>
        <li>
          <strong>WhatsApp Layanan Info</strong>
          <span><a href="https://wa.me/6283823253829" target="_blank" rel="noopener">083823253829</a> (Chat saja)</span>
        </li>
      </ul>
      <p class="catatan" style="margin-top:14px;">Butuh jawaban cepat? Cek juga halaman <a href="/faq" style="color:var(--marun);font-weight:600;">FAQ</a>.</p>
    </section>

    <section class="panel">
      <h2>Kirim Pesan</h2>
      <form id="form-kontak" class="form-kontak">
        <div class="grup-input">
          <label for="k-nama">Nama Lengkap</label>
          <input type="text" id="k-nama" name="nama" placeholder="Masukkan nama Anda" required>
        </div>
        <div class="grup-input">
          <label for="k-email">Email</label>
          <input type="email" id="k-email" name="email" placeholder="nama@email.com" required>
        </div>
        <div class="grup-input">
          <label for="k-topik">Topik</label>
          <select id="k-topik" name="topik">
            <option value="Saran / Masukan">Saran / Masukan</option>
            <option value="Koreksi Data Restoran">Koreksi Data Restoran</option>
            <option value="Ajukan Restoran Baru">Ajukan Restoran Baru</option>
            <option value="Kendala Teknis">Kendala Teknis</option>
          </select>
        </div>
        <div class="grup-input">
          <label for="k-pesan">Pesan</label>
          <textarea id="k-pesan" name="pesan" rows="4" placeholder="Tulis pesan atau detail restoran..." required></textarea>
        </div>
        <button type="submit" class="tombol">Kirim Pesan</button>
        <p id="k-status" class="kontak-status" role="status" hidden></p>
      </form>
    </section>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('skrip') ?>
<script>
document.getElementById('form-kontak').addEventListener('submit', function (ev) {
  ev.preventDefault();
  var status = document.getElementById('k-status');
  var nama = document.getElementById('k-nama').value.trim();
  status.textContent = 'Terima kasih, ' + nama + '! Pesan Anda telah dicatat oleh tim Kuliner Khas Medan.';
  status.hidden = false;
  ev.target.reset();
});
</script>
<?= $this->endSection() ?>
