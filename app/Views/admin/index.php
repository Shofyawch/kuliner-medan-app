<?php
/**
 * @var string $username
 * @var string|null $token
 */
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin - Kuliner Khas Medan</title>
<style>
  :root { --marun:#7a1f2b; --marun-gelap:#5c1520; --emas:#c9962b; --krem:#faf5ec; --garis:#e9dfd0; --teks:#2b2523; --abu:#6b625d; --bahaya:#b3261e; }
  * { box-sizing:border-box; }
  [hidden] { display:none !important; }
  body { margin:0; font-family:system-ui,-apple-system,"Segoe UI",sans-serif; background:var(--krem); color:var(--teks); line-height:1.5; }
  h1,h2,h3 { font-family:Georgia,"Times New Roman",serif; line-height:1.2; }
  .wadah { max-width:1080px; margin:0 auto; padding:0 16px; }
  button { font:inherit; font-size:14px; padding:8px 16px; border:1px solid var(--garis); background:#fff; color:var(--teks); border-radius:999px; cursor:pointer; transition:background .15s,border-color .15s; }
  button:hover:not(:disabled) { border-color:var(--marun); }
  button:disabled { opacity:.45; cursor:default; }
  button:focus-visible, input:focus-visible, select:focus-visible, textarea:focus-visible { outline:3px solid var(--emas); outline-offset:1px; }
  button.utama { background:var(--marun); color:#fff; border-color:var(--marun); font-weight:600; }
  button.utama:hover { background:var(--marun-gelap); }
  button.bahaya { color:var(--bahaya); border-color:#efc7c3; }
  button.bahaya:hover { background:#fdecea; }
  input, select, textarea { font:inherit; font-size:14px; padding:10px 12px; border:1px solid var(--garis); border-radius:10px; width:100%; background:#fff; color:var(--teks); }
  label { display:block; font-size:13px; font-weight:600; margin:12px 0 4px; }
  .tab { display:flex; gap:24px; margin:28px 0 4px; border-bottom:1px solid var(--garis); }
  .tab button { border:0; border-bottom:3px solid transparent; border-radius:0; background:none; padding:8px 2px; font-size:15px; color:var(--abu); }
  .tab button.aktif { color:var(--marun); border-bottom-color:var(--emas); font-weight:700; }
  .baris { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:16px 0 8px; }
  .baris input { flex:1; min-width:180px; width:auto; border-radius:999px; padding-left:18px; }
  .info { color:var(--abu); font-size:13px; margin:12px 0 8px; }
  .gulir { overflow-x:auto; background:#fff; border:1px solid var(--garis); border-radius:16px; box-shadow:0 2px 8px #3c1e0a0f; }
  table { border-collapse:collapse; width:100%; min-width:640px; }
  th, td { text-align:left; padding:12px 16px; border-bottom:1px solid var(--garis); vertical-align:middle; font-size:14px; }
  th { background:#f5ecdb; font-size:12px; color:var(--abu); font-weight:700; }
  tbody tr:hover { background:#fdfaf4; }
  tbody tr:last-child td { border-bottom:0; }
  td small, .info small { color:var(--abu); }
  td b { font-size:15px; }
  .aksi { white-space:nowrap; text-align:right; }
  .aksi button { padding:6px 13px; font-size:13px; }
  .label { display:inline-block; font-size:12px; padding:2px 10px; border-radius:999px; background:#f3ead9; color:#7a5a12; margin-bottom:3px; }
  .bintang { color:#9a6f10; font-weight:700; white-space:nowrap; }
  .galat { color:var(--bahaya); font-size:13px; min-height:18px; margin:8px 0; white-space:pre-line; }
  .pager { display:flex; gap:12px; align-items:center; justify-content:center; margin:18px 0 40px; font-size:14px; }
  dialog { border:0; border-radius:18px; padding:24px; width:min(680px,94vw); box-shadow:0 20px 60px #0005; color:var(--teks); }
  dialog::backdrop { background:#2b0a1099; }
  dialog h2 { margin:0 0 8px; color:var(--marun); }
  .dua { display:grid; grid-template-columns:1fr 1fr; gap:0 14px; }
  .tutup { float:right; }
  #toast { position:fixed; bottom:18px; left:50%; transform:translateX(-50%); background:var(--teks); color:#fff; padding:10px 18px; border-radius:999px; opacity:0; transition:opacity .2s; pointer-events:none; font-size:14px; }
  #toast.tampil { opacity:1; }

  /* ---- header foto ---- */
  .hero { position:relative; isolation:isolate; color:#fff; background:#22090d; min-height:460px; display:flex; flex-direction:column; }
  .kolase { position:absolute; inset:0; z-index:-1; display:grid; grid-template-columns:1fr 1fr 1fr; gap:3px; overflow:hidden; }
  .kolase img { width:100%; height:100%; object-fit:cover; min-height:0; position:absolute; }
  .kolase img:nth-child(1) { left:0; width:34%; }
  .kolase img:nth-child(2) { left:34.2%; width:32%; }
  .kolase img:nth-child(3) { left:66.4%; width:33.6%; }
  .kolase::after { content:""; position:absolute; inset:0; background:linear-gradient(90deg,#22090df2 0%,#22090db8 42%,#22090d26 100%), linear-gradient(0deg,#22090de6,transparent 55%); }
  .nav { width:100%; display:flex; align-items:center; gap:18px; padding-top:18px; padding-bottom:14px; border-bottom:1px solid #ffffff30; }
  .nav b { flex:1; font:bold 19px Georgia,serif; }
  .nav b span { color:var(--emas); }
  .nav a { color:#fff; font-size:14px; }
  .nav form { margin:0; }
  .nav button { background:transparent; color:#fff; border-color:#ffffff70; }
  .judul { width:100%; margin-top:auto; padding-bottom:30px; }
  .sapa { margin:0; color:#f3c45c; font:italic 20px Georgia,serif; }
  .judul h1 { margin:6px 0 22px; font-size:clamp(38px,7vw,72px); line-height:1; letter-spacing:-.01em; text-shadow:0 3px 20px #0008; }
  .judul h1 em { color:var(--emas); }
  .angka { display:flex; margin:0; }
  .angka div { padding:0 28px; border-left:1px solid #ffffff45; }
  .angka div:first-child { padding-left:0; border-left:0; }
  .angka dd { margin:0; font:34px/1.1 Georgia,serif; }
  .angka dt { font-size:13px; color:#ecd9bd; }
  /* ---- kartu restoran ---- */
  .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:22px; }
  .kartu-resto { background:#fff; border-radius:6px; overflow:hidden; display:flex; flex-direction:column; box-shadow:0 2px 10px #3c1e0a17; transition:transform .2s,box-shadow .2s; }
  .kartu-resto:hover { transform:translateY(-5px); box-shadow:0 18px 36px #3c1e0a33; }
  .gambar { position:relative; height:210px; overflow:hidden; background:linear-gradient(135deg,var(--marun),#2b0a10); color:#fff; }
  .gambar img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; transition:transform .4s; }
  .kartu-resto:hover img { transform:scale(1.07); }
  .gambar::after { content:""; position:absolute; inset:0; background:linear-gradient(0deg,#1d060acc,transparent 62%); }
  .inisial { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font:90px Georgia,serif; color:#ffffff40; }
  .nilai { position:absolute; z-index:1; top:12px; right:12px; background:var(--emas); color:#2b1a05; font-weight:700; font-size:13px; padding:3px 10px; border-radius:4px; }
  .nama { position:absolute; z-index:1; left:16px; right:16px; bottom:12px; }
  .nama small { color:#f3c45c; font-weight:600; }
  .nama h3 { margin:2px 0 0; font-size:21px; }
  .kartu-resto p { margin:0; padding:14px 16px; font-size:13px; color:var(--abu); flex:1; }
  .kartu-resto .aksi { display:flex; border-top:1px solid var(--garis); text-align:center; }
  .kartu-resto .aksi button { flex:1; border:0; border-radius:0; padding:11px; font-weight:600; }
  .kartu-resto .aksi button + button { border-left:1px solid var(--garis); }
  .kartu-resto .aksi button:hover { background:#f5ecdb; }
  .kartu-resto .aksi button.bahaya:hover { background:#fdecea; }
  @media (max-width:640px) { .hero { min-height:420px; } .kolase img:nth-child(1) { width:100%; } .kolase img:nth-child(n+2) { display:none; } .angka div { padding:0 16px; } .dua { grid-template-columns:1fr; } }
  @media (prefers-reduced-motion:reduce) { .kartu-resto, .gambar img { transition:none; } }
</style>
</head>
<body>
<header class="hero">
  <div class="kolase" aria-hidden="true">
    <img src="/img/restoran/warung-sate-mie-rebus-mak-etek.jpg" alt="">
    <img src="/img/restoran/seafood-pandawa.jpg" alt="">
    <img src="/img/restoran/rm-selat-panjang-8.jpg" alt="">
  </div>
  <nav class="wadah nav">
    <b>Kuliner <span>Khas</span> Medan</b>
    <a href="/" target="_blank" rel="noopener">Lihat situs</a>
    <form method="post" action="/admin/keluar">
      <?= csrf_field() ?>
      <button type="submit">Keluar</button>
    </form>
  </nav>
  <div class="wadah judul">
    <p class="sapa">Horas, <?= esc($username) ?>!</p>
    <h1>Dapur Admin<br><em>Kuliner Medan</em></h1>
    <dl class="angka">
      <div><dd id="s-restoran">-</dd><dt>restoran</dt></div>
      <div><dd id="s-kategori">-</dd><dt>kategori</dt></div>
      <div><dd id="s-ulasan">-</dd><dt>ulasan</dt></div>
    </dl>
  </div>
</header>

<main class="wadah">
  <section id="v-app">
    <nav class="tab">
      <button class="aktif" data-aksi="tab" data-nama="restoran">Restoran</button>
      <button data-aksi="tab" data-nama="ulasan">Ulasan Restoran</button>
      <button data-aksi="tab" data-nama="hotel-ulasan">Ulasan Penginapan</button>
    </nav>

    <div id="tab-restoran">
      <form id="f-cari" class="baris">
        <input id="q" placeholder="Cari nama restoran" aria-label="Cari nama restoran">
        <button type="submit">Cari</button>
        <button type="button" class="utama" data-aksi="tambah">+ Tambah restoran</button>
      </form>
      <p id="info-restoran" class="info"></p>
      <div id="tb-restoran" class="grid"></div>
      <div id="pg-restoran" class="pager"></div>
    </div>

    <div id="tab-ulasan" hidden>
      <p id="info-ulasan" class="info" style="margin-top:20px"></p>
      <div class="gulir">
        <table>
          <thead><tr><th>Restoran</th><th>Pengguna</th><th>Rating</th><th>Komentar</th><th>Aksi</th></tr></thead>
          <tbody id="tb-ulasan"></tbody>
        </table>
      </div>
      <div id="pg-ulasan" class="pager"></div>
    </div>
    <div id="tab-hotel-ulasan" hidden>
      <div class="baris">
        <select id="filter-hotel" aria-label="Filter penginapan" style="max-width:340px">
          <option value="">Semua penginapan</option>
        </select>
        <button type="button" data-aksi="hotel-muat">Muat ulang</button>
      </div>
      <p id="info-hotel-ulasan" class="info"></p>
      <div class="gulir">
        <table>
          <thead><tr><th>Penginapan</th><th>Penulis</th><th>Rating</th><th>Ulasan</th><th>Aksi</th></tr></thead>
          <tbody id="tb-hotel-ulasan"></tbody>
        </table>
      </div>
      <div id="pg-hotel-ulasan" class="pager"></div>
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
      <thead><tr><th>Menu</th><th>Harga</th><th>Kategori</th><th>Aksi</th></tr></thead>
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
const st = { restoran: { page: 1, total_pages: 1 }, ulasan: { page: 1, total_pages: 1 }, 'hotel-ulasan': { page: 1, total_pages: 1, hotelLoaded: false }, menu: [], menuRestoran: null };

function h(s) {
  return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
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
  const opt = { method: metode, headers: { 'Content-Type': 'application/json' } };
  if (token) opt.headers.Authorization = 'Bearer ' + token;
  if (body !== undefined) opt.body = JSON.stringify(body);
  let res;
  try {
    res = await fetch(API + path, opt);
  } catch (e) {
    throw { kode: 0, pesan: 'Tidak bisa menghubungi server API.' };
  }
  let json = null;
  try { json = await res.json(); } catch (e) { /* bukan JSON */ }
  if (res.status === 401) {
    // Token habis: muat ulang halaman. Server memberi token baru, atau meminta login lagi.
    const terakhir = Number(sessionStorage.getItem('admin_muat_ulang') || 0);
    if (Date.now() - terakhir > 60000) {
      sessionStorage.setItem('admin_muat_ulang', String(Date.now()));
      location.reload();
    }
    throw { kode: 401, pesan: 'Sesi berakhir. Keluar lalu masuk lagi.' };
  }
  if (!res.ok) {
    throw { kode: res.status, pesan: (json && json.message) || 'Terjadi kesalahan.', errors: json && json.errors };
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
      <article class="kartu-resto">
        <div class="gambar">
          <span class="inisial">${h(x.nama.charAt(0))}</span>
          ${x.foto ? `<img src="${h(urlFoto(x.foto))}" alt="" loading="lazy" onerror="this.remove()">` : ''}
          <span class="nilai">&#9733; ${h(x.rating)}</span>
          <div class="nama"><small>${h(x.kategori)}</small><h3>${h(x.nama)}</h3></div>
        </div>
        <p><b>${h(x.kecamatan)}</b><br>${h(x.alamat)}</p>
        <div class="aksi">
          <button data-aksi="menu" data-slug="${h(x.slug)}">Menu</button>
          <button data-aksi="ubah" data-slug="${h(x.slug)}">Ubah</button>
          <button class="bahaya" data-aksi="hapus" data-id="${h(x.id)}" data-nama="${h(x.nama)}">Hapus</button>
        </div>
      </article>`).join('') || '<p>Tidak ada data.</p>';
    gambarPager('restoran');
    muatStatistik();
  } catch (e) {
    toast(pesanGalat(e));
  }
}

async function muatStatistik() {
  try {
    const [r, k, u] = await Promise.all([api('GET', '/restoran?per_page=1'), api('GET', '/kategori'), api('GET', '/ulasan?per_page=1')]);
    $('#s-restoran').textContent = r.meta.total;
    $('#s-kategori').textContent = k.data.length;
    $('#s-ulasan').textContent = u.meta.total;
  } catch (e) { /* angka ringkasan tidak wajib */ }
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
  st.menuRestoran = { id: d.id, slug: d.slug, nama: d.nama };
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
  st.menuRestoran = { slug };
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
    muatStatistik();
  } catch (e) {
    toast(pesanGalat(e));
  }
}

/* ---------- moderasi ulasan penginapan ---------- */
async function muatFilterHotel() {
  if (st['hotel-ulasan'].hotelLoaded) return;
  try {
    const r = await api('GET', '/hotel');
    const sel = $('#filter-hotel');
    sel.innerHTML = '<option value="">Semua penginapan</option>' +
      (r.data || []).map((x) => `<option value="${h(x.id)}">${h(x.nama)}</option>`).join('');
    st['hotel-ulasan'].hotelLoaded = true;
  } catch (e) { /* abaikan jika daftar hotel gagal dimuat */ }
}
async function muatHotelUlasan() {
  await muatFilterHotel();
  const hid = $('#filter-hotel').value;
  const qs = 'per_page=15&page=' + st['hotel-ulasan'].page + (hid ? '&hotel_id=' + encodeURIComponent(hid) : '');
  $('#tb-hotel-ulasan').innerHTML = '<tr><td colspan="5">Memuat ulasan penginapan...</td></tr>';
  try {
    const r = await api('GET', '/hotel/ulasan?' + qs);
    st['hotel-ulasan'].total_pages = r.meta.total_pages || 1;
    $('#info-hotel-ulasan').textContent = r.meta.total + ' ulasan penginapan (API Singgah Medan)';
    $('#tb-hotel-ulasan').innerHTML = r.data.map((u) => `
      <tr>
        <td><b>${h(u.hotel)}</b></td>
        <td>${h(u.penulis)}</td>
        <td class="bintang">${'&#9733;'.repeat(Math.max(0, Math.min(5, Number(u.rating) || 0)))}</td>
        <td>${u.judul ? `<b>${h(u.judul)}</b><br>` : ''}${h(u.isi || '-')}<br><small>${h(u.dibuat)}</small></td>
        <td class="aksi"><button class="bahaya" data-aksi="hotel-ulasan-hapus" data-id="${h(u.id)}" data-hotel-id="${h(u.hotel_id)}">Hapus</button></td>
      </tr>`).join('') || '<tr><td colspan="5">Belum ada ulasan penginapan.</td></tr>';
    gambarPager('hotel-ulasan');
  } catch (e) {
    $('#tb-hotel-ulasan').innerHTML = `<tr><td colspan="5" class="galat">${h(pesanGalat(e))}</td></tr>`;
    toast(pesanGalat(e));
  }
}
async function hapusHotelUlasan(id, hotelId) {
  if (!confirm('Hapus ulasan penginapan ini dari server Singgah Medan?')) return;
  try {
    await api('DELETE', '/hotel/ulasan/' + id + (hotelId ? '?hotel_id=' + encodeURIComponent(hotelId) : ''));
    toast('Ulasan penginapan dihapus.');
    muatHotelUlasan();
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
    $('#tab-hotel-ulasan').hidden = d.nama !== 'hotel-ulasan';
    if (d.nama === 'ulasan') muatUlasan();
    else if (d.nama === 'hotel-ulasan') muatHotelUlasan();
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
  'hotel-ulasan-hapus': (d) => hapusHotelUlasan(d.id, d.hotelId),
  'hotel-muat': () => muatHotelUlasan(),
  hal: (d) => {
    st[d.tab].page = Math.max(1, Number(d.ke));
    if (d.tab === 'restoran') muatRestoran();
    else if (d.tab === 'ulasan') muatUlasan();
    else muatHotelUlasan();
  },
  tutup: (d) => document.getElementById(d.dlg).close(),
};
document.addEventListener('click', (ev) => {
  const el = ev.target.closest('[data-aksi]');
  if (!el || el.disabled) return;
  const f = aksi[el.dataset.aksi];
  if (f) f(el.dataset);
});
$('#filter-hotel').addEventListener('change', () => {
  st['hotel-ulasan'].page = 1;
  muatHotelUlasan();
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
