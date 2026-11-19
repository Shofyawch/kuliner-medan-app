-- Ganti teks label lama
UPDATE restoran SET info_halal = 'Status halal belum dicek'
 WHERE info_halal ILIKE '%belum%';
