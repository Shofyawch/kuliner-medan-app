-- Ganti teks label lama
UPDATE restoran SET info_halal = 'Status halal belum dicek'
 WHERE info_halal ILIKE '%belum%';

-- Restoran yang menunya mengandung babi/saksang
UPDATE restoran SET info_halal = 'Non-halal'
 WHERE id IN (SELECT DISTINCT restoran_id FROM menu
              WHERE nama_menu ILIKE '%babi%' OR nama_menu ILIKE '%saksang%');
