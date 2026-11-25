<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<div class="wadah halaman-info">
  <a class="kembali" href="/">&larr; Kembali ke Beranda</a>
  <h1 class="judul-halaman">Tentang Kuliner Khas Medan</h1>
  <p class="info-sub">Direktori kuliner autentik Kota Medan untuk membantu warga lokal dan wisatawan menemukan cita rasa terbaik.</p>

  <section class="panel">
    <h2>Misi Kami</h2>
    <p>Medan dikenal sebagai salah satu surga kuliner di Indonesia—mulai dari soto Medan, lontong sayur, mie balap, masakan Batak, peranakan Tionghoa, hingga olahan durian legendaris. <strong>Kuliner Khas Medan</strong> hadir untuk merangkum informasi tempat makan tersebut dalam satu tempat yang mudah dicari.</p>
  </section>

  <div class="kontak-grid">
    <section class="panel">
      <h2>Fitur Utama</h2>
      <ul class="daftar-polos">
        <li><strong>Pencarian &amp; Filter Lengkap:</strong> Cari berdasarkan nama, kategori masakan, kecamatan, dan rentang harga.</li>
        <li><strong>Pencarian GPS Terdekat:</strong> Temukan restoran terdekat dari posisi Anda secara real-time lengkap dengan peta interaktif.</li>
        <li><strong>Integrasi Penginapan:</strong> Terhubung dengan data hotel Singgah Medan untuk melihat kuliner di sekitar penginapan Anda.</li>
        <li><strong>Ulasan Komunitas:</strong> Baca dan bagikan pengalaman makan Anda bersama pengguna lainnya.</li>
      </ul>
    </section>

    <section class="panel">
      <h2>Butuh Bantuan?</h2>
      <p>Jika Anda memiliki pertanyaan seputar penggunaan situs atau ingin berkontribusi memperbarui data restoran, silakan kunjungi halaman <a href="/faq" style="color:var(--marun);font-weight:600;">FAQ</a> atau <a href="/kontak" style="color:var(--marun);font-weight:600;">Hubungi Kami</a>.</p>
    </section>
  </div>
</div>
<?= $this->endSection() ?>
