<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin - Kuliner Khas Medan</title>
  <style>
    :root {
      --marun: #6b1f2a;
      --emas: #a67c00;
      --krem: #faf5ec;
      --garis: #e4d8c3;
      --teks: #2b2220;
      --bahaya: #b3261e;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: system-ui, "Segoe UI", Roboto, sans-serif;
      background: var(--krem);
      color: var(--teks);
    }

    .wadah {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 16px;
    }

    header.atas {
      background: var(--marun);
      color: #fff;
      padding: 12px 0;
    }

    header.atas .wadah {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    header.atas b {
      font-size: 18px;
      flex: 1;
    }

    button {
      font: inherit;
      padding: 7px 12px;
      border: 1px solid var(--garis);
      background: #fff;
      border-radius: 8px;
      cursor: pointer;
    }

    button:hover {
      border-color: var(--marun);
    }

    button.utama {
      background: var(--marun);
      color: #fff;
      border-color: var(--marun);
    }

    button.bahaya {
      color: var(--bahaya);
      border-color: #e8b9b5;
    }

    header button {
      background: transparent;
      color: #fff;
      border-color: #ffffff66;
    }

    input,
    select,
    textarea {
      font: inherit;
      padding: 8px 10px;
      border: 1px solid var(--garis);
      border-radius: 8px;
      width: 100%;
      background: #fff;
    }

    label {
      display: block;
      font-size: 13px;
      margin: 10px 0 4px;
      color: #5a4a44;
    }

    .kartu {
      background: #fff;
      border: 1px solid var(--garis);
      border-radius: 14px;
      padding: 20px;
      margin: 24px auto;
    }

    .kecil {
      max-width: 380px;
    }

    .baris {
      display: flex;
      gap: 8px;
      align-items: center;
      flex-wrap: wrap;
      margin: 16px 0 8px;
    }

    .baris input {
      flex: 1;
      min-width: 180px;
      width: auto;
    }

    .tab {
      display: flex;
      gap: 8px;
      margin: 20px 0 4px;
    }

    .tab button.aktif {
      background: var(--marun);
      color: #fff;
      border-color: var(--marun);
    }

    .gulir {
      overflow-x: auto;
      background: #fff;
      border: 1px solid var(--garis);
      border-radius: 12px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      min-width: 640px;
    }

    th,
    td {
      text-align: left;
      padding: 10px 12px;
      border-bottom: 1px solid var(--garis);
      vertical-align: top;
      font-size: 14px;
    }

    th {
      background: #f3ead9;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .04em;
    }

    small {
      color: #7a6a63;
    }

    .aksi {
      white-space: nowrap;
    }

    .info {
      color: #7a6a63;
      font-size: 13px;
      margin: 4px 0 8px;
    }

    .galat {
      color: var(--bahaya);
      font-size: 13px;
      min-height: 18px;
      margin: 8px 0;
      white-space: pre-line;
    }

    .pager {
      display: flex;
      gap: 10px;
      align-items: center;
      justify-content: center;
      margin: 14px 0 30px;
      font-size: 14px;
    }

    dialog {
      border: none;
      border-radius: 14px;
      padding: 20px;
      width: min(640px, 94vw);
      box-shadow: 0 10px 40px #0004;
    }

    dialog::backdrop {
      background: #0006;
    }

    dialog h2 {
      margin: 0 0 4px;
    }

    .dua {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0 12px;
    }

    .tutup {
      float: right;
    }

    #toast {
      position: fixed;
      bottom: 18px;
      left: 50%;
      transform: translateX(-50%);
      background: #2b2220;
      color: #fff;
      padding: 10px 16px;
      border-radius: 10px;
      opacity: 0;
      transition: opacity .2s;
      pointer-events: none;
    }

    #toast.tampil {
      opacity: 1;
    }

    .bintang {
      color: var(--emas);
      white-space: nowrap;
    }

    header.atas a {
      color: #fff;
      font-size: 14px;
    }

    header.atas form {
      margin: 0;
    }

    header.atas .wadah {
      flex-wrap: wrap;
    }

    .foto {
      width: 72px;
      height: 54px;
      object-fit: cover;
      border-radius: 8px;
      background: #f3ead9;
      display: block;
    }
  </style>
</head>

