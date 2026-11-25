/* Login pengguna dan ulasan restoran untuk situs utama. Semua data lewat API (/api/v1). */
(function () {
  'use strict';

  var API = '/api/v1';
  var K_TOKEN = 'pub_token';
  var K_NAMA = 'pub_nama';
  var token = sessionStorage.getItem(K_TOKEN) || '';
  var saya = sessionStorage.getItem(K_NAMA) || '';
  var modeDaftar = false;

  function h(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function $(s) { return document.querySelector(s); }
  function pesan(e) {
    var p = e && e.pesan ? e.pesan : 'Terjadi kesalahan.';
    if (e && e.errors) {
      p += '\n' + Object.keys(e.errors).map(function (k) { return '- ' + k + ': ' + e.errors[k]; }).join('\n');
    }
    return p;
  }

  async function api(metode, path, body) {
    var opt = { method: metode, headers: { 'Content-Type': 'application/json' } };
    if (token) opt.headers.Authorization = 'Bearer ' + token;
    if (body !== undefined) opt.body = JSON.stringify(body);
    var res;
    try {
      res = await fetch(API + path, opt);
    } catch (e) {
      throw { kode: 0, pesan: 'Tidak bisa menghubungi server.' };
    }
    var json = null;
    try { json = await res.json(); } catch (e) { /* bukan JSON */ }
    if (res.status === 401 && token) {
      keluar(false);
      throw { kode: 401, pesan: 'Sesi berakhir, silakan masuk lagi.' };
    }
    if (!res.ok) {
      throw { kode: res.status, pesan: (json && json.message) || 'Terjadi kesalahan.', errors: json && json.errors };
    }
    return json;
  }

  /* ---------- tampilan: gaya, bilah atas, dialog ---------- */
  var css = [
    '.u-bar{position:fixed;top:10px;right:12px;z-index:60;display:flex;gap:8px;align-items:center;background:#fff;border:1px solid #e4d8c3;border-radius:999px;padding:6px 8px 6px 14px;box-shadow:0 2px 10px #0002;font:14px system-ui,sans-serif;color:#2b2220}',
    '.u-btn{font:inherit;padding:6px 12px;border:1px solid #e4d8c3;background:#fff;border-radius:999px;cursor:pointer;color:#2b2220}',
    '.u-btn:hover{border-color:#6b1f2a}',
    '.u-utama{background:#6b1f2a;color:#fff;border-color:#6b1f2a}',
    '.u-bahaya{color:#b3261e;border-color:#e8b9b5}',
    'dialog.u-dlg{border:none;border-radius:14px;padding:22px;width:min(380px,92vw);box-shadow:0 10px 40px #0004;font:15px system-ui,sans-serif}',
    'dialog.u-dlg::backdrop{background:#0006}',
    '.u-dlg label,.u-ul label{display:block;font-size:13px;margin:10px 0 4px;color:#5a4a44}',
    '.u-dlg input,.u-ul select,.u-ul textarea{font:inherit;padding:9px 10px;border:1px solid #e4d8c3;border-radius:8px;width:100%;box-sizing:border-box;background:#fff}',
    '.u-galat{color:#b3261e;font-size:13px;min-height:18px;margin:8px 0;white-space:pre-line}',
    '.u-tautan{background:none;border:none;color:#6b1f2a;text-decoration:underline;padding:0;cursor:pointer;font:inherit}',
    '.u-ul .u-item{padding:10px 0;border-bottom:1px solid #e4d8c3}',
    '.u-ul .u-item small{color:#7a6a63}',
    '.u-bintang{color:#a67c00;font-weight:600;white-space:nowrap}',
    '.u-ajakan{background:#f3ead9;border-radius:10px;padding:12px 14px;margin-top:12px}',
    '#u-toast{position:fixed;bottom:18px;left:50%;transform:translateX(-50%);background:#2b2220;color:#fff;padding:10px 16px;border-radius:10px;opacity:0;transition:opacity .2s;pointer-events:none;z-index:70;font:14px system-ui,sans-serif}',
    '#u-toast.tampil{opacity:1}'
  ].join('\n');

  function toast(teks) {
    var t = $('#u-toast');
    t.textContent = teks;
    t.classList.add('tampil');
    setTimeout(function () { t.classList.remove('tampil'); }, 2600);
  }

  function gambarBar() {
    var b = $('#u-bar');
    b.innerHTML = token
      ? '<span>Halo, <b>' + h(saya) + '</b></span><button class="u-btn" data-u="keluar" type="button">Keluar</button>'
      : '<span>Ingin menulis ulasan?</span><button class="u-btn u-utama" data-u="buka-masuk" type="button">Masuk / Daftar</button>';
  }

  function pasangKerangka() {
    var gaya = document.createElement('style');
    gaya.textContent = css;
    document.head.appendChild(gaya);

    var bar = document.createElement('div');
    bar.id = 'u-bar';
    bar.className = 'u-bar';
    document.body.appendChild(bar);

    var dlg = document.createElement('dialog');
    dlg.id = 'u-dlg';
    dlg.className = 'u-dlg';
    dlg.innerHTML =
      '<h2 id="u-judul" style="margin:0 0 4px">Masuk</h2>' +
      '<form id="u-form-masuk">' +
      '<label for="u-user">Username</label><input id="u-user" name="username" autocomplete="username" required>' +
      '<label for="u-pass">Password</label><input id="u-pass" name="password" type="password" autocomplete="current-password" required>' +
      '<p id="u-galat" class="u-galat" role="alert"></p>' +
      '<button class="u-btn u-utama" id="u-kirim" type="submit">Masuk</button> ' +
      '<button class="u-btn" data-u="tutup" type="button">Batal</button>' +
      '</form>' +
      '<p style="font-size:13px;margin:14px 0 0"><span id="u-teks-ganti">Belum punya akun?</span> ' +
      '<button class="u-tautan" id="u-ganti" data-u="ganti" type="button">Daftar</button></p>';
    document.body.appendChild(dlg);

    var t = document.createElement('div');
    t.id = 'u-toast';
    t.setAttribute('role', 'status');
    document.body.appendChild(t);
  }

  function aturMode(daftar) {
    modeDaftar = daftar;
    $('#u-judul').textContent = daftar ? 'Daftar akun baru' : 'Masuk';
    $('#u-kirim').textContent = daftar ? 'Daftar' : 'Masuk';
    $('#u-teks-ganti').textContent = daftar ? 'Sudah punya akun?' : 'Belum punya akun?';
    $('#u-ganti').textContent = daftar ? 'Masuk' : 'Daftar';
    $('#u-pass').autocomplete = daftar ? 'new-password' : 'current-password';
    $('#u-galat').textContent = '';
  }
  function bukaDialog(daftar) {
    aturMode(!!daftar);
    $('#u-dlg').showModal();
  }

  /* ---------- masuk dan keluar ---------- */
  function keluar(umumkan) {
    token = '';
    saya = '';
    sessionStorage.removeItem(K_TOKEN);
    sessionStorage.removeItem(K_NAMA);
    gambarBar();
    muatUlasan();
    if (umumkan !== false) toast('Kamu sudah keluar.');
  }

  async function prosesMasuk(ev) {
    ev.preventDefault();
    var f = ev.target;
    var u = f.elements.username.value.trim();
    var p = f.elements.password.value;
    $('#u-galat').textContent = '';
    try {
      if (modeDaftar) await api('POST', '/auth/daftar', { username: u, password: p });
      var r = await api('POST', '/auth/login', { username: u, password: p });
      token = r.data.token;
      saya = r.data.username;
      sessionStorage.setItem(K_TOKEN, token);
      sessionStorage.setItem(K_NAMA, saya);
      f.reset();
      $('#u-dlg').close();
      gambarBar();
      toast('Selamat datang, ' + saya + '.');
      muatUlasan();
    } catch (e) {
      $('#u-galat').textContent = pesan(e);
    }
  }

  /* ---------- ulasan di halaman detail restoran ---------- */
  async function muatUlasan() {
    var kotak = $('#panel-ulasan');
    if (!kotak) return;
    var id = kotak.getAttribute('data-restoran-id');
    try {
      var r = await api('GET', '/restoran/' + encodeURIComponent(id) + '/ulasan?per_page=50');
      var milik = saya ? (r.data.filter(function (u) { return u.pengguna === saya; })[0] || null) : null;
      var rata = r.meta.rata_rata_rating;

      var daftar = r.data.map(function (u) {
        return '<div class="u-item"><span class="u-bintang">' + '&#9733;'.repeat(u.rating) + '&#9734;'.repeat(5 - u.rating) +
          '</span> <b>' + h(u.pengguna) + '</b> <small>' + h(String(u.diubah || u.dibuat).slice(0, 10)) + '</small>' +
          (u.komentar ? '<div>' + h(u.komentar) + '</div>' : '') + '</div>';
      }).join('') || '<p class="catatan">Belum ada ulasan. Jadilah yang pertama!</p>';

      var opsi = function (n) {
        return [5, 4, 3, 2, 1].map(function (x) {
          return '<option value="' + x + '"' + (x === n ? ' selected' : '') + '>' + x + ' bintang</option>';
        }).join('');
      };

      var bawah = token
        ? '<h3>' + (milik ? 'Ulasanmu' : 'Tulis ulasan') + '</h3>' +
          '<form id="u-form-ulasan" data-id="' + h(id) + '" data-ulasan="' + (milik ? h(milik.id) : '') + '" data-versi="' + (milik ? h(milik.versi) : '') + '">' +
          '<label>Rating</label><select name="rating">' + opsi(milik ? milik.rating : 5) + '</select>' +
          '<label>Komentar (maksimal 500 karakter)</label>' +
          '<textarea name="komentar" rows="3" maxlength="500">' + (milik ? h(milik.komentar || '') : '') + '</textarea>' +
          '<p id="u-galat-ulasan" class="u-galat" role="alert"></p>' +
          '<button class="u-btn u-utama" type="submit">' + (milik ? 'Perbarui ulasan' : 'Kirim ulasan') + '</button> ' +
          (milik ? '<button class="u-btn u-bahaya" type="button" data-u="hapus-ulasan">Hapus ulasanku</button>' : '') +
          '</form>'
        : '<div class="u-ajakan">Ingin menulis ulasan? Kamu perlu masuk dulu. ' +
          '<button class="u-btn u-utama" data-u="buka-masuk" type="button">Masuk / Daftar</button></div>';

      kotak.className = 'panel u-ul';
      kotak.innerHTML = '<h2>Ulasan pengguna</h2>' +
        '<p class="catatan">' + r.meta.total + ' ulasan' + (rata ? ' &middot; rata-rata ' + h(rata) + ' dari 5' : '') + '</p>' +
        daftar + bawah;
    } catch (e) {
      kotak.innerHTML = '<h2>Ulasan pengguna</h2><p class="u-galat">' + h(pesan(e)) + '</p>';
    }
  }

  async function kirimUlasan(ev) {
    ev.preventDefault();
    var f = ev.target;
    var body = { rating: Number(f.elements.rating.value), komentar: f.elements.komentar.value };
    try {
      if (f.getAttribute('data-ulasan')) {
        body.versi = Number(f.getAttribute('data-versi'));
        await api('PUT', '/ulasan/' + f.getAttribute('data-ulasan'), body);
      } else {
        await api('POST', '/restoran/' + f.getAttribute('data-id') + '/ulasan', body);
      }
      toast('Ulasan tersimpan.');
      muatUlasan();
    } catch (e) {
      var g = $('#u-galat-ulasan');
      if (g) g.textContent = pesan(e) + (e.kode === 409 ? '\nMuat ulang halaman, lalu coba lagi.' : '');
    }
  }

  async function hapusUlasan() {
    var f = $('#u-form-ulasan');
    if (!f || !f.getAttribute('data-ulasan') || !confirm('Hapus ulasanmu?')) return;
    try {
      await api('DELETE', '/ulasan/' + f.getAttribute('data-ulasan'));
      toast('Ulasan dihapus.');
      muatUlasan();
    } catch (e) {
      toast(pesan(e));
    }
  }

  /* ---------- penghubung ---------- */
  function mulai() {
    pasangKerangka();
    gambarBar();
    document.addEventListener('click', function (ev) {
      var el = ev.target.closest('[data-u]');
      if (!el) return;
      var a = el.getAttribute('data-u');
      if (a === 'buka-masuk') bukaDialog(false);
      else if (a === 'keluar') keluar(true);
      else if (a === 'tutup') $('#u-dlg').close();
      else if (a === 'ganti') aturMode(!modeDaftar);
      else if (a === 'hapus-ulasan') hapusUlasan();
    });
    document.addEventListener('submit', function (ev) {
      if (ev.target.id === 'u-form-masuk') prosesMasuk(ev);
      else if (ev.target.id === 'u-form-ulasan') kirimUlasan(ev);
    });
    muatUlasan();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', mulai);
  else mulai();
})();
