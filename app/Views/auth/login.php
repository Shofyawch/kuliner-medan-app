<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<div class="wadah" style="max-width: 440px; margin: 40px auto; padding: 0 16px;">
  <div class="panel" style="box-shadow: 0 4px 16px rgba(0,0,0,0.06); padding: 28px 24px;">
    <h1 style="margin: 0 0 6px; font-size: 26px; text-align: center; color: var(--marun);">Masuk</h1>
    <p style="text-align: center; color: var(--abu); margin: 0 0 24px; font-size: 14px;">
      Masuk ke akun Anda untuk menulis ulasan kuliner
    </p>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-galat">
        <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-sukses">
        <?= esc(session()->getFlashdata('success')) ?>
      </div>
    <?php endif; ?>

    <div class="alert alert-galat" id="galat-login" role="alert" hidden></div>
    <noscript><div class="alert alert-galat">Aktifkan JavaScript untuk masuk.</div></noscript>

    <form method="post" action="/login" id="form-login">
      <?= csrf_field() ?>
      <input type="hidden" name="token" id="token">
      <div style="margin-bottom: 16px;">
        <label for="username" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Username</label>
        <input type="text" id="username" name="username" value="<?= old('username') ?>" placeholder="Masukkan username Anda" style="width: 100%; padding: 11px 14px; border: 1px solid var(--garis); border-radius: 10px; font-size: 15px; font-family: inherit;" required autofocus>
      </div>

      <div style="margin-bottom: 22px;">
        <label for="password" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Password</label>
        <input type="password" id="password" autocomplete="current-password" placeholder="Masukkan password" style="width: 100%; padding: 11px 14px; border: 1px solid var(--garis); border-radius: 10px; font-size: 15px; font-family: inherit;" required>
      </div>

      <button type="submit" class="tombol" style="width: 100%; padding: 12px; font-size: 16px; border: 0; cursor: pointer;">
        Masuk Sekarang
      </button>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 14px; color: var(--abu); border-top: 1px solid var(--garis); padding-top: 16px;">
      Belum punya akun? <a href="/daftar" style="color: var(--marun); font-weight: bold; text-decoration: none;">Daftar di sini &rarr;</a>
    </div>
  </div>
</div>
<script>
(() => {
  const form = document.getElementById('form-login');
  const galat = document.getElementById('galat-login');
  const tombol = form.querySelector('button[type=submit]');
  const gagal = (pesan) => { galat.textContent = pesan; galat.hidden = false; tombol.disabled = false; };

  // Password hanya dikirim ke API. Ke halaman web yang diteruskan cuma tokennya.
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    galat.hidden = true;
    tombol.disabled = true;
    try {
      const res = await fetch('/api/v1/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ username: form.username.value.trim(), password: form.password.value }),
      });
      const json = await res.json().catch(() => null);
      if (!res.ok || !json?.data?.token) {
        return gagal(json?.message || 'Login gagal (kode ' + res.status + ').');
      }
      document.getElementById('token').value = json.data.token;
      form.submit();
    } catch (err) {
      gagal('Server tidak bisa dihubungi.');
    }
  });
  window.addEventListener('pageshow', () => { tombol.disabled = false; });
})();
</script>
<?= $this->endSection() ?>

