<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<div class="wadah halaman-info">
  <a class="kembali" href="/">&larr; Kembali ke Beranda</a>
  <h1 class="judul-halaman">Kebijakan &amp; Ketentuan</h1>
  <p class="info-sub">Ketentuan penggunaan layanan dan penyajian informasi pada platform Kuliner Khas Medan.</p>

  <section class="panel">
    <h2>1. Akurasi Harga &amp; Menu</h2>
    <p>Informasi harga, jam operasional, dan daftar menu yang ditampilkan merupakan data referensi hasil pendataan. Harga dan ketersediaan menu dapat berubah sewaktu-waktu sesuai kebijakan masing-masing pengelola restoran.</p>

    <h2>2. Status Kehalalan</h2>
    <p>Label status halal dicantumkan berdasarkan informasi yang diperoleh saat pendataan. Untuk restoran dengan status <em>Belum Terverifikasi</em>, pengguna disarankan melakukan konfirmasi langsung kepada pihak restoran.</p>

    <h2>3. Ulasan Pengguna</h2>
    <p>Pengguna yang menulis ulasan bertanggung jawab penuh atas isi komentar yang dikirimkan. Ulasan diharapkan bersifat jujur, sopan, relevan dengan pengalaman kuliner, dan tidak mengandung ujaran kebencian maupun spam.</p>

    <h2>4. Data Peta &amp; Lokasi</h2>
    <p>Tampilan peta menggunakan data dari kontributor <strong>OpenStreetMap</strong>. Akses lokasi GPS pada fitur <em>Terdekat</em> hanya digunakan di browser Anda untuk menghitung jarak restoran dan tidak disimpan sebagai riwayat pelacakan pribadi.</p>
  </section>
</div>
<?= $this->endSection() ?>
