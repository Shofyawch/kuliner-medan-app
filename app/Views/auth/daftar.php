<?= $this->extend('layouts/main') ?>

<?= $this->section('konten') ?>
<div class="wadah" style="max-width: 440px; margin: 40px auto; padding: 0 16px;">
  <div class="panel" style="box-shadow: 0 4px 16px rgba(0,0,0,0.06); padding: 28px 24px;">
    <h1 style="margin: 0 0 6px; font-size: 26px; text-align: center; color: var(--marun);">Daftar Akun</h1>
    <p style="text-align: center; color: var(--abu); margin: 0 0 24px; font-size: 14px;">
      Buat akun baru untuk memberikan ulasan dan rating tempat kuliner
    </p>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-galat">
        <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>

    <form method="post" action="/daftar">
      <?= csrf_field() ?>
      <div style="margin-bottom: 16px;">
        <label for="username" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Username</label>
        <input type="text" id="username" name="username" value="<?= old('username') ?>" placeholder="Contoh: kulinerlover12" style="width: 100%; padding: 11px 14px; border: 1px solid var(--garis); border-radius: 10px; font-size: 15px; font-family: inherit;" required autofocus>
        <small style="color: var(--abu); font-size: 12px;">3-30 karakter (huruf, angka, _ atau -)</small>
      </div>

      <div style="margin-bottom: 16px;">
        <label for="password" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Password</label>
        <div class="input-sandi-wrap">
          <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" style="width: 100%; padding: 11px 14px; border: 1px solid var(--garis); border-radius: 10px; font-size: 15px; font-family: inherit;" minlength="8" required>
          <button type="button" class="btn-sandi" data-toggle-sandi="password" aria-pressed="false" aria-label="Tampilkan password" title="Tampilkan password">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <div style="margin-bottom: 22px;">
        <label for="konfirmasi" style="display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px;">Ulangi Password</label>
        <div class="input-sandi-wrap">
          <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ketik ulang password" style="width: 100%; padding: 11px 14px; border: 1px solid var(--garis); border-radius: 10px; font-size: 15px; font-family: inherit;" required>
          <button type="button" class="btn-sandi" data-toggle-sandi="konfirmasi" aria-pressed="false" aria-label="Tampilkan password" title="Tampilkan password">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          </button>
        </div>
      </div>

      <button type="submit" class="tombol" style="width: 100%; padding: 12px; font-size: 16px; border: 0; cursor: pointer;">
        Daftar Akun Baru
      </button>
    </form>

    <div style="margin-top: 20px; text-align: center; font-size: 14px; color: var(--abu); border-top: 1px solid var(--garis); padding-top: 16px;">
      Sudah punya akun? <a href="/login" style="color: var(--marun); font-weight: bold; text-decoration: none;">Masuk di sini &rarr;</a>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

