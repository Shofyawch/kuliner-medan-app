<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<div class="wadah halaman-info">
  <a class="kembali" href="/">&larr; Kembali ke Beranda</a>
  <h1 class="judul-halaman">Pertanyaan yang Sering Diajukan (FAQ)</h1>
  <p class="info-sub">Temukan jawaban seputar pencarian kuliner, status halal, fitur lokasi terdekat, dan ulasan pengguna.</p>

  <div class="faq-daftar">
    <details class="faq-item" name="faq" open>
      <summary>Bagaimana cara mencari kuliner terdekat dari lokasi saya?</summary>
      <div class="faq-isi">
        <p>Buka halaman <a href="/terdekat">Terdekat</a> dari menu atas, lalu klik tombol <strong>Gunakan Lokasi Saya</strong> dan izinkan akses GPS di browser Anda. Anda juga bisa memilih titik patokan populer (seperti Istana Maimun atau Lapangan Merdeka) dan mengatur radius jarak pencarian.</p>
      </div>
    </details>

    <details class="faq-item" name="faq">
      <summary>Apakah semua restoran di sini sudah terverifikasi halal?</summary>
      <div class="faq-isi">
        <p>Belum semuanya. Setiap restoran dilengkapi label status seperti <strong>Halal</strong>, <strong>Non-Halal</strong>, atau <strong>Belum Terverifikasi</strong> berdasarkan informasi yang tersedia. Kami menyarankan untuk selalu memastikan kembali langsung ke pihak restoran bila diperlukan.</p>
      </div>
    </details>

    <details class="faq-item" name="faq">
      <summary>Apakah harga menu yang tertera selalu sesuai dengan harga di tempat?</summary>
      <div class="faq-isi">
        <p>Harga yang ditampilkan adalah estimasi dan referensi dari pendataan terakhir. Harga dapat berubah sewaktu-waktu sesuai kebijakan masing-masing restoran.</p>
      </div>
    </details>

    <details class="faq-item" name="faq">
      <summary>Bagaimana cara menulis atau mengubah ulasan restoran?</summary>
      <div class="faq-isi">
        <p>Anda perlu <a href="/login">Masuk</a> (atau <a href="/daftar">Daftar akun baru</a> terlebih dahulu). Setelah masuk, buka halaman detail restoran yang ingin Anda ulas, lalu isi rating bintang dan komentar pada kolom ulasan di bagian bawah halaman.</p>
      </div>
    </details>

    <details class="faq-item" name="faq">
      <summary>Apa itu menu Penginapan di navigasi atas?</summary>
      <div class="faq-isi">
        <p>Menu <a href="/hotel">Penginapan</a> menampilkan daftar hotel dan penginapan di Medan yang terintegrasi secara langsung dengan layanan <strong>Singgah Medan</strong>, lengkap dengan rekomendasi tempat makan terdekat dari lokasi hotel pilihan Anda.</p>
      </div>
    </details>

    <details class="faq-item" name="faq">
      <summary>Bagaimana jika ada data restoran yang salah atau ingin mengajukan tempat baru?</summary>
      <div class="faq-isi">
        <p>Silakan kirimkan informasi koreksi atau rekomendasi tempat kuliner melalui halaman <a href="/kontak">Hubungi Kami</a> agar tim kami dapat meninjau dan memperbarui datanya.</p>
      </div>
    </details>
  </div>
</div>
<?= $this->endSection() ?>