<body>
  <header class="atas">
    <div class="wadah">
      <b>Admin Kuliner Medan</b>
      <span>Masuk sebagai <?= esc($username) ?></span>
      <a href="/" target="_blank" rel="noopener">Lihat situs</a>
      <form method="post" action="/admin/keluar">
        <?= csrf_field() ?>
        <button type="submit">Keluar</button>
      </form>
    </div>
  </header>

  <main class="wadah">
    <section id="v-app">
      <h1 style="margin-bottom:0">Dashboard Admin</h1>
      <nav class="tab">
        <button class="aktif" data-aksi="tab" data-nama="restoran">Restoran</button>
        <button data-aksi="tab" data-nama="ulasan">Ulasan (moderasi)</button>
      </nav>

      <div id="tab-restoran">
        <form id="f-cari" class="baris">
          <input id="q" placeholder="Cari nama restoran" aria-label="Cari nama restoran">
          <button type="submit">Cari</button>
          <button type="button" class="utama" data-aksi="tambah">+ Tambah restoran</button>
        </form>
        <p id="info-restoran" class="info"></p>
        <div class="gulir">
          <table>
            <thead>
              <tr>
                <th>Foto</th>
                <th>Restoran</th>
                <th>Kategori</th>
                <th>Rating</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="tb-restoran"></tbody>
          </table>
        </div>
        <div id="pg-restoran" class="pager"></div>
      </div>

      <div id="tab-ulasan" hidden>
        <p id="info-ulasan" class="info" style="margin-top:20px"></p>
        <div class="gulir">
          <table>
            <thead>
              <tr>
                <th>Restoran</th>
                <th>Pengguna</th>
                <th>Rating</th>
                <th>Komentar</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody id="tb-ulasan"></tbody>
          </table>
        </div>
        <div id="pg-ulasan" class="pager"></div>
      </div>
    </section>
  </main>

  <dialog id="d-restoran">
    <button class="tutup" data-aksi="tutup" data-dlg="d-restoran" type="button">Tutup</button>
    <h2 id="j-restoran">Restoran</h2>
    <form id="f-restoran">
      <input type="hidden" name="id"><input type="hidden" name="versi">
      <label>Nama</label><input name="nama" required>
      <label>Alamat</label><input name="alamat" required>
      <div class="dua">
        <div><label>Kecamatan</label><input name="kecamatan" required></div>
        <div><label>Kategori khas</label><input name="kategori_khas" required></div>
        <div><label>Jenis masakan</label><input name="jenis_masakan" required></div>
        <div><label>No. telepon</label><input name="no_telepon"></div>
        <div><label>Latitude</label><input name="latitude" type="number" step="any" required></div>
        <div><label>Longitude</label><input name="longitude" type="number" step="any" required></div>
        <div><label>Harga minimum (Rp)</label><input name="harga_min" type="number" min="0"></div>
        <div><label>Harga maksimum (Rp)</label><input name="harga_max" type="number" min="0"></div>
      </div>
      <label>Status halal</label>
      <select name="info_halal">
        <option>Status halal belum dicek</option>
        <option>Halal (menurut ulasan)</option>
        <option>Non-halal (menurut ulasan)</option>
      </select>
      <label>Path foto (contoh: img/restoran/nama.jpg)</label><input name="link_foto">
      <label>Link Google Maps</label><input name="link_maps" type="url">
      <label>Deskripsi</label><textarea name="deskripsi" rows="3"></textarea>
      <p id="galat-restoran" class="galat" role="alert"></p>
      <button class="utama" type="submit">Simpan</button>
    </form>
  </dialog>

  <dialog id="d-menu">
    <button class="tutup" data-aksi="tutup" data-dlg="d-menu" type="button">Tutup</button>
    <h2 id="j-menu">Menu</h2>
    <div class="gulir" style="margin:10px 0">
      <table>
        <thead>
          <tr>
            <th>Menu</th>
            <th>Harga</th>
            <th>Kategori</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody id="tb-menu"></tbody>
      </table>
    </div>
    <h3 id="j-form-menu" style="margin-bottom:0">Tambah menu</h3>
    <form id="f-menu">
      <input type="hidden" name="id"><input type="hidden" name="versi">
      <div class="dua">
        <div><label>Nama menu</label><input name="nama_menu" required></div>
        <div><label>Harga (Rp)</label><input name="harga" type="number" min="0" required></div>
        <div><label>Kategori</label><input name="kategori" required placeholder="Makanan / Minuman"></div>
        <div><label>Deskripsi</label><input name="deskripsi"></div>
      </div>
      <p id="galat-menu" class="galat" role="alert"></p>
      <button class="utama" type="submit">Simpan menu</button>
      <button type="button" data-aksi="menu-baru">Kosongkan form</button>
    </form>
  </dialog>

  <div id="toast" role="status"></div>

  <script>
    'use strict';
    const API = '/api/v1';
    const $ = (s) => document.querySelector(s);
    const token = <?= json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const urlFoto = (p) => (/^https?:/i.test(p) ? p : '/' + p.replace(/^\/+/, ''));
    const st = {
      restoran: {
        page: 1,
        total_pages: 1
      },
      ulasan: {
        page: 1,
        total_pages: 1
      },
      menu: [],
      menuRestoran: null
    };

    function h(s) {
      return String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      } [c]));
    }

    function toast(teks) {
      const t = $('#toast');
      t.textContent = teks;
      t.classList.add('tampil');
      setTimeout(() => t.classList.remove('tampil'), 2600);
    }

    function pesanGalat(e) {
      let p = e && e.pesan ? e.pesan : 'Terjadi kesalahan.';
      if (e && e.errors) {
        p += '\n' + Object.entries(e.errors).map(([k, v]) => '- ' + k + ': ' + v).join('\n');
      }
      if (e && e.kode === 409) p += '\nMuat ulang data, lalu ulangi perubahanmu.';
      return p;
    }

    async function api(metode, path, body) {
      const opt = {
        method: metode,
        headers: {
          'Content-Type': 'application/json'
        }
      };
      if (token) opt.headers.Authorization = 'Bearer ' + token;
      if (body !== undefined) opt.body = JSON.stringify(body);
      let res;
      try {
        res = await fetch(API + path, opt);
      } catch (e) {
        throw {
          kode: 0,
          pesan: 'Tidak bisa menghubungi server API.'
        };
      }
      let json = null;
      try {
        json = await res.json();
      } catch (e) {
        /* bukan JSON */ }
      if (res.status === 401) {
        // Token habis: muat ulang halaman. Server memberi token baru, atau meminta login lagi.
        const terakhir = Number(sessionStorage.getItem('admin_muat_ulang') || 0);
        if (Date.now() - terakhir > 60000) {
          sessionStorage.setItem('admin_muat_ulang', String(Date.now()));
          location.reload();
        }
        throw {
          kode: 401,
          pesan: 'Sesi berakhir. Keluar lalu masuk lagi.'
        };
      }
      if (!res.ok) {
        throw {
          kode: res.status,
          pesan: (json && json.message) || 'Terjadi kesalahan.',
          errors: json && json.errors
        };
      }
      return json;
    }

    /* ---------- daftar restoran ---------- */
    async function muatRestoran() {
      const q = $('#q').value.trim();
      try {
        const r = await api('GET', '/restoran?per_page=15&page=' + st.restoran.page + (q ? '&q=' + encodeURIComponent(q) : ''));
        st.restoran.total_pages = r.meta.total_pages || 1;
        $('#info-restoran').textContent = r.meta.total + ' restoran';
        $('#tb-restoran').innerHTML = r.data.map((x) => `
      <tr>
        <td>${x.foto ? `<img class="foto" src="${h(urlFoto(x.foto))}" alt="" loading="lazy">` : '<small>-</small>'}</td>
        <td><b>${h(x.nama)}</b><br><small>${h(x.alamat)}</small></td>
        <td>${h(x.kategori)}<br><small>${h(x.kecamatan)}</small></td>
        <td>${h(x.rating)}</td>
        <td class="aksi">
          <button data-aksi="menu" data-slug="${h(x.slug)}">Menu</button>
          <button data-aksi="ubah" data-slug="${h(x.slug)}">Ubah</button>
          <button class="bahaya" data-aksi="hapus" data-id="${h(x.id)}" data-nama="${h(x.nama)}">Hapus</button>
        </td>
      </tr>`).join('') || '<tr><td colspan="5">Tidak ada data.</td></tr>';
        gambarPager('restoran');
      } catch (e) {
        toast(pesanGalat(e));
      }
    }

    function gambarPager(nama) {
      const p = st[nama];
      $('#pg-' + nama).innerHTML = `
    <button data-aksi="hal" data-tab="${nama}" data-ke="${p.page - 1}" ${p.page <= 1 ? 'disabled' : ''}>&lsaquo; Sebelumnya</button>
    <span>Halaman ${p.page} dari ${p.total_pages}</span>
    <button data-aksi="hal" data-tab="${nama}" data-ke="${p.page + 1}" ${p.page >= p.total_pages ? 'disabled' : ''}>Berikutnya &rsaquo;</button>`;
    }

    /* ---------- form restoran ---------- */
    async function bukaFormRestoran(slug) {
      const f = $('#f-restoran');
      f.reset();
      $('#galat-restoran').textContent = '';
      f.elements.id.value = '';
      f.elements.versi.value = '';
      if (slug) {
        try {
          const d = (await api('GET', '/restoran/' + encodeURIComponent(slug))).data;
          f.elements.id.value = d.id;
          f.elements.versi.value = d.versi ?? '';
          f.elements.nama.value = d.nama ?? '';
          f.elements.alamat.value = d.alamat ?? '';
          f.elements.kecamatan.value = d.kecamatan ?? '';
          f.elements.kategori_khas.value = d.kategori ?? '';
          f.elements.jenis_masakan.value = d.jenis_masakan ?? '';
          f.elements.no_telepon.value = d.no_telepon ?? '';
          f.elements.latitude.value = d.lokasi ? d.lokasi.lat : '';
          f.elements.longitude.value = d.lokasi ? d.lokasi.lng : '';
          f.elements.harga_min.value = d.harga && d.harga.min != null ? d.harga.min : '';
          f.elements.harga_max.value = d.harga && d.harga.max != null ? d.harga.max : '';
          f.elements.info_halal.value = d.info_halal ?? 'Status halal belum dicek';
          f.elements.link_foto.value = d.foto ?? '';
          f.elements.link_maps.value = d.link_maps ?? '';
          f.elements.deskripsi.value = d.deskripsi ?? '';
        } catch (e) {
          toast(pesanGalat(e));
          return;
        }
      }
      $('#j-restoran').textContent = slug ? 'Ubah restoran' : 'Tambah restoran';
      $('#d-restoran').showModal();
    }

    $('#f-restoran').addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const fd = new FormData(ev.target);
      const angka = (k) => (fd.get(k) === '' ? null : Number(fd.get(k)));
      const body = {
        nama: fd.get('nama'),
        alamat: fd.get('alamat'),
        kecamatan: fd.get('kecamatan'),
        latitude: Number(fd.get('latitude')),
        longitude: Number(fd.get('longitude')),
        kategori_khas: fd.get('kategori_khas'),
        jenis_masakan: fd.get('jenis_masakan'),
        harga_min: angka('harga_min'),
        harga_max: angka('harga_max'),
        info_halal: fd.get('info_halal'),
        link_foto: fd.get('link_foto'),
        link_maps: fd.get('link_maps'),
        deskripsi: fd.get('deskripsi'),
        no_telepon: fd.get('no_telepon'),
      };
      const id = fd.get('id');
      try {
        if (id) {
          body.versi = Number(fd.get('versi'));
          await api('PUT', '/restoran/' + id, body);
        } else {
          await api('POST', '/restoran', body);
        }
        $('#d-restoran').close();
        toast('Restoran tersimpan.');
        muatRestoran();
      } catch (e) {
        $('#galat-restoran').textContent = pesanGalat(e);
      }
    });

    async function hapusRestoran(id, nama) {
      if (!confirm('Hapus "' + nama + '" beserta semua menunya?')) return;
      try {
        const r = await api('DELETE', '/restoran/' + id);
        toast('Dihapus. Menu ikut terhapus: ' + r.data.menu_ikut_terhapus);
        muatRestoran();
      } catch (e) {
        toast(pesanGalat(e));
      }
    }

    /* ---------- menu ---------- */
    async function muatMenu() {
      const d = (await api('GET', '/restoran/' + encodeURIComponent(st.menuRestoran.slug))).data;
      st.menuRestoran = {
        id: d.id,
        slug: d.slug,
        nama: d.nama
      };
      st.menu = d.menu || [];
      $('#j-menu').textContent = 'Menu: ' + d.nama;
      $('#tb-menu').innerHTML = st.menu.map((m) => `
    <tr>
      <td>${h(m.nama)}</td>
      <td>Rp ${Number(m.harga).toLocaleString('id-ID')}</td>
      <td>${h(m.kategori)}</td>
      <td class="aksi">
        <button data-aksi="menu-ubah" data-id="${h(m.id)}">Ubah</button>
        <button class="bahaya" data-aksi="menu-hapus" data-id="${h(m.id)}">Hapus</button>
      </td>
    </tr>`).join('') || '<tr><td colspan="4">Belum ada menu.</td></tr>';
    }
    async function bukaMenu(slug) {
      st.menuRestoran = {
        slug
      };
      try {
        await muatMenu();
        kosongkanFormMenu();
        $('#d-menu').showModal();
      } catch (e) {
        toast(pesanGalat(e));
      }
    }

    function kosongkanFormMenu() {
      const f = $('#f-menu');
      f.reset();
      f.elements.id.value = '';
      f.elements.versi.value = '';
      $('#galat-menu').textContent = '';
      $('#j-form-menu').textContent = 'Tambah menu';
    }

    function isiFormMenu(id) {
      const m = st.menu.find((x) => String(x.id) === String(id));
      if (!m) return;
      const f = $('#f-menu');
      f.elements.id.value = m.id;
      f.elements.versi.value = m.versi ?? '';
      f.elements.nama_menu.value = m.nama ?? '';
      f.elements.harga.value = m.harga ?? '';
      f.elements.kategori.value = m.kategori ?? '';
      f.elements.deskripsi.value = m.deskripsi ?? '';
      $('#j-form-menu').textContent = 'Ubah menu';
    }
    $('#f-menu').addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const fd = new FormData(ev.target);
      const body = {
        nama_menu: fd.get('nama_menu'),
        harga: Number(fd.get('harga')),
        kategori: fd.get('kategori'),
        deskripsi: fd.get('deskripsi'),
      };
      const id = fd.get('id');
      try {
        if (id) {
          body.versi = Number(fd.get('versi'));
          await api('PUT', '/menu/' + id, body);
        } else {
          await api('POST', '/restoran/' + st.menuRestoran.id + '/menu', body);
        }
        toast('Menu tersimpan.');
        await muatMenu();
        kosongkanFormMenu();
      } catch (e) {
        $('#galat-menu').textContent = pesanGalat(e);
      }
    });
    async function hapusMenu(id) {
      const m = st.menu.find((x) => String(x.id) === String(id));
      if (!m || !confirm('Hapus menu "' + m.nama + '"?')) return;
      try {
        await api('DELETE', '/menu/' + id);
        toast('Menu dihapus.');
        await muatMenu();
      } catch (e) {
        toast(pesanGalat(e));
      }
    }

    /* ---------- moderasi ulasan ---------- */
    async function muatUlasan() {
      try {
        const r = await api('GET', '/ulasan?per_page=15&page=' + st.ulasan.page);
        st.ulasan.total_pages = r.meta.total_pages || 1;
        $('#info-ulasan').textContent = r.meta.total + ' ulasan';
        $('#tb-ulasan').innerHTML = r.data.map((u) => `
      <tr>
        <td>${h(u.restoran)}</td>
        <td>${h(u.pengguna)}</td>
        <td class="bintang">${'&#9733;'.repeat(u.rating)}</td>
        <td>${h(u.komentar || '-')}<br><small>${h(u.dibuat)}</small></td>
        <td class="aksi"><button class="bahaya" data-aksi="ulasan-hapus" data-id="${h(u.id)}">Hapus</button></td>
      </tr>`).join('') || '<tr><td colspan="5">Belum ada ulasan.</td></tr>';
        gambarPager('ulasan');
      } catch (e) {
        toast(pesanGalat(e));
      }
    }
    async function hapusUlasan(id) {
      if (!confirm('Hapus ulasan ini?')) return;
      try {
        await api('DELETE', '/ulasan/' + id);
        toast('Ulasan dihapus.');
        muatUlasan();
      } catch (e) {
        toast(pesanGalat(e));
      }
    }

    /* ---------- penghubung klik ---------- */
    const aksi = {
      tab: (d) => {
        document.querySelectorAll('.tab button').forEach((b) => b.classList.toggle('aktif', b.dataset.nama === d.nama));
        $('#tab-restoran').hidden = d.nama !== 'restoran';
        $('#tab-ulasan').hidden = d.nama !== 'ulasan';
        if (d.nama === 'ulasan') muatUlasan();
        else muatRestoran();
      },
      tambah: () => bukaFormRestoran(null),
      ubah: (d) => bukaFormRestoran(d.slug),
      hapus: (d) => hapusRestoran(d.id, d.nama),
      menu: (d) => bukaMenu(d.slug),
      'menu-ubah': (d) => isiFormMenu(d.id),
      'menu-hapus': (d) => hapusMenu(d.id),
      'menu-baru': () => kosongkanFormMenu(),
      'ulasan-hapus': (d) => hapusUlasan(d.id),
      hal: (d) => {
        st[d.tab].page = Math.max(1, Number(d.ke));
        if (d.tab === 'restoran') muatRestoran();
        else muatUlasan();
      },
      tutup: (d) => document.getElementById(d.dlg).close(),
    };
    document.addEventListener('click', (ev) => {
      const el = ev.target.closest('[data-aksi]');
      if (!el || el.disabled) return;
      const f = aksi[el.dataset.aksi];
      if (f) f(el.dataset);
    });
    $('#f-cari').addEventListener('submit', (ev) => {
      ev.preventDefault();
      st.restoran.page = 1;
      muatRestoran();
    });

    muatRestoran();
  </script>
</body>

</html>