-- migrasi_01.sql: dijalankan SEKALI di Supabase, SETELAH schema, seed, foto, halal, deskripsi.
-- Parameter: -v pw_api=... -v pw_ro=...  (password tidak disimpan di file ini)

-- 1. Nomor id otomatis
CREATE SEQUENCE restoran_id_seq OWNED BY restoran.id;
SELECT setval('restoran_id_seq', COALESCE((SELECT max(id) FROM restoran), 0) + 1, false);
ALTER TABLE restoran ALTER COLUMN id SET DEFAULT nextval('restoran_id_seq');

CREATE SEQUENCE menu_id_seq OWNED BY menu.id;
SELECT setval('menu_id_seq', COALESCE((SELECT max(id) FROM menu), 0) + 1, false);
ALTER TABLE menu ALTER COLUMN id SET DEFAULT nextval('menu_id_seq');

-- 2. Versi dan waktu ubah (untuk penanganan konflik update)
ALTER TABLE restoran
  ADD COLUMN IF NOT EXISTS versi integer NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS updated_at timestamptz NOT NULL DEFAULT now();
ALTER TABLE menu
  ADD COLUMN IF NOT EXISTS versi integer NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS updated_at timestamptz NOT NULL DEFAULT now();

CREATE OR REPLACE FUNCTION trg_naikkan_versi() RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
  NEW.versi := OLD.versi + 1;
  NEW.updated_at := now();
  RETURN NEW;
END $$;

DROP TRIGGER IF EXISTS trg_restoran_versi ON restoran;
CREATE TRIGGER trg_restoran_versi BEFORE UPDATE ON restoran
  FOR EACH ROW EXECUTE FUNCTION trg_naikkan_versi();
DROP TRIGGER IF EXISTS trg_menu_versi ON menu;
CREATE TRIGGER trg_menu_versi BEFORE UPDATE ON menu
  FOR EACH ROW EXECUTE FUNCTION trg_naikkan_versi();

-- 3. Constraint CHECK
ALTER TABLE restoran
  ADD CONSTRAINT restoran_rating_chk CHECK (rating BETWEEN 0 AND 5),
  ADD CONSTRAINT restoran_ulasan_chk CHECK (jumlah_ulasan >= 0),
  ADD CONSTRAINT restoran_harga_chk CHECK (harga_min IS NULL OR harga_max IS NULL OR harga_min <= harga_max),
  ADD CONSTRAINT restoran_koordinat_chk CHECK (latitude BETWEEN -90 AND 90 AND longitude BETWEEN -180 AND 180),
  ADD CONSTRAINT restoran_nama_chk CHECK (btrim(nama) <> '');
ALTER TABLE menu
  ADD CONSTRAINT menu_harga_chk CHECK (harga >= 0),
  ADD CONSTRAINT menu_nama_chk CHECK (btrim(nama_menu) <> '');

DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM menu GROUP BY restoran_id, nama_menu HAVING count(*) > 1) THEN
    ALTER TABLE menu ADD CONSTRAINT menu_restoran_nama_key UNIQUE (restoran_id, nama_menu);
  ELSE
    RAISE NOTICE 'Ada menu ganda, constraint UNIQUE menu dilewati';
  END IF;
END $$;

-- 4. Tabel pengguna untuk login
CREATE TABLE pengguna (
  id serial PRIMARY KEY,
  username text NOT NULL UNIQUE,
  password_hash text NOT NULL,
  role text NOT NULL CHECK (role IN ('admin', 'user')),
  aktif boolean NOT NULL DEFAULT true,
  created_at timestamptz NOT NULL DEFAULT now()
);

-- 5. View ringkasan restoran
CREATE VIEW v_restoran_ringkas AS
SELECT r.id, r.nama, r.slug, r.kecamatan, r.kategori_khas, r.rating,
       r.info_halal, r.link_foto, r.versi,
       count(m.id) AS jumlah_menu,
       min(m.harga) AS menu_termurah,
       max(m.harga) AS menu_termahal
FROM restoran r
LEFT JOIN menu m ON m.restoran_id = r.id
GROUP BY r.id;

-- 6. Fungsi transaksi: tambah restoran + menu sekaligus (gagal sebagian = batal semua)
CREATE FUNCTION tambah_restoran_dengan_menu(p_restoran jsonb, p_menu jsonb)
RETURNS integer LANGUAGE plpgsql AS $$
DECLARE v_id integer;
BEGIN
  INSERT INTO restoran (nama, slug, alamat, kecamatan, latitude, longitude,
                        kategori_khas, jenis_masakan, harga_min, harga_max,
                        info_halal, link_foto, link_maps, deskripsi, no_telepon)
  VALUES (p_restoran->>'nama', p_restoran->>'slug', p_restoran->>'alamat',
          p_restoran->>'kecamatan',
          (p_restoran->>'latitude')::numeric, (p_restoran->>'longitude')::numeric,
          p_restoran->>'kategori_khas', p_restoran->>'jenis_masakan',
          NULLIF(p_restoran->>'harga_min', '')::integer,
          NULLIF(p_restoran->>'harga_max', '')::integer,
          p_restoran->>'info_halal', p_restoran->>'link_foto',
          p_restoran->>'link_maps', p_restoran->>'deskripsi', p_restoran->>'no_telepon')
  RETURNING id INTO v_id;

  INSERT INTO menu (restoran_id, nama_menu, harga, kategori, deskripsi)
  SELECT v_id, x->>'nama_menu', (x->>'harga')::integer,
         COALESCE(x->>'kategori', 'Lainnya'), x->>'deskripsi'
  FROM jsonb_array_elements(COALESCE(p_menu, '[]'::jsonb)) AS x;

  RETURN v_id;
END $$;

-- 7. User database dengan hak minimal
CREATE ROLE kuliner_api LOGIN PASSWORD :'pw_api';
CREATE ROLE kuliner_readonly LOGIN PASSWORD :'pw_ro';

REVOKE ALL ON ALL FUNCTIONS IN SCHEMA public FROM PUBLIC;
DO $$ BEGIN
  IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'anon') THEN
    EXECUTE 'REVOKE ALL ON ALL TABLES IN SCHEMA public FROM anon, authenticated';
    EXECUTE 'REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM anon, authenticated';
    EXECUTE 'REVOKE ALL ON ALL FUNCTIONS IN SCHEMA public FROM anon, authenticated';
  END IF;
END $$;

GRANT USAGE ON SCHEMA public TO kuliner_api, kuliner_readonly;

GRANT SELECT, INSERT, UPDATE, DELETE ON restoran, menu TO kuliner_api;
GRANT SELECT ON pengguna TO kuliner_api;
GRANT SELECT, INSERT ON log_integrasi TO kuliner_api;
GRANT USAGE, SELECT ON SEQUENCE restoran_id_seq, menu_id_seq, log_integrasi_id_seq TO kuliner_api;
GRANT SELECT ON v_restoran_ringkas TO kuliner_api, kuliner_readonly;
GRANT EXECUTE ON FUNCTION restoran_terdekat(double precision, double precision, double precision, integer)
  TO kuliner_api, kuliner_readonly;
GRANT EXECUTE ON FUNCTION tambah_restoran_dengan_menu(jsonb, jsonb) TO kuliner_api;

GRANT SELECT ON restoran, menu, log_integrasi TO kuliner_readonly;
