-- migrasi_02.sql: dijalankan SEKALI sebagai pemilik (user postgres.<kode>), setelah migrasi_01.sql.

CREATE TABLE ulasan (
  id          serial PRIMARY KEY,
  restoran_id integer NOT NULL REFERENCES restoran(id) ON DELETE CASCADE,
  pengguna_id integer NOT NULL REFERENCES pengguna(id) ON DELETE CASCADE,
  rating      smallint NOT NULL CHECK (rating BETWEEN 1 AND 5),
  komentar    text CHECK (komentar IS NULL OR char_length(komentar) <= 500),
  versi       integer NOT NULL DEFAULT 1,
  created_at  timestamptz NOT NULL DEFAULT now(),
  updated_at  timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT ulasan_satu_per_pengguna UNIQUE (restoran_id, pengguna_id)
);

CREATE INDEX idx_ulasan_restoran ON ulasan (restoran_id, created_at DESC);

CREATE TRIGGER trg_ulasan_versi BEFORE UPDATE ON ulasan
  FOR EACH ROW EXECUTE FUNCTION trg_naikkan_versi();

CREATE VIEW v_ulasan AS
SELECT u.id, u.restoran_id, r.nama AS restoran_nama, u.pengguna_id,
       p.username, u.rating, u.komentar, u.versi, u.created_at, u.updated_at
FROM ulasan u
JOIN restoran r ON r.id = u.restoran_id
JOIN pengguna p ON p.id = u.pengguna_id;

-- Hak akses minimal
DO $$ BEGIN
  IF EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'anon') THEN
    EXECUTE 'REVOKE ALL ON ulasan, v_ulasan FROM anon, authenticated';
    EXECUTE 'REVOKE ALL ON SEQUENCE ulasan_id_seq FROM anon, authenticated';
  END IF;
END $$;

GRANT SELECT, INSERT, UPDATE, DELETE ON ulasan TO kuliner_api;
GRANT USAGE, SELECT ON SEQUENCE ulasan_id_seq TO kuliner_api;
GRANT SELECT ON v_ulasan TO kuliner_api, kuliner_readonly;
GRANT SELECT ON ulasan TO kuliner_readonly;
GRANT INSERT ON pengguna TO kuliner_api;
GRANT USAGE, SELECT ON SEQUENCE pengguna_id_seq TO kuliner_api;
