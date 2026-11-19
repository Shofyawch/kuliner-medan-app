-- Hapus catatan internal "(Deskripsi otomatis. silakan sesuaikan)"
UPDATE restoran
   SET deskripsi = btrim(regexp_replace(deskripsi, '\s*\(\s*Deskripsi otomatis[^)]*\)', '', 'gi'))
 WHERE deskripsi ILIKE '%deskripsi otomatis%';

UPDATE menu
   SET deskripsi = btrim(regexp_replace(deskripsi, '\s*\(\s*Deskripsi otomatis[^)]*\)', '', 'gi'))
 WHERE deskripsi ILIKE '%deskripsi otomatis%';

-- Buang kalimat rating yang sudah tampil di halaman
UPDATE restoran
   SET deskripsi = btrim(regexp_replace(deskripsi, '\s*Rating Google [0-9.,]+ dari [0-9.,]+ ulasan\.?', '', 'g'))
 WHERE deskripsi ILIKE '%Rating Google%';
