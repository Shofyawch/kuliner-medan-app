#!/usr/bin/env bash
# Uji pendaftaran dan ulasan. Pakai: bash docs/uji-ulasan.sh
BASE="${BASE:-http://localhost:8080/api/v1}"

req() {  # metode path token [json]
  local m="$1" p="$2" t="$3" b="${4:-}" raw
  local args=(-s -w '\n%{http_code}' -X "$m" "$BASE$p" -H 'Content-Type: application/json')
  [ -n "$t" ] && args+=(-H "Authorization: Bearer $t")
  [ -n "$b" ] && args+=(-d "$b")
  raw=$(curl "${args[@]}")
  CODE="${raw##*$'\n'}"
  BODY="${raw%$'\n'*}"
  echo "   -> HTTP $CODE  $(echo "$BODY" | tr -s '\n ' ' ' | head -c 230)"
}
ambil() {
  echo "$BODY" | php -r '$j=json_decode(stream_get_contents(STDIN),true); $v=$j; foreach (explode(".", $argv[1]) as $k) { $v=$v[$k] ?? null; } echo is_scalar($v) ? $v : "";' -- "$1"
}
masuk() {  # username password -> token
  php -r 'echo json_encode(["username"=>$argv[1],"password"=>$argv[2]]);' -- "$1" "$2" \
   | curl -s -X POST "$BASE/auth/login" -H 'Content-Type: application/json' -d @- \
   | php -r '$j=json_decode(stream_get_contents(STDIN),true); echo $j["data"]["token"] ?? "";'
}
akun() {  # username password
  req POST /auth/daftar "" "$(php -r 'echo json_encode(["username"=>$argv[1],"password"=>$argv[2]]);' -- "$1" "$2")"
}

U1="uji_$(date +%s)a"; U2="uji_$(date +%s)b"
PW=$(openssl rand -hex 8)

echo "1) Daftar akun baru -> harus 201"
akun "$U1" "$PW"
echo "2) Daftar dengan username yang sama -> harus 409"
akun "$U1" "$PW"
echo "3) Daftar dengan password terlalu pendek -> harus 422"
akun "uji_pendek" "abc"

T1=$(masuk "$U1" "$PW")
echo "   panjang token: ${#T1} (harus > 100)"
[ -z "$T1" ] && { echo "Login gagal, hentikan."; exit 1; }

echo "4) Tulis ulasan restoran 1 -> harus 201"
req POST /restoran/1/ulasan "$T1" '{"rating":5,"komentar":"Enak dan murah."}'
ID=$(ambil data.id); VERSI=$(ambil data.versi)
echo "   id=$ID versi=$VERSI"
echo "5) Tulis ulasan kedua di restoran yang sama -> harus 409 (satu pengguna satu ulasan)"
req POST /restoran/1/ulasan "$T1" '{"rating":4}'
echo "6) Rating di luar 1-5 -> harus 422"
req POST /restoran/2/ulasan "$T1" '{"rating":9}'
echo "7) Baca daftar ulasan restoran 1 -> harus 200"
req GET /restoran/1/ulasan "$T1"
echo "8) Ubah ulasan dengan versi benar -> harus 200"
req PUT "/ulasan/$ID" "$T1" "{\"versi\":$VERSI,\"rating\":4,\"komentar\":\"Direvisi.\"}"
echo "9) Ubah lagi dengan versi lama -> harus 409"
req PUT "/ulasan/$ID" "$T1" "{\"versi\":$VERSI,\"rating\":1}"

echo "10) Pengguna lain mencoba menghapus ulasan itu -> harus 403"
akun "$U2" "$PW" >/dev/null
T2=$(masuk "$U2" "$PW")
req DELETE "/ulasan/$ID" "$T2"

echo "11) Pengguna biasa membuka daftar moderasi admin -> harus 403"
req GET /ulasan "$T1"

read -s -p "Password admin aplikasi (Enter untuk lewati bagian admin): " AP; echo
if [ -n "$AP" ]; then
  TA=$(masuk admin "$AP")
  echo "12) Admin membuka semua ulasan -> harus 200"
  req GET /ulasan "$TA"
fi

echo "13) Pemilik menghapus ulasannya -> harus 200"
req DELETE "/ulasan/$ID" "$T1"
echo "14) Ulasan yang sudah dihapus -> harus 404"
req DELETE "/ulasan/$ID" "$T1"
unset PW AP T1 T2 TA
echo
echo "Bersihkan akun uji (jalankan sebagai pemilik database):"
echo "  DELETE FROM pengguna WHERE username LIKE 'uji\\_%';"
