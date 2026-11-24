-- Simpan restoran + menu berharga negatif: harus GAGAL dan restoran ikut batal
SELECT tambah_restoran_dengan_menu(
  '{"nama":"Uji Rollback","slug":"uji-rollback","alamat":"Jl. Uji No. 1","kecamatan":"Medan Kota","latitude":3.5,"longitude":98.6,"kategori_khas":"Uji","jenis_masakan":"Uji"}'::jsonb,
  '[{"nama_menu":"Menu Uji","harga":-5,"kategori":"Makanan"}]'::jsonb);
