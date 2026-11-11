-- schema.sql : Kuliner Khas Medan (PostgreSQL)
-- Jalankan: psql -U postgres -d kuliner_medan -f schema.sql
DROP FUNCTION IF EXISTS restoran_terdekat(double precision, double precision, double precision, integer);
DROP TABLE IF EXISTS log_integrasi CASCADE;
DROP TABLE IF EXISTS menu CASCADE;
DROP TABLE IF EXISTS restoran CASCADE;

CREATE TABLE restoran (
    id                 INTEGER PRIMARY KEY,
    nama               TEXT NOT NULL,
    slug               TEXT NOT NULL UNIQUE,
    alamat             TEXT NOT NULL,
    kecamatan          TEXT NOT NULL,
    latitude           NUMERIC(9,6) NOT NULL,
    longitude          NUMERIC(9,6) NOT NULL,
    kategori_khas      TEXT NOT NULL,
    keyakinan_khas     TEXT,
    jenis_masakan      TEXT NOT NULL,
    harga_min          INTEGER,
    harga_max          INTEGER,
    price_level_google SMALLINT,
    jam_buka           TIME,
    jam_tutup          TIME,
    catatan_jam        TEXT,
    rating             NUMERIC(2,1) NOT NULL DEFAULT 0,
    jumlah_ulasan      INTEGER NOT NULL DEFAULT 0,
    no_telepon         TEXT,
    info_halal         TEXT,
    link_foto          TEXT,
    link_maps          TEXT,
    deskripsi          TEXT,
    google_place_id    TEXT UNIQUE,
    created_at         TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at         TIMESTAMP NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_restoran_kecamatan ON restoran(kecamatan);
CREATE INDEX idx_restoran_kategori ON restoran(kategori_khas);
CREATE INDEX idx_restoran_rating ON restoran(rating DESC);

CREATE TABLE menu (
    id           INTEGER PRIMARY KEY,
    restoran_id  INTEGER NOT NULL REFERENCES restoran(id) ON DELETE CASCADE,
    nama_menu    TEXT NOT NULL,
    harga        INTEGER NOT NULL,
    kategori     TEXT NOT NULL,
    status_harga TEXT,
    deskripsi    TEXT
);
CREATE INDEX idx_menu_restoran ON menu(restoran_id);

CREATE TABLE log_integrasi (
    id             BIGSERIAL PRIMARY KEY,
    arah           TEXT NOT NULL CHECK (arah IN ('masuk','keluar')),
    endpoint       TEXT NOT NULL,
    metode         TEXT NOT NULL DEFAULT 'GET',
    parameter      TEXT,
    status_code    INTEGER,
    jumlah_data    INTEGER,
    durasi_ms      INTEGER,
    keterangan     TEXT,
    created_at     TIMESTAMP NOT NULL DEFAULT NOW()
);
CREATE INDEX idx_log_created ON log_integrasi(created_at DESC);

-- Restoran terdekat (rumus Haversine). Contoh:
-- SELECT * FROM restoran_terdekat(3.575278, 98.683889, 2, 5);
CREATE FUNCTION restoran_terdekat(
    p_lat DOUBLE PRECISION,
    p_lng DOUBLE PRECISION,
    p_radius_km DOUBLE PRECISION DEFAULT 2,
    p_limit INTEGER DEFAULT 10
)
RETURNS TABLE (
    id INTEGER, nama TEXT, slug TEXT, alamat TEXT, kecamatan TEXT,
    latitude NUMERIC, longitude NUMERIC, kategori_khas TEXT, jenis_masakan TEXT,
    harga_min INTEGER, harga_max INTEGER, rating NUMERIC,
    jumlah_ulasan INTEGER, info_halal TEXT, link_foto TEXT, jarak_km NUMERIC
)
LANGUAGE sql STABLE AS $$
    SELECT t.id, t.nama, t.slug, t.alamat, t.kecamatan, t.latitude, t.longitude,
           t.kategori_khas, t.jenis_masakan, t.harga_min, t.harga_max, t.rating, t.jumlah_ulasan,
           t.info_halal, t.link_foto, ROUND(t.jarak::numeric, 3) AS jarak_km
    FROM (
        SELECT r.*,
            6371 * 2 * ASIN(SQRT(
                POWER(SIN(RADIANS(r.latitude::double precision - p_lat) / 2), 2) +
                COS(RADIANS(p_lat)) * COS(RADIANS(r.latitude::double precision)) *
                POWER(SIN(RADIANS(r.longitude::double precision - p_lng) / 2), 2)
            )) AS jarak
        FROM restoran r
    ) t
    WHERE t.jarak <= p_radius_km
    ORDER BY t.jarak
    LIMIT p_limit
$$;
