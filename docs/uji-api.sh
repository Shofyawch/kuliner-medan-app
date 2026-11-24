#!/usr/bin/env bash
# Uji cepat API: 401, login, 403, 422, 201, 409, 200, 404, cascade delete.
# Pakai: bash docs/uji-api.sh        (server harus jalan: php spark serve)
BASE="${BASE:-http://localhost:8080/api/v1}"

req() {  # metode path token [json]
  local m="$1" p="$2" t="$3" b="${4:-}" raw
  local args=(-s -w '\n%{http_code}' -X "$m" "$BASE$p" -H 'Content-Type: application/json')
  [ -n "$t" ] && args+=(-H "Authorization: Bearer $t")
  [ -n "$b" ] && args+=(-d "$b")
  raw=$(curl "${args[@]}")
  CODE="${raw##*$'\n'}"
  BODY="${raw%$'\n'*}"
  echo "   -> HTTP $CODE  $(echo "$BODY" | tr -s '\n ' ' ' | head -c 260)"
}
ambil() {  # ambil nilai dari JSON di $BODY: ambil data.id
  echo "$BODY" | php -r '$j=json_decode(stream_get_contents(STDIN),true); $v=$j; foreach (explode(".", $argv[1]) as $k) { $v=$v[$k] ?? null; } echo is_scalar($v) ? $v : "";' -- "$1"
}
login() {  # username password
  php -r 'echo json_encode(["username"=>$argv[1],"password"=>$argv[2]]);' -- "$1" "$2" \
   | curl -s -X POST "$BASE/auth/login" -H 'Content-Type: application/json' -d @-
}
token() { php -r '$j=json_decode(stream_get_contents(STDIN),true); echo $j["data"]["token"] ?? "";'; }

echo "1) Tanpa token, mau menulis -> harus 401"
req POST /restoran "" '{}'
echo "2) Login dengan password salah -> harus 401"
login admin salah-sekali | head -c 160; echo

read -s -p "Password admin aplikasi: " AP; echo
read -s -p "Password akun pengguna biasa: " UP; echo
ADMIN=$(login admin "$AP" | token)
USERT=$(login pengguna "$UP" | token)
echo "   panjang token admin: ${#ADMIN}, pengguna: ${#USERT} (harus > 100)"
[ -z "$ADMIN" ] && { echo "Login admin gagal, hentikan."; exit 1; }

echo "3) Role user mencoba menulis -> harus 403"
req POST /restoran "$USERT" '{}'
echo "4) Admin, data tidak valid -> harus 422"
req POST /restoran "$ADMIN" '{"nama":"x","latitude":999}'

echo "5) Tambah restoran -> harus 201"
req POST /restoran "$ADMIN" '{"nama":"Warung Uji Demo","alamat":"Jl. Contoh No. 1, Medan","kecamatan":"Medan Kota","latitude":3.59,"longitude":98.67,"kategori_khas":"Masakan Uji","jenis_masakan":"Uji","harga_min":10000,"harga_max":30000}'
ID=$(ambil data.id); VERSI=$(ambil data.versi)
echo "   id=$ID versi=$VERSI"

echo "6) Ubah dengan versi benar -> harus 200 dan versi naik"
req PUT "/restoran/$ID" "$ADMIN" "{\"versi\":$VERSI,\"deskripsi\":\"Diubah lewat API\"}"
echo "7) Ubah lagi dengan versi lama (simulasi konflik) -> harus 409"
req PUT "/restoran/$ID" "$ADMIN" "{\"versi\":$VERSI,\"deskripsi\":\"Bentrok\"}"

echo "8) Tambah menu ke restoran itu -> harus 201"
req POST "/restoran/$ID/menu" "$ADMIN" '{"nama_menu":"Menu Uji","harga":15000,"kategori":"Makanan"}'
MENU=$(ambil data.id)
echo "9) Baca restoran lewat token admin -> harus 200"
req GET "/restoran/warung-uji-demo" "$ADMIN"

echo "10) Hapus restoran -> harus 200, menu ikut terhapus (cascade)"
req DELETE "/restoran/$ID" "$ADMIN"
echo "11) Baca lagi -> harus 404"
req GET "/restoran/warung-uji-demo" "$ADMIN"

echo "12) Transaksi: restoran + menu sekaligus -> harus 201"
req POST /restoran/lengkap "$ADMIN" '{"restoran":{"nama":"Warung Transaksi","alamat":"Jl. Transaksi No. 2, Medan","kecamatan":"Medan Kota","latitude":3.6,"longitude":98.68,"kategori_khas":"Masakan Uji","jenis_masakan":"Uji"},"menu":[{"nama_menu":"Nasi Uji","harga":12000,"kategori":"Makanan"},{"nama_menu":"Teh Uji","harga":4000,"kategori":"Minuman"}]}'
ID2=$(ambil data.id)
[ -n "$ID2" ] && { echo "   (bersihkan data uji)"; req DELETE "/restoran/$ID2" "$ADMIN"; }
unset AP UP ADMIN USERT
